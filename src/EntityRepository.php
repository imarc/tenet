<?php
namespace Tenet;

use Doctrine;

/**
 *
 */
class EntityRepository extends Doctrine\ORM\EntityRepository
{
	const ALIAS_NAME = 'data';
	const REGEX_CONDITION = '/^([^\:]*)\:([^\:]+)$/';

	/**
	 *
	 */
	public function build(Array $terms = NULL, $order = NULL, $limit = NULL, $page = 1)
	{
		$builder = $this->createQueryBuilder(static::ALIAS_NAME);

		if ($limit) {
			if ($limit < 0) {
				throw new InvalidArgumentException();
			}

			$builder->setMaxResults($limit);
			$builder->setFirstResult(($page - 1) * $limit);
		}

		if ($terms) {
			$builder->where($this->expandBuildTerms($builder, $terms));
		}

		return $builder->getQuery()->getResult();
	}


	/**
	 *
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

				} elseif (is_array($value)) {
					$field    = $condition;
					$operator = 'in';

				} else {
					$field    = $condition;
					$operator = '=';
				}

			} elseif (!is_array($value)) {
				$field    = $value;
				$operator = '!';

			} else {
				$ors->add($this->expandBuildTerms($builder, $value, $pcount));
				continue;
			}

			$and->add($this->makeComparison($builder, $field, $operator, $value, ++$pcount));
		}

		return $expr = $and->add($ors);
	}


	/**
	 *
	 */
	private function makeComparison($builder, $field, $operator, $value, $pcount)
	{
		$method_translations = [
			'='   => 'eq',
			'<'   => 'lt',
			'>'   => 'gt',
			'~'   => 'like',
			'!'   => 'neq',
			'!='  => 'neq',
			'<>'  => 'neq',
			'<='  => 'lte',
			'>='  => 'gte',
			'in'  => 'in',
			'!in' => 'notIn'
		];

		if (!isset($method_translations[$operator])) {
			throw new Flourish\ProgrammerException(
				'Invalid operator %s specified', $operator
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

		if (strpos($field, '.') === FALSE) {
			$field = self::ALIAS_NAME . '.' . $field;
		}

		return $builder
			->setParameter($pcount, $value)
			->expr()->$method($field, '?' . $pcount);
	}
}