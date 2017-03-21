<?php

namespace Tests\Appointer\VueTranslation;

use Appointer\VueTranslation\VueTranslation;
use Appointer\VueTranslation\VueTranslationServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            VueTranslationServiceProvider::class,
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        // Load our routes.
        VueTranslation::routes();

        // Some changes to the container.
        $this->app->instance('path.lang', __DIR__ . '/testfiles');
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set some defaults.
        $app['config']->set('app.locale', 'en');
        $app['config']->set('app.fallback_locale', 'en');
    }

    /**
     * Helper wich returns the full translation set.
     *
     * @param $locale
     * @return array
     */
    protected function get_trans_content($locale)
    {
        return [
            'file1' => trans('file1', [], $locale),
            'file2' => trans('file2', [], $locale),
            'file3' => trans('file3', [], $locale),
        ];
    }

    /**
     * Helper which returns the full translation set whith
     * vendor stuff attached.
     *
     * @param $namespace
     * @param $locale
     * @return array
     */
    protected function get_trans_content_with_vendor($namespace, $locale)
    {
        return array_merge($this->get_trans_content($locale), [
            $namespace => [
                'vendor1' => trans('vendorpkg::vendor1', [], $locale),
            ]
        ]);
    }
}
