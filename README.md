# Laravel vue-i18n Bridge [![CircleCI](https://circleci.com/gh/appointer/laravel-vue-i18n-bridge.svg?style=svg)](https://circleci.com/gh/appointer/laravel-vue-i18n-bridge)

This bridge gives you full freedom of translating your Vue (especially SPA) frontend with Laravels built-in translation techniques,
without sacrificing frontend performance or general user experience. It ensures that only the client requested language has to be downloaded 
by the client. You are no longer forced to either maintain a second place for translation in javascript, or pregenerate a javascript file
containing all your translated strings of all locales.

This package is designed to work in direct conjunction with [vue-i18n](https://github.com/kazupon/vue-i18n) and especially its 
[dynamic locale](https://kazupon.github.io/vue-i18n/dynamic.html) feature. However, it should be possible to make it work with other vue plugins or
even other javascript frameworks with minimal javascript effort.

### Other approaches and inspiration

Generating a javascript dictionary with your translatable strings may be totally fine for some use cases. If this is for you, you should totally check
out Martin Lindhe's exceptional [laravel-vue-i18n-generator](https://github.com/martinlindhe/laravel-vue-i18n-generator). 

**Credits to him and his package for inspiration.**

## Installation

Navigate to your project and run the composer command:

```bash
composer require appointer/laravel-vue-i18n-bridge
```

The next step is to register the service provider:

```php
// config/app.php
'providers' => [
    ...
    \Appointer\VueTranslation\VueTranslationServiceProvider::class,
];
```

Finally, you have to register the routes of this package:

```php
// app/Providers/RouteServiceProvider.php
public function boot()
{
    parent::boot();

    Appointer\VueTranslation\VueTranslation::routes();
}
```

*(Optional)* If you want to, you can publish the config. This gives you full control over white- and blacklists amongst other things. 
Use the following artisan command:

```bash
php artisan vendor:publish --provider="Appointer\VueTranslation\VueTranslationServiceProvider::class" --tag="config"
```

**NOTE:** White- and blacklisting is also possible using the `VueTranslation::whitelist()` and `VueTranslation::blacklist()` methods respectively.

### Implementing the javascript

No worries, its a piece of cake. We are using `axios` as an example HTTP client. If you got a stock laravel frontend, 
you probably got it installed already. You just have to replace your current locale population with the following implementation:

```javascript
// Keep in mind, that this requires the vue-i18n plugin.
// We are following https://kazupon.github.io/vue-i18n/dynamic.html

var lang = 'en';
Vue.locale(lang, function() {
    return axios.get('/i18n/' + lang)
        .then(function(response) {
            return Promise.resolve(response.data[lang]);
        })
        .catch(function(error) {
            return Promise.reject(error);
        });
}, function() {
    Vue.config.lang = lang;
});
```

#### Pitfalls

* Vendor translations are supported and loaded automatically. Though vue-i18n does not support namespacing (`{namespace}::{key}`) like laravel does,
the vendor namespaces are part of the translation key (`{namespace}.{key}`).
* White- and blacklisting is currently only possible using the root group (the translation file like `auth.php` => `auth`) or namespace
(`namespace::` => `namespace`) of a translation. 

## Testing

Tests can be executed using the command:

```bash
./vendor/bin/phpunit
```

## Contibuting

Every help is very welcome. Do you got an issue, or having a great idea for extending this project? 
Feel free to open a pull request or submit an issue.

If you file a bug report, your issue should contain a title and a clear description of the issue. You should also include as much relevant 
information as possible and a code sample that demonstrates the issue. The goal of a bug report is to make it easy for yourself - and others - 
to replicate the bug and develop a fix.

Please use the [issue tracker](https://github.com/appointer/laravel-vue-i18n-bridge/issues) to report issues.

## License

This library is open-sourced software licensed under the [MIT](https://github.com/appointer/laravel-vue-i18n-bridge/blob/master/LICENSE.md) license.
