<?php

namespace Phatnt99\AdvancedQuery\QueryBuilder;

use Phatnt99\AdvancedQuery\Exceptions\AdvancedQueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\ForwardsCalls;

abstract class QueryBuilder
{
    use ForwardsCalls;

    /**
     * Define Model class
     *
     * @var
     */
    protected $model;

    /**
     * Define Sort class
     *
     * @var
     */
    protected $sort;

    /**
     * Define Filter class
     *
     * @var
     */
    protected $filter;

    /**
     * Actual query
     *
     * @var
     */
    protected $query;

    /**
     * Request instance
     *
     * @var
     */
    protected $request;

    /**
     * Filter instance
     *
     * @var
     */
    protected $filterInstance;

    /**
     * Sort instance
     *
     * @var
     */
    protected $sortInstance;

    /**
     * QueryBuilder constructor.
     *
     * @throws \Phatnt99\AdvancedQuery\Exceptions\AdvancedQueryException
     */
    public function __construct()
    {
        $this->validateResources();
        $this->request = app(Request::class);
        $this->filterInstance = new $this->filter;
        $this->sortInstance = new $this->sort;
        $this->query = (new $this->model)->query();
    }

    public function __call($method, $arguments)
    {
        $this->forwardCallTo($this->query, $method, $arguments);
        return $this;
    }

    public function validateResources() {
        if(!$this->model) {
            throw new AdvancedQueryException(
              '$model attribute not provided!'
            );
        }
        if(!$this->filter) {
            throw  new AdvancedQueryException(
                '$filter attribute not provided'
            );
        }
        if(!$this->sort) {
            throw new AdvancedQueryException(
                '$sort attribute not provided'
            );
        }
    }

    /**
     * Apply filter with attributes
     * i.e: ?filters[name]=John
     *
     * @param array $customAttrs
     * @param array $allows
     * @return $this
     */
    public function filter($customAttrs = [], $allows = [])
    {
        $attributes = $customAttrs ?? $this->request->input('filters', []);
        $this->filterInstance->setQuery($this->query)
                             ->setAllowAttrs($allows)
                             ->apply($attributes);

        return $this;
    }

    /**
     * Apply sort with attributes
     * i.e: ?sort=id,-name
     *
     * @param array $customAttrs
     * @param array $allows
     * @return $this
     */
    public function sort($customAttrs = [], $allows = [])
    {
        $attributes = $customAttrs ?? $this->request->input('sort');
        $this->sortInstance->setQuery($this->query)
                           ->setAllowAttrs($allows)
                           ->apply($attributes);

        return $this;
    }

    /**
     * Paginate
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginate($perPage = 1)
    {
        return $this->query->paginate($perPage);
    }
}