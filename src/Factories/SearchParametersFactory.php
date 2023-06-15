<?php declare(strict_types=1);

namespace Elastic\ScoutDriver\Factories;

use Elastic\ScoutDriver\ElasticsearchBooleanQuery;
use Elastic\Adapter\Search\SearchParameters;
use Laravel\Scout\Builder;
use stdClass;

class SearchParametersFactory implements SearchParametersFactoryInterface
{
    public function makeFromBuilder(Builder $builder, array $options = []): SearchParameters
    {
        $searchParameters = new SearchParameters();

        $index = $this->makeIndex($builder);
        $searchParameters->indices([$index]);

        $query = $this->makeQuery($builder);
        $searchParameters->query($query);

        if ($sort = $this->makeSort($builder)) {
            $searchParameters->sort($sort);
        }

        if ($from = $this->makeFrom($options)) {
            $searchParameters->from($from);
        }

        if ($size = $this->makeSize($builder, $options)) {
            $searchParameters->size($size);
        }

        return $searchParameters;
    }

    protected function makeIndex(Builder $builder): string
    {
        return $builder->index ?: $builder->model->searchableAs();
    }

    protected function makeQuery(Builder $builder): array
    {
	    $query = [
		    'bool' => $this->convertBuilderDataToBooleanQuery($builder)->toArray()
	    ];

	    if (!empty($builder->query)) {
		    $query['bool']['must'] = [
			    'query_string' => [
				    'query' => $builder->query,
			    ],
		    ];
	    } else {
		    $query['bool']['must'] = [
			    'match_all' => new stdClass(),
		    ];
	    }

        return $query;
    }

    protected function makeFilter(Builder $builder): ?array
    {
        $filter = collect($builder->wheres)->map(static fn ($value, string $field) => [
            'term' => [$field => $value],
        ])->values();

        if (property_exists($builder, 'whereIns')) {
            $whereIns = collect($builder->whereIns)->map(static fn (array $values, string $field) => [
                'terms' => [$field => $values],
            ])->values();

            $filter = $filter->merge($whereIns);
        }

        return $filter->isEmpty() ? null : $filter->all();
    }

    protected function makeSort(Builder $builder): ?array
    {
        $sort = collect($builder->orders)->map(static fn (array $order) => [
            $order['column'] => $order['direction'],
        ]);

        return $sort->isEmpty() ? null : $sort->all();
    }

    protected function makeFrom(array $options): ?int
    {
        if (isset($options['page'], $options['perPage'])) {
            return ($options['page'] - 1) * $options['perPage'];
        }

        return null;
    }

    protected function makeSize(Builder $builder, array $options): ?int
    {
        return $options['perPage'] ?? $builder->limit;
    }

	public function convertBuilderDataToBooleanQuery(Builder $builder) : ElasticsearchBooleanQuery
	{
		$elasticQuery = new ElasticsearchBooleanQuery();

		foreach($builder->wheres as $field => $value) {
			$elasticQuery->addNormal(is_array($value) ? ['terms' => [$field => $value]] : ['term' => [$field => $value]]);
		}

		// iterate in reverse order to permit the grouping of OR conditions in sub containers
		$orBundle = null;
		$advancedWheres = array_reverse($builder->advancedWheres);
		foreach ($advancedWheres as $field => $whereData) {
			// catch OR conditions and add them to the bundle.
			// If the conditions is not an OR and there's an open bundle, commit them
			if(isset($whereData['boolean']) && $whereData['boolean'] === "or") {
				if($orBundle === null) {
					$orBundle = new ElasticsearchBooleanQuery();
				}

				$this->{"convert{$whereData['type']}OperatorToBooleanQuery"}($whereData, $orBundle, true);
				continue;
			} elseif ($orBundle !== null) {
				$elasticQuery->filter[] = $orBundle;
				$orBundle = null;
			}

			$this->{"convert{$whereData['type']}OperatorToBooleanQuery"}($whereData, $elasticQuery);
		}

		if($orBundle !== null) {
			$elasticQuery->filter[] = $orBundle;
		}

		return $elasticQuery;
	}

