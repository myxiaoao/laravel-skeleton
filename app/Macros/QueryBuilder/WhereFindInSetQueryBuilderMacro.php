<?php

namespace App\Macros\QueryBuilder;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Query\Builder
 * @mixin \Illuminate\Database\Eloquent\Relations\Relation
 */
class WhereFindInSetQueryBuilderMacro
{
    public function whereFindInSet(): callable
    {
        /* @var string|Arrayable|string[] $values */
        return function (string $column, $values, string $boolean = 'and', bool $not = false) {
            $sql = $not ? "not find_in_set(?, $column)" : "find_in_set(?, $column)";

            $values instanceof Arrayable and $values = $values->toArray();
            is_array($values) and $values = implode(',', $values);

            return $this->whereRaw($sql, $values, $boolean);
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
}
