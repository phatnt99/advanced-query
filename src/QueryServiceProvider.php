<?php

namespace Phatnt99\AdvancedQuery;

use Phatnt99\AdvancedQuery\Commands\MakeAdvancedQuery;
use Illuminate\Support\ServiceProvider;
use Phatnt99\AdvancedQuery\Commands\MakeFilterQuery;
use Phatnt99\AdvancedQuery\Commands\MakeSortQuery;

class QueryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands(MakeAdvancedQuery::class);
        $this->commands(MakeFilterQuery::class);
        $this->commands(MakeSortQuery::class);
    }
}
