<?php

namespace Tsotsi\ModelValidate;

use Illuminate\Support\ServiceProvider;

class ValidateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {


    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(ValidateModelCommand::class);
        //
        $this->app->bind('validate-model', function (\App $app) {
            return $app->make(ValidateModel::class);
        });

    }

    public function provides()
    {
        return ['validate-model'];
    }
}
