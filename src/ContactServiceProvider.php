<?php

namespace Rutatiina\Contact;

use Illuminate\Support\ServiceProvider;

class ContactServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        include __DIR__.'/routes/routes.php';
        include __DIR__.'/routes/api.php';

        $this->loadViewsFrom(__DIR__.'/resources/views/limitless/', 'contact.limitless');
        $this->loadViewsFrom(__DIR__.'/resources/views/azia/', 'contact');
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Rutatiina\Contact\Http\Controllers\ContactController');
    }
}
