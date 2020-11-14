<?php

namespace Phatnt99\AdvancedQuery\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MakeAdvancedQuery extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:query {name} {--model} {--fs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make powerful query for Eloquent model';

    protected $type = 'Query';

    protected $model;

    protected function getStub()
    {
        return __DIR__.'\stubs\advancedquery.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Queries';
    }

    protected function buildClass($name)
    {
        $replace = [];
        if (! $this->option('model')) {
            $this->input->setOption('model', $this->resolveModelFromClassName());
            $this->model = class_basename($this->option('model'));
        }

        if ($this->option('model')) {
            $replace = $this->buildModelReplacements($replace);
        }

        if ($this->option('fs')) {
            $replace = $this->buildFsReplacements($replace);
        }

        return str_replace(array_keys($replace), array_values($replace), parent::buildClass($name));
    }

    /**
     * Build the model replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        if (! class_exists($modelClass = $this->parseModel($this->option('model')))) {
            $this->error('Model '.$this->option('model').' does not exist!');
            exit;
        }

        return array_merge($replace, [
            '{{useModel}}' => $modelClass,
            '{{model}}'    => class_basename($modelClass),
        ]);
    }

    protected function buildFsReplacements(array $replace)
    {
        $this->call(MakeFilterQuery::class,
            ["name" => $this->model.'Filter']);

        $this->call(MakeSortQuery::class,
            ["name" => $this->model.'Sort']);

        $fullNameFilter = $this->qualifyClass('\\Filters\\'.$this->model.'Filter');
        $fullNameSort = $this->qualifyClass('\\Sorts\\'.$this->model.'Sort');

        return array_merge($replace, [
            '{{useFilter}}' => $fullNameFilter,
            '{{useSort}}'   => $fullNameSort,
            '{{filter}}'    => class_basename($fullNameFilter),
            '{{sort}}'      => class_basename($fullNameSort),
        ]);
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param $model
     * @return string
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            $this->error('Model name contains invalid characters.');
            exit;
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (! Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace())) {
            $model = $rootNamespace.$model;
        }

        return $model;
    }

    /**
     * Resolve a model from the given class name.
     *
     * @return string
     */
    protected function resolveModelFromClassName()
    {
        return 'App\\Models\\'.str_replace('Query', '', Arr::last(explode('/', $this->getNameInput())));
    }
}
