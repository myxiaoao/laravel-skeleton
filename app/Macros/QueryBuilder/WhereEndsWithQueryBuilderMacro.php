<?php

namespace App\Macros\QueryBuilder;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @mixin \Illuminate\Database\Eloquent\Relations\Relation
 */
class WhereEndsWithQueryBuilderMacro
{
    public function whereEndsWith(): callable
    {
        return function ($column, string $value, string $boolean = 'and', bool $not = false) {
            $operator = $not ? 'not like' : 'like';

            return $this->where($column, $operator, "%$value", $boolean);
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
}
