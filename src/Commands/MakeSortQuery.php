<?php

namespace Phatnt99\AdvancedQuery\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeSortQuery extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:sort {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make powerful filter query for Eloquent model';

    protected function getStub()
    {
        return __DIR__.'\stubs\sortquery.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Queries\\'.'Sorts';
    }

    protected function buildClass($name)
    {
        $replace = [];

        return str_replace(array_keys($replace), array_values($replace), parent::buildClass($name));
    }

}
