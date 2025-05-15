<?php

namespace Glaezz\Okeconnect;

use Illuminate\Support\ServiceProvider;

class OkeconnectServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // publish config
        $this->publishes([
            __DIR__ . '/../config/okeconnect.php' => config_path('okeconnect.php'),
        ], 'config');

        // set ke static class config
        Config::load(config('okeconnect'));
    }

    public function register()
    {
        //
    }
}
