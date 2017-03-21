<?php

namespace Appointer\VueTranslation;

use Illuminate\Support\ServiceProvider;

class VueTranslationServiceProvider extends ServiceProvider
{
    /**
     * Lazy load service provider.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/vue-translation.php' => config_path('vue-translation.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/vue-translation.php', 'vue-translation');

        $this->registerResolver();
    }

    /**
     * Register the translation resolver.
     *
     * @return void
     */
    protected function registerResolver()
    {
        $this->app->singleton(TranslationResolver::class, function ($app) {
            return new TranslationResolver($app['translation.loader'], $app['files'], $app['path.lang']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [TranslationResolver::class];
    }
}