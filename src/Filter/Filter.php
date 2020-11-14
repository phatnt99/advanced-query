<?php

namespace Phatnt99\AdvancedQuery\Filter;

use Phatnt99\AdvancedQuery\Traits\HasAllowAttributes;
use Phatnt99\AdvancedQuery\Traits\HasCustomQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class Filter
{
    use HasAllowAttributes, HasCustomQuery;

    protected $filterExact = [];

    protected $filterPartial = [];

    protected $filterDate = [];

    protected $attributes = [];

    protected $filters = [];

    protected $allows = [];

    protected $query;

    /**
     * Filter constructor.
     */
    public function __construct()
    {
        $this->convertFilterMethods();
    }

    public function apply(array $attribute = [])
    {
        $this->addAttributesToFilter($attribute);

        foreach ($this->attributes as $key => $value) {
            $this->filter($key, $value);
        }

        return $this;
    }

    private function filter($column, $value)
    {
        if (in_array($column, $this->filterExact)) {
            $this->filterExact($column, $value);
        } elseif (in_array($column, $this->filterPartial)) {
            $this->filterPartial($column, $value);
        } elseif (in_array($column, $this->filterDate) || array_key_exists($column, $this->filterDate)) {
            $this->filterDate($column, $value);
        } elseif (in_array($column, $this->filters)) {
            ($this->filters[$column])($value);
        };
    }

    private function convertFilterMethods()
    {
        $methods = get_class_methods($this);
        collect($methods)->each(function ($method) {
            $this->filters[$method] = \Closure::fromCallable([$this, $method]);
        });
    }

    private function addAttributesToFilter(array $attribute)
    {
        if ($this->allows) {
            $assocAllows = array_fill_keys($this->allows, '');
            $this->attributes = array_intersect_key($attribute, $assocAllows);

            return;
        }
        $this->attributes = $attribute;
    }

    private function filterExact($column, $value)
    {
        if (Str::of($value)->contains(',')) {
            return $this->query->whereIn($column, explode(',', trim($value)));
        }

        return $this->query->where($column, '=', $value);
    }

    private function filterPartial($column, $value)
    {
        $wrappedColumn = $this->query->getQuery()->getGrammar()->wrap($column);
        $sql = "LOWER({$wrappedColumn}) LIKE ?";
        // Ex: use array_filter($value) to delete all empty element
        if (is_array($value) && (count(array_filter($value)) !== 0)) {
            return $this->query->where(function (Builder $query) use ($value, $sql) {
                foreach (array_filter($value) as $partialValue) {
                    $partialValue = mb_strtolower($partialValue, 'UTF8');
                    $query->orWhereRaw($sql, ["%{$partialValue}%"]);
                }
            });
        }
        $value = mb_strtolower($value, 'UTF8');

        return $this->query->whereRaw($sql, ["%{$value}%"]);
    }

    private function filterDate($column, $value)
    {

        $from = strpos($column, 'from.') == 0 ? $value : null;
        $to = strpos($column, 'to.') == 0 ? $value : null;

        // get actual column name
        $actualColumn = Arr::get(explode('.', $column), 1, $column);

        return $this->query->where(function (Builder $query) use ($actualColumn, $from, $to) {
            return $query
                ->when($from, function (Builder $query) use ($actualColumn, $from) {
                    return $query->where($actualColumn, '>=', $from);
                })
                ->when($to, function (Builder $query) use ($actualColumn, $to) {
                    return $query->where($actualColumn, '<=', $to);
                });
        });
    }
}