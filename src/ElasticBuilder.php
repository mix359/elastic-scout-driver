<?php

namespace Elastic\ScoutDriver;

use Laravel\Scout\Builders\Traits\WhereExistsTrait;
use Laravel\Scout\Builders\Traits\WhereNullTrait;

class ElasticBuilder extends \Laravel\Scout\Builder
{
	use WhereExistsTrait, WhereNullTrait;

	/**
	 * Add a where string start with to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @param  string  $boolean
	 * @param  bool  $not
	 * @return $this
	 */
	public function whereStringStartWith($field, $value, $boolean = 'and', $not = false)
	{
		$type = 'StringStartWith';

		$this->wheres[] = compact('type', 'field', 'value', 'boolean', 'not');

		return $this;
	}

	/**
	 * Add an or string start with clause to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @param  bool  $not
	 * @return $this
	 */
	public function orWhereStringStartWith($field, $value, $not = false)
	{
		return $this->whereStringStartWith($field, $value, 'or', $not);
	}

	/**
	 * Add a where not string start with clause to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @param  string  $boolean
	 * @return $this
	 */
	public function whereNotStringStartWith($field, $value, $boolean = 'and')
	{
		return $this->whereStringStartWith($field, $value, $boolean, true);
	}

	/**
	 * Add a where not string start with clause to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @return $this
	 */
	public function orWhereNotStringStartWith($field, $value)
	{
		return $this->orWhereStringStartWith($field, $value, true);
	}

	/**
	 * Add a where string end with to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @param  string  $boolean
	 * @param  bool  $not
	 * @return $this
	 */
	public function whereStringEndWith($field, $value, $boolean = 'and', $not = false)
	{
		$type = 'StringEndWith';

		$this->wheres[] = compact('type', 'field', 'value', 'boolean', 'not');

		return $this;
	}

	/**
	 * Add an or string end with clause to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @param  bool  $not
	 * @return $this
	 */
	public function orWhereStringEndWith($field, $value, $not = false)
	{
		return $this->whereStringEndWith($field, $value, 'or', $not);
	}

	/**
	 * Add a where not string end with clause to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @param  string  $boolean
	 * @return $this
	 */
	public function whereNotStringEndWith($field, $value, $boolean = 'and')
	{
		return $this->whereStringEndWith($field, $value, $boolean, true);
	}

	/**
	 * Add a where not string end with clause to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @return $this
	 */
	public function orWhereNotStringEndWith($field, $value)
	{
		return $this->orWhereStringEndWith($field, $value, true);
	}

	/**
	 * Add a where string contains to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @param  string  $boolean
	 * @param  bool  $not
	 * @return $this
	 */
	public function whereStringContains($field, $value, $boolean = 'and', $not = false)
	{
		$type = 'StringContains';

		$this->wheres[] = compact('type', 'field', 'value', 'boolean', 'not');

		return $this;
	}

	/**
	 * Add an or string contains clause to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @param  bool  $not
	 * @return $this
	 */
	public function orWhereStringContains($field, $value, $not = false)
	{
		return $this->whereStringContains($field, $value, 'or', $not);
	}

	/**
	 * Add a where not string contains clause to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @param  string  $boolean
	 * @return $this
	 */
	public function whereNotStringContains($field, $value, $boolean = 'and')
	{
		return $this->whereStringContains($field, $value, $boolean, true);
	}

	/**
	 * Add a where not string contains clause to the query.
	 *
	 * @param  string  $field
	 * @param  string  $value
	 * @return $this
	 */
	public function orWhereNotStringContains($field, $value)
	{
		return $this->orWhereStringContains($field, $value, true);
	}
}