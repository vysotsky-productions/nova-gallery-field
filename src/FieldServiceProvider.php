<?php

namespace VysotskyProductions\NovaGalleryField;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('NovaGalleryField', __DIR__ . '/../dist/js/field.js');
            Nova::style('NovaGalleryField', __DIR__ . '/../dist/css/field.css');
        });

        $this->publishes([
            __DIR__ . '/config/nova-gallery-field.php' => config_path('nova-gallery-field.php'),
        ], 'nova-gallery-field');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
