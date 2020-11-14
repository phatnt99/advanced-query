<?php

namespace Phatnt99\AdvancedQuery\Sort;

use Phatnt99\AdvancedQuery\Enums\SortDirection;
use Phatnt99\AdvancedQuery\Traits\HasAllowAttributes;
use Phatnt99\AdvancedQuery\Traits\HasCustomQuery;

/*
 * Sort by column and their direction -OK
 * Default sort -OK
 * Advanced sort -NOT REQUIREMENT
 * Custom ordering index on result - ORDER OF SORT STRING -OK
 */

abstract class Sort
{
    use HasAllowAttributes, HasCustomQuery;

    protected $attributes;

    protected $sorts = [];

    protected $defaultSorts = [];

    protected $allows = [];

    protected $query;

    public function __construct()
    {
        $this->convertSortMethods();
    }

    public function apply($attributes)
    {
        $this->addAttributeToSort($attributes);

        $this->convertSortAttribute()->each(function ($value, $key) {
            $this->sort($key, $value);
        });

        $this->applyDefault();

        return $this;
    }

    private function sort($column, $direction)
    {
        if (in_array($column, $this->defaultSorts) || array_key_exists($column, $this->convertSortAttribute()
                                                                                     ->toArray())) {
            $this->query->orderBy($column, $direction);
        }
        if (array_key_exists($column, $this->sorts)) {
            ($this->sorts[$column])($direction);
        }
    }

    private function applyDefault()
    {
        collect(
            array_diff_assoc($this->defaultSorts,
                $this->convertSortAttribute()->toArray()))
            ->each(function ($value, $key) {
                if (! is_int($key)) {
                    $this->query->orderBy($key, $value);
                }
            });
    }

    private function addAttributeToSort($attributes)
    {
        $this->attributes = $attributes;
    }

    private function convertSortAttribute()
    {
        $convertedAttrs = [];
        if (is_string($this->attributes)) {
            $convertedAttrs = explode(',', $this->attributes);
        }

        return collect($convertedAttrs)
            ->mapWithKeys(function ($att) {
                if ($this->allows && in_array($att, $this->allows)) {
                    return [ltrim($att, '-') => $this->parseSortDirection($att)];
                }
            });
    }

    private function parseSortDirection(string $att)
    {
        return strpos($att, '-') === 0 ?
            SortDirection::DESCENDING :
            SortDirection::ASCENDING;
    }

    private function convertSortMethods()
    {
        $methods = get_class_methods($this);
        collect($methods)->each(function ($method) {
            $this->sorts[$method] = \Closure::fromCallable([$this, $method]);
        });
    }
}