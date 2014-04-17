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

				$builder->addOrderBy($field, $direction);
			}
		}

		foreach (static::$order as $field => $direction) {
			$builder->addOrderBy(static::ALIAS_NAME . '.' . $field, $direction);
		}

		return new Paginator($builder->getQuery());
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
	 * Expands build terms in `and` / `or` expressions
	 *
	 * @param QueryBuilder $builder A builder to build the and/or expressions
	 * @param array $terms The terms from which to expand comparisons
	 * @param integer &$pcount The current parameter count (when called recursively)
	 * @return Expr An `and` expression containing the terms and conditions passed by $terms
	 */
	protected function expandBuildTerms($builder, $terms, &$pcount = 0)
	{
		$and  = $builder->expr()->andx();
		$ors  = $builder->expr()->orx();

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
				$field_parts = explode('.', $field, 2);
				$rel_alias   = $field_parts[0];

				foreach ($builder->getDQLPart('select') as $select_part) {
					$aliases =  $select_part->getParts();

					if (!in_array($rel_alias, $aliases)) {
						$aliases[] = $rel_alias;

						$builder->leftJoin(self::ALIAS_NAME . '.' . $rel_alias, $rel_alias, 'ON');
						$builder->select($aliases);
					}
				}
			}

			$comparison = $this->makeComparison($builder, $field, $operator, $value, ++$pcount);

			$builder->setParameter($pcount, $value);
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
	private function makeComparison($builder, $field, $operator, $value, $pcount)
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

		if (is_array($value)) {
			switch ($method) {
				case 'eq':
					$method = 'in';
					break;

				case 'neq':
					$method = 'notIn';
					break;
			}
		}

		return $builder->expr()->$method($field, '?' . $pcount);
	}
}
