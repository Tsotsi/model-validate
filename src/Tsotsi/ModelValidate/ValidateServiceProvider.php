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
        \Validator::extend('sets', function ($attribute, $value, $parameters) {
            $as = explode(',', $value);
         return empty(array_diff($as,$parameters));
        });
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'tsotsi');

        $this->publishes([
            __DIR__.'/../../lang' => base_path('resources/lang/vendor/tsotsi'),
        ]);

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
