<?php

namespace App\Support\Macros;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class CollectionMacro
{
    public function pluckToArray(): callable
    {
        return function ($value, $key = null): array {
            /** @var \Illuminate\Support\Collection $this */
            return $this->pluck($value, $key)->toArray();
        };
    }

    public function head(): callable
    {
        return function () {
            /** @var \Illuminate\Support\Collection $this */
            return $this->first();
        };
    }

    public function end(): callable
    {
        return function () {
            /** @var \Illuminate\Support\Collection $this */
            return $this->last();
        };
    }

    public function after(): callable
    {
        return function ($currentItem, $fallback = null) {
            /** @var \Illuminate\Support\Collection $this */
            $currentKey = $this->search($currentItem, true);

            if ($currentKey === false) {
                return $fallback;
            }

            $currentOffset = $this->keys()->search($currentKey, true);

            $next = $this->slice($currentOffset, 2);

            if ($next->count() < 2) {
                return $fallback;
            }

            return $next->last();
        };
    }

    public function before(): callable
    {
        return function ($currentItem, $fallback = null) {
            /** @var \Illuminate\Support\Collection $this */
            return $this->reverse()->after($currentItem, $fallback);
        };
    }

    public function ifAny(): callable
    {
        return function (callable $callback): Collection {
            /** @var \Illuminate\Support\Collection $this */
            if (! $this->isEmpty()) {
                $callback($this);
            }

            return $this;
        };
    }

    public function ifEmpty(): callable
    {
        return function (callable $callback): Collection {
            /** @var \Illuminate\Support\Collection $this */
            if ($this->isEmpty()) {
                $callback($this);
            }

            return $this;
        };
    }

    public function if(): callable
    {
        return function ($if,  $then = null,  $else = null) {
            /** @var \Illuminate\Support\Collection $this */
            return value($if, $this) ? value($then, $this) : value($else, $this);
        };
    }

    public function paginate(): callable
    {
        return function ($perPage = 15, $pageName = 'page', $page = null, $total = null, $options = []) {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            /** @var \Illuminate\Support\Collection $this */
            $items = $this->forPage($page, $perPage)->values();

            $total = $total ?: $this->count();

            $options += [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ];


            return new LengthAwarePaginator($items, $total, $perPage, $page, $options);
        };
    }

    public function simplePaginate(): callable
    {
        return function ($perPage = 15, $pageName = 'page', $page = null, $options = []) {
            $page = $page ?: Paginator::resolveCurrentPage($pageName);

            /** @var \Illuminate\Support\Collection $this */
            $items = $this->slice(($page - 1) * $perPage)->take($perPage + 1);

            $options += [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ];

            return new Paginator($items, $perPage, $page, $options);
        };
    }

    public function filterFilled(): callable
    {
        return function () {
            /** @var \Illuminate\Support\Collection $this */
            return $this->filter(function ($value) {
                return filled($value);
            });
        };
    }
}