	public function convertBasicOperatorToBooleanQuery(array $whereData, ElasticsearchBooleanQuery $booleanQuery, $should = false)
	{
		$not = $whereData['not']??false;
		switch ($whereData['operator']) {
			case '=':
			case 'eq':
				$booleanQuery->add(['term' => [$whereData['field'] => $whereData['value']]], $not, $should);
				break;

			case '>':
			case 'gt':
				$booleanQuery->add(['range' => [$whereData['field'] => ['gt' => $whereData['value']]]], $not, $should);
				break;

			case '<':
			case 'lt':
				$booleanQuery->add(['range' => [$whereData['field'] => ['lt' => $whereData['value']]]], $not, $should);
				break;

			case '>=':
			case 'gte':
				$booleanQuery->add(['range' => [$whereData['field'] => ['gte' => $whereData['value']]]], $not, $should);
				break;

			case '<=':
			case 'lte':
				$booleanQuery->add(['range' => [$whereData['field'] => ['lte' => $whereData['value']]]], $not, $should);
				break;

			case '!=':
			case '<>':
			case 'neq':
				$booleanQuery->add(['term' => [$whereData['field'] => $whereData['value']]], !$not, $should);
				break;

			case 'like':
				$booleanQuery->add(['wildcard' => [$whereData['field'] => str_replace("%","*",$whereData['value'])]], $not, $should);
				break;
		}
	}

	public function convertBetweenOperatorToBooleanQuery(array $whereData, ElasticsearchBooleanQuery $booleanQuery, $should = false)
	{
		$booleanQuery->add(['range' => [$whereData['field'] => ['gte' => $whereData['value'], 'lte' => $whereData['secondValue']]]], $whereData['not']??false, $should);
	}

	public function convertInOperatorToBooleanQuery(array $whereData, ElasticsearchBooleanQuery $booleanQuery, $should = false)
	{
		$booleanQuery->add(['terms' => [$whereData['field'] => $whereData['values']]], $whereData['not']??false, $should);
	}

	public function convertNullOperatorToBooleanQuery(array $whereData, ElasticsearchBooleanQuery $booleanQuery, $should = false)
	{
		$booleanQuery->add(['exists' => ['field' => $whereData['field']]], !($whereData['not']??false), $should);
	}

	public function convertExistsOperatorToBooleanQuery(array $whereData, ElasticsearchBooleanQuery $booleanQuery, $should = false)
	{
		$booleanQuery->add(['exists' => ['field' => $whereData['field']]], $whereData['not']??false, $should);
	}

	public function convertStringStartWithOperatorToBooleanQuery(array $whereData, ElasticsearchBooleanQuery $booleanQuery, $should = false)
	{
		$booleanQuery->add(['match_phrase_prefix' => [$whereData['field'] => $whereData['value']]], $whereData['not']??false, $should);
	}

	public function convertStringEndWithOperatorToBooleanQuery(array $whereData, ElasticsearchBooleanQuery $booleanQuery, $should = false)
	{
		$booleanQuery->add(['wildcard' => [$whereData['field'] => "*".$whereData['value']]], $whereData['not']??false, $should);
	}

	public function convertStringContainsOperatorToBooleanQuery(array $whereData, ElasticsearchBooleanQuery $booleanQuery, $should = false)
	{
		$booleanQuery->add(['wildcard' => [$whereData['field'] => "*".$whereData['value']."*"]], $whereData['not']??false, $should);
	}

	public function convertNestedOperatorToBooleanQuery(array $whereData, ElasticsearchBooleanQuery $booleanQuery, $should = false)
	{
		if(isset($whereData['query']) && $whereData['query'] instanceof Builder) {
			$booleanQuery->add($this->convertBuilderDataToBooleanQuery($whereData['query']), $whereData['not']??false, $should);
		}
	}
}
