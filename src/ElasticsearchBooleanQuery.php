<?php

namespace Elastic\ScoutDriver;

use Illuminate\Contracts\Support\Arrayable;

class ElasticsearchBooleanQuery implements Arrayable {
	public $must = [];
	public $filter = [];
	public $should = [];
	public $mustNot = [];

	public function add($condition, $negated = false, $should = false)
	{
		if($negated) {
			if($should) {
				$boolQuery = new ElasticsearchBooleanQuery();
				$boolQuery->mustNot[] = $condition;
				$this->should[] =  $boolQuery;
			} else {
				$this->mustNot[] = $condition;
			}
		} else {
			if($should) {
				$this->should[] = $condition;
			} else {
				$this->filter[] = $condition;
			}
		}
	}

	public function addNormal($condition, $should = false)
	{
		if($should) {
			$this->should[] = $condition;
		} else {
			$this->filter[] = $condition;
		}
	}

	public function addNegated($condition, $should = false)
	{
		if($should) {
			$boolQuery = new ElasticsearchBooleanQuery();
			$boolQuery->mustNot[] = $condition;
			$this->should[] =  $boolQuery;
		} else {
			$this->mustNot[] = $condition;
		}
	}

	public function toArray() : array
	{
		$data = [];
		if(!empty($this->must)) {
			$data['must'] = [];
			foreach ($this->must as $item) {
				$data['must'][] = $item instanceof ElasticsearchBooleanQuery ? $item->toArray() : $item;
			}
		}

		if(!empty($this->filter)) {
			$data['filter'] = [];
			foreach ($this->filter as $item) {
				$data['filter'][] = $item instanceof ElasticsearchBooleanQuery ? $item->toArray() : $item;
			}
		}

		if(!empty($this->should)) {
			$data['should'] = [];
			foreach ($this->should as $item) {
				$data['should'][] = $item instanceof ElasticsearchBooleanQuery ? $item->toArray() : $item;
			}
		}

		if(!empty($this->mustNot)) {
			$data['must_not'] = [];
			foreach ($this->mustNot as $item) {
				$data['must_not'][] = $item instanceof ElasticsearchBooleanQuery ? $item->toArray() : $item;
			}
		}

		return $data;
	}
}