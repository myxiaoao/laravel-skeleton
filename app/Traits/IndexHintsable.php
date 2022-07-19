<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @url https://dev.mysql.com/doc/refman/5.7/en/index-hints.html
 *
 * @method static Builder|Model forceIndex(array|string[] $indexes, string $for = '', string $as = '')
 * @method static Builder|Model useIndex(string|string[] $indexes, string $for = '', string $as = '')
 * @method static Builder|Model ignoreIndex(array|string[] $indexes, string $for = '', string $as = '')
 * @method static Builder|Model getTable()
 */
trait IndexHintsable
{
    protected $forceIndexes = [];
    protected $useIndexes = [];
    protected $ignoreIndexes = [];
    protected $preparedIndexes = '';

    /**
     * @param  Builder  $query
     * @param  string|string[]  $indexes
     * @param  string  $for  JOIN|ORDER BY|GROUP BY
     * @param  string  $as
     *
     * @return Builder
     */
    public function scopeForceIndex(Builder $query, $indexes, string $for = '', string $as = ''): Builder
    {
        if (Str::contains($this->preparedIndexes, 'USE')) {
            throw new InvalidArgumentException('It is an error to mix USE INDEX and FORCE INDEX for the same table.');
        }

        if (! $this->tableIndexExists($indexes, 'force')) {
            return $query;
        }

        $this->setTableNameAndAlias($as);

        $indexesToSting = implode(',', $this->forceIndexes);
        $this->forceIndexes = [];
        $this->preparedIndexes .= " FORCE INDEX";
        $this->prepareFor($for);
        $this->preparedIndexes .= " ($indexesToSting)";

        return $query->from(DB::raw($this->preparedIndexes));
    }

    /**
     * @param  Builder  $query
     * @param  string|string[]  $indexes
     * @param  string  $for
     * @param  string  $as
     *
     * @return Builder
     */
    public function scopeUseIndex(Builder $query, $indexes, string $for = '', string $as = ''): Builder
    {
        if (Str::contains($this->preparedIndexes, 'FORCE')) {
            throw new \Exception('However, it is an error to mix USE INDEX and FORCE INDEX for the same table:');
        }

        if (! $this->tableIndexExists($indexes, 'use')) {
            return $query;
        }

        $this->setTableNameAndAlias($as);

        $indexesToSting = implode(',', $this->useIndexes);
        $this->useIndexes = [];
        $this->preparedIndexes .= " USE INDEX";
        $this->prepareFor($for);
        $this->preparedIndexes .= " ($indexesToSting)";

        return $query->from(DB::raw($this->preparedIndexes));
    }

    /**
     * @param  Builder  $query
     * @param  string|string[]  $indexes
     * @param  string  $for
     * @param  string  $as
     *
     * @return Builder
     */
    public function scopeIgnoreIndex(Builder $query, $indexes, string $for = '', string $as = ''): Builder
    {
        if (! $this->tableIndexExists($indexes, 'ignore')) {
            return $query;
        }

        $this->setTableNameAndAlias($as);

        $indexesToSting = implode(',', $this->ignoreIndexes);
        $this->ignoreIndexes = [];
        $this->preparedIndexes .= " IGNORE INDEX";
        $this->prepareFor($for);
        $this->preparedIndexes .= " ($indexesToSting)";

        return $query->from(DB::raw($this->preparedIndexes));
    }

    protected function setTableNameAndAlias(string $as = ''): void
    {
        if (! empty($this->preparedIndexes)) {
            return;
        }

        $this->preparedIndexes = self::getTable();
        $this->preparedIndexes .= ! empty($as) ? " {$as}" : '';
    }

    /**
     * @param $indexes
     * @param  string|string[]  $type
     *
     * @return bool
     */
    protected function tableIndexExists($indexes, string $type): bool
    {
        foreach (Arr::wrap($indexes) as $index) {
            $index = strtolower($index);

            Schema::table(self::getTable(), function (Blueprint $table) use ($index, $type) {
                return $this->fillIndexes($table, $index, $type);
            });
        }

        return ! empty($this->forceIndexes) || ! empty($this->ignoreIndexes) || ! empty($this->useIndexes);
    }

    protected function fillIndexes(Blueprint $table, $index, $type): void
    {
        if (! $table->hasIndex($index)) {
            return;
        }
        switch ($type) {
            case 'force':
                $this->forceIndexes[] = $index;

                break;
            case  'ignore':
                $this->ignoreIndexes[] = $index;

                break;
            case 'use':
                $this->useIndexes[] = $index;

                break;
        };
    }

    protected function prepareFor(string $for = ''): bool
    {
        if (empty($for)) {
            return false;
        }
        $for = strtoupper(str_replace('_', ' ', $for));
        $this->preparedIndexes .= " FOR {$for}";

        return true;
    }
}