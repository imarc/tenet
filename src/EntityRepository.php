<?php
namespace Tenet;

use Doctrine;
use InvalidArgumentException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 *
 */
class EntityRepository extends Doctrine\ORM\EntityRepository
{
	const ALIAS_NAME = 'data';
	const REGEX_CONDITION = '/^([^\:]*)\:([^\:]+)$/';

	static protected $order = [];


	/**
	 *
	 */
	public function build(Array $terms = NULL, $order = NULL, $limit = NULL, $page = 1)
	{
		$builder = $this->createQueryBuilder(static::ALIAS_NAME);

		if ($limit) {
			if ($limit < 0) {
				throw new InvalidArgumentException(
					'Limit cannot be less than 0'
				);
			}

			if ($page < 0) {
				throw new InvalidArgumentException(
					'Page cannot be less than 1'
				);
			}

			$builder->setMaxResults($limit);
			$builder->setFirstResult(($page - 1) * $limit);
		}

		if ($terms) {
			$builder->where($this->expandBuildTerms($builder, $terms));
		}

		if ($order) {
			foreach ($order as $field => $direction) {
				if (is_numeric($field)) {
					$field     = $order[$field];
					$direction = 'asc';
				}

				if (strpos($field, '.') === FALSE) {
					$field = 'data.' . $field;
				}

				$builder->addOrderBy($field, $direction);
			}
		}

		foreach (static::$order as $field => $direction) {
			$builder->addOrderBy(static::ALIAS_NAME . '.' . $field, $direction);
		}

		return new Paginator($builder->getQuery());
	}


	/**
	 *
	 */
	public function count(Array $terms = NULL, $field = '*')
	{
		$builder = $this->createQueryBuilder(static::ALIAS_NAME);

		$builder->select('count(data.' . $field . ')');

		if ($terms) {
			$builder->where($this->expandBuildTerms($builder, $terms));
		}

		return $builder->getQuery()->getSingleScalarResult();
	}


	/**
	 * Standard findAll with the option to add an orderBy
	 *
	 * @param array $orderBy The order by clause to add
	 *
	 * {@inheritDoc}
	 *
	 */
	public function findAll(array $orderBy = array())
	{
		return $this->findBy(array(), $orderBy);
	}


	/**
	 * {@inheritDoc}
	 */
	public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
	{
		$orderBy = array_merge((array) $orderBy, static::$order);

		return parent::findBy($criteria, $orderBy, $limit, $offset);
	}


	/**
	 * {@inheritDoc}
	 */
	public function findOneBy(array $criteria, array $orderBy = null)
	{
		$orderBy = array_merge((array) $orderBy, static::$order);

		return parent::findOneBy($criteria, $orderBy);
	}


	/**
	 * Fetch an associated repository by property name
	 *
	 * @param string $entity_property The entity property pointing to the foreign repo
	 * @return EntityRepository The related repository
	 */
	public function fetchAssociatedRepository($entity_property)
	{
		$entity_class       = $this->getClassName();
		$class_meta_data    = $this->getEntityManager()->getClassMetaData($entity_class);
		$associated_mapping = $class_meta_data->getAssociationMapping($entity_property);

		return $this->getEntityManager()->getRepository($associated_mapping['targetEntity']);
	}


	/**
	 * Expands build terms in `and` / `or` expressions
	 *
	 * @param QueryBuilder $builder A builder to build the and/or expressions
	 * @param array $terms The terms from which to expand comparisons
	 * @param integer &$pcount The current parameter count (when called recursively)
	 * @return Expr An `and` expression containing the terms and conditions passed by $terms
	 */
	protected function expandBuildTerms($builder, $terms, &$pcount = 0)
	{
		$and     = $builder->expr()->andx();
		$ors     = $builder->expr()->orx();

		foreach ($terms as $condition => $value) {
			if (!is_numeric($condition)) {
				if (preg_match_all(self::REGEX_CONDITION, $condition, $matches)) {
					$field    = $matches[1][0];
					$operator = $matches[2][0];

				} else {
					$field    = $condition;
					$operator = '=';
				}

			} elseif (!is_array($value)) {
				$field    = $terms['condition'];
				$operator = '!';

			} else {
				$ors->add($this->expandBuildTerms($builder, $value, $pcount));
				continue;
			}

			//
			// Normalize field and handle joins
			//

			if (strpos($field, '.') === FALSE) {
				$field = self::ALIAS_NAME . '.' . $field;
			} else {
				$join_aliases = array();
				$field_parts  = explode('.', $field, 2);
				$rel_alias    = $field_parts[0];

				foreach ($builder->getDQLPart('join') as $join_part) {
					foreach ($join_part as $join) {
						$join_aliases[] = explode('.', $join->getJoin())[1];
					}
				}

				if (!in_array($rel_alias, $join_aliases)) {
					$builder->leftJoin(self::ALIAS_NAME . '.' . $rel_alias, $rel_alias, 'ON');
				}
			}

			if (!is_null($value)) {
				$comparison = $this->makeComparison($builder, $field, $operator, $value, ++$pcount);

				if ($operator == '~') {
					$value = strtolower($value);
					$value = str_replace(' ', '%', $value);
					$value = '%' . $value . '%';

					$builder->setParameter($pcount, $value);
				} else {
					$builder->setParameter($pcount, $value);
				}
			} else {
				$comparison = $this->makeComparison($builder, $field, $operator, $value);
			}

			$and->add($comparison);
		}

		return $expr = $and->add($ors);
	}


	/**
	 * Makes a comparison based on shortened operators
	 *
	 * @param QueryBuilder $builder A builder to build comparisons
	 * @param string $field The field to make a comparison for
	 * @param string $operator The comparison operation to use
	 * @param mixed $value The value for comparison
	 * @param integer $pcount The current parameter count
	 * @return Expr A mixed comparison expression of the equivalent $operator type
	 */
	private function makeComparison($builder, $field, $operator, $value, $pcount = NULL)
	{
		$method_translations = [
			'='   => 'eq',
			'<'   => 'lt',
			'>'   => 'gt',
			'!'   => 'neq',
			'~'   => 'like',
			'in'  => 'in',
			'!in' => 'notIn',
			'=='  => 'eq',
			'!='  => 'neq',
			'<>'  => 'neq',
			'<='  => 'lte',
			'>='  => 'gte'
		];

		if (!isset($method_translations[$operator])) {
			throw new InvalidArgumentException(
				'Build terms contains invalid operator ' . $operator
			);
		}

		$method = $method_translations[$operator];

		if (is_null($value)) {
			switch ($method) {
				case 'eq':
					$method = 'isNull';
					break;

				case 'neq':
					$method = 'isNotNull';
					break;
			}

		} elseif (is_array($value)) {
			switch ($method) {
				case 'eq':
					$method = 'in';
					break;

				case 'neq':
					$method = 'notIn';
					break;
			}
		}

		if ($method == 'like') {
			$field = 'LOWER(' . $field . ')';
		}

		return ($pcount !== NULL)
			? $builder->expr()->$method($field, '?' . $pcount)
			: $builder->expr()->$method($field);
	}
}
