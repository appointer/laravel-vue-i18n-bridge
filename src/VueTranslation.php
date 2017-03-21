<?php
namespace Appointer\VueTranslation;

use Illuminate\Support\Facades\Route;

class VueTranslation
{
    /**
     * Binds the routes into the controller.
     *
     * @param  array $options
     * @return void
     */
    public static function routes(array $options = [])
    {
        $defaultOptions = [
            'prefix' => 'i18n',
            'namespace' => '\Appointer\VueTranslation\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);
        Route::group($options, function ($router) {
            (new RouteRegistrar($router))->all();
        });
    }

    /**
     * Exposes all translation keys of the given locale.
     *
     * @param string|null $locale
     * @param string|null $fallbackLocale
     * @return array
     */
    public static function expose($locale = null, $fallbackLocale = null): array
    {
        $locale = $locale ?? config('app.locale');
        $fallbackLocale = $fallbackLocale ?? config('app.fallback_locale');

        return app(TranslationResolver::class)
            ->expose($locale, $fallbackLocale);
    }

    /**
     * Alternative posibility to set the whitelist
     * without the need to publish the config.
     *
     * @param array $whitelist
     */
    public static function whitelist(array $whitelist)
    {
        config()->set('vue-translation.whitelist', $whitelist);
    }

    /**
     * Alternative posibility to set the blacklist
     * without the need to publish the config.
     *
     * @param array $blacklist
     */
    public static function blacklist(array $blacklist)
    {
        config()->set('vue-translation.blacklist', $blacklist);
    }
}