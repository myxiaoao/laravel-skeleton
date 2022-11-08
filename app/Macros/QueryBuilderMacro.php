<?php

namespace App\Macros;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pipeline\Pipeline;
use InvalidArgumentException;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class QueryBuilderMacro
{
    public function pipe(): callable
    {
        return function (...$pipes) {
            return tap($this, function ($builder) use ($pipes) {
                array_unshift($pipes, function ($builder, $next) {
                    if (
                        ! ($piped = $next($builder)) instanceof EloquentBuilder
                        && ! $piped instanceof QueryBuilder
                        && ! $piped instanceof Relation
                    ) {
                        throw new InvalidArgumentException(
                            sprintf(
                                'Query builder pipeline must be return a %s or %s or %s instance.',
                                EloquentBuilder::class,
                                QueryBuilder::class,
                                Relation::class,
                            )
                        );
                    }
                });

                (new Pipeline(app()))
                    ->send($builder)
                    ->through(...$pipes)
                    ->thenReturn();
            });
        };
    }

    public function getToArray(): callable
    {
        return function ($columns = ['*']): array {
            return $this->get($columns)->toArray();
        };
    }

    public function firstToArray(): callable
    {
        return function ($columns = ['*']): ?array {
            // return optional($this->first($columns))->toArray();
            return ($model = $this->first($columns)) ? $model->toArray() : (array)$model;
        };
    }

    public function whereFindInSet(): callable
    {
        /* @var string|Arrayable|string[] $values */
        return function (string $column, $values, string $boolean = 'and', bool $not = false) {
            $type = $not ? "not find_in_set(?, $column)" : "find_in_set(?, $column)";

            $values instanceof Arrayable and $values = $values->toArray();
            is_array($values) and $values = implode(',', $values);

            return $this->whereRaw($type, $values, $boolean);
        };
    }

    public function whereNotFindInSet(): callable
    {
        /* @var string|Arrayable|string[] $values */
        return function (string $column, $values) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereFindInSet($column, $values, 'and', true);
        };
    }

    public function orWhereFindInSet(): callable
    {
        /* @var string|Arrayable|string[] $values */
        return function (string $column, $values) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereFindInSet($column, $values, 'or');
        };
    }

    public function orWhereNotFindInSet(): callable
    {
        /* @var string|Arrayable|string[] $values */
        return function (string $column, $values) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereFindInSet($column, $values, 'or', true);
        };
    }

    public function orderByWith(): callable
    {
        return function ($relation, $column, $direction = 'asc') {
            if (is_string($relation)) {
                $relation = $this->getRelationWithoutConstraints($relation);
            }

            /** @noinspection PhpParamsInspection */
            return $this->orderBy(
                $relation->getRelationExistenceQuery(
                    $relation->getRelated()->newQueryWithoutRelationships(),
                    $this,
                    $column
                ),
                $direction
            );
        };
    }

    public function orderByWithDesc(): callable
    {
        return function ($relation, $column) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->orderByWith($relation, $column, 'desc');
        };
    }

    public function whereLike(): callable
    {
        return function ($column, string $value, string $boolean = 'and', bool $not = false) {
            $type = $not ? 'not like' : 'like';

            return $this->where($column, $type, "%$value%", $boolean);
        };
    }

    public function whereNotLike(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereLike($column, $value, 'and', true);
        };
    }

    public function orWhereLike(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereLike($column, $value, 'or');
        };
    }

    public function orWhereNotLike(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereLike($column, $value, 'or', true);
        };
    }

    public function whereStartsWith(): callable
    {
        return function ($column, string $value, string $boolean = 'and', bool $not = false) {
            $type = $not ? 'not like' : 'like';

            return $this->where($column, $type, "$value%");
        };
    }

    public function whereNotStartsWith(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereStartsWith($column, $value, 'and', true);
        };
    }

    public function orWhereStartsWith(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereStartsWith($column, $value, 'or');
        };
    }

    public function orWhereNotStartsWith(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereStartsWith($column, $value, 'or', true);
        };
    }

    public function whereEndsWith(): callable
    {
        return function ($column, string $value, string $boolean = 'and', bool $not = false) {
            $type = $not ? 'not like' : 'like';

            return $this->where($column, 'like', "%$value");
        };
    }

    public function whereNotEndsWith(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereEndsWith($column, $value, 'and', true);
        };
    }

    public function orWhereEndsWith(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereEndsWith($column, $value, 'or');
        };
    }

    public function orWhereNotEndsWith(): callable
    {
        return function ($column, string $value) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereEndsWith($column, $value, 'or', true);
        };
    }

    public function whereIns(): callable
    {
        /* @var Arrayable|array[] $values */
        return function (array $columns, $values, string $boolean = 'and', bool $not = false) {
            $type = $not ? 'not in' : 'in';

            $rawColumns = implode(',', $columns);

            $values instanceof Arrayable and $values = $values->toArray();
            $values = array_map(function ($value) use ($columns) {
                if (array_is_list($value)) {
                    return $value;
                }

                return array_reduce($columns, function ($sortedValue, $column) use ($value) {
                    $sortedValue[$column] = $value[$column] ?? trigger_error(
                        sprintf('The value of the column is not found in the array.: %s', $column),
                        E_USER_ERROR
                    );

                    return $sortedValue;
                }, []);
            }, $values);

            $rawValue = sprintf('(%s)', implode(',', array_fill(0, count($columns), '?')));
            $rawValues = implode(',', array_fill(0, count($values), $rawValue));

            $raw = "($rawColumns) $type ($rawValues)";

            return $this->whereRaw($raw, $values, $boolean);
        };
    }

    public function whereNotIns(): callable
    {
        return function (array $columns, $values) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereIns($columns, $values, 'and', true);
        };
    }

    public function orWhereIns(): callable
    {
        return function (array $columns, $values) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereIns($columns, $values, 'or');
        };
    }

    public function orWhereNotIns(): callable
    {
        return function (array $columns, $values) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereIns($columns, $values, 'or', true);
        };
    }

    public function whereFullText(): callable
    {
        /**
         * Add a "where fulltext" clause to the query.
         *
         * @param  string|string[]  $columns
         * @param  string  $value
         * @param  string  $boolean
         *
         * @return $this
         */
        return function ($columns, $value, array $options = [], $boolean = 'and') {
            $type = 'Fulltext';

            $columns = (array)$columns;

            $this->wheres[] = compact('type', 'columns', 'value', 'options', 'boolean');

            $this->addBinding($value);

            return $this;
        };
    }

    public function orWhereFullText(): callable
    {
        /**
         * Add a "or where fulltext" clause to the query.
         *
         * @param  string|string[]  $columns
         * @param  string  $value
         * @return $this|callable
         */
        return function ($columns, $value, array $options = []) {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            return $this->whereFulltext($columns, $value, $options, 'or');
        };
    }

    /**
     * @see https://github.com/ankane/hightop-php
     */
    public function top(): callable
    {
        return function ($column, ?int $limit = null, ?bool $null = false, ?int $min = null, ?string $distinct = null) {
            if ($distinct === null) {
                $op = 'count(*)';
            } else {
                $quotedDistinct = $this->getGrammar()->wrap($distinct);
                $op = "count(distinct $quotedDistinct)";
            }

            $relation = $this->select($column)->selectRaw($op)->groupBy($column)->orderByRaw('1 desc')->orderBy($column);

            if ($limit !== null) {
                $relation = $relation->limit($limit);
            }

            if (! $null) {
                $relation = $relation->whereNotNull($column);
            }

            if ($min !== null) {
                $relation = $relation->havingRaw("$op >= ?", [$min]);
            }

            // can't use pluck with expressions in Postgres without an alias
            $rows = $relation->get()->toArray();
            $result = [];
            foreach ($rows as $row) {
                $values = array_values($row);
                $result[$values[0]] = $values[1];
            }

            return $result;
        };
    }
}