<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Channel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \View::share('channels', Channel::all());

        // \View::composer('*', function ($view) {
        //     $channels = \Cache::rememberForever('channels', function () {
        //         return Channel::all();
        //     });
        //     $view->with('channels', $channels);
        // });

        \Validator::extend('spamfree', 'App\Rules\SpamFree@passes');
    }
}
