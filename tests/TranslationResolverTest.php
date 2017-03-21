<?php
namespace Tests\Appointer\VueTranslation;

class TranslationResolverTest extends TestCase
{
    public function test_that_default_locale_is_returned()
    {
        $locale = config('app.locale');
        $default = $this->get_trans_content($locale);

        $this->json('GET', 'i18n')
            ->assertStatus(200)
            ->assertJson([
                $locale => $default
            ]);
    }

    public function test_that_specified_locale_is_returned()
    {
        // english
        $english = $this->get_trans_content('en');

        $this->json('GET', 'i18n/en')
            ->assertStatus(200)
            ->assertJson([
                'en' => $english
            ]);

        // german
        $german = $this->get_trans_content('de');

        $this->json('GET', 'i18n/de')
            ->assertStatus(200)
            ->assertJson([
                'de' => $german
            ]);
    }

    public function test_that_fallback_locale_is_returned()
    {
        $fallback = $this->get_trans_content(config('app.fallback_locale'));

        $this->json('GET', 'i18n/fr')
            ->assertStatus(200)
            ->assertJson([
                'fr' => $fallback
            ]);
    }

    public function test_that_whitelist_is_shown()
    {
        $whitelist = ['file3'];

        // set whitelist in config
        $this->app['config']->set('vue-translation.whitelist', $whitelist);

        // mimic the expected result
        $english = collect($this->get_trans_content('en'))
            ->only($whitelist)
            ->toArray();

        $this->json('GET', 'i18n/en')
            ->assertStatus(200)
            ->assertJson([
                'en' => $english
            ]);
    }

    public function test_that_blacklist_is_excluded()
    {
        $blacklist = ['file1'];

        // set blacklist in config
        $this->app['config']->set('vue-translation.blacklist', $blacklist);

        // mimic the expected result
        $english = collect($this->get_trans_content('en'))
            ->except($blacklist)
            ->toArray();

        $this->json('GET', 'i18n/en')
            ->assertStatus(200)
            ->assertJson([
                'en' => $english
            ]);
    }

    public function test_that_vendor_translations_exist()
    {
        // add vendor namespace
        $this->app->make('translator')
            ->addNamespace('vendorpkg', __DIR__ . '/testfiles/vendor');

        // english
        $english = $this->get_trans_content_with_vendor('vendorpkg', 'en');

        $this->json('GET', 'i18n/en')
            ->assertStatus(200)
            ->assertJson([
                'en' => $english
            ]);

        // german
        $german = $this->get_trans_content_with_vendor('vendorpkg', 'de');

        $this->json('GET', 'i18n/de')
            ->assertStatus(200)
            ->assertJson([
                'de' => $german
            ]);
    }

    public function test_that_whitelist_shows_vendor()
    {
        // add vendor namespace
        $this->app->make('translator')
            ->addNamespace('vendorpkg', __DIR__ . '/testfiles/vendor');

        $whitelist = ['file1', 'vendorpkg'];

        // set whitelist in config
        $this->app['config']->set('vue-translation.whitelist', $whitelist);

        // mimic the expected result
        $english = collect($this->get_trans_content('en'))
            ->only($whitelist)
            ->toArray();

        $this->json('GET', 'i18n/en')
            ->assertStatus(200)
            ->assertJson([
                'en' => $english
            ]);
    }

    public function test_that_blacklist_excludes_vendor()
    {
        // add vendor namespace
        $this->app->make('translator')
            ->addNamespace('vendorpkg', __DIR__ . '/testfiles/vendor');

        $blacklist = ['file1', 'vendorpkg'];

        // set blacklist in config
        $english = $this->get_trans_content_with_vendor('vendorpkg', 'en');

        // mimic the expected result
        $english = collect($this->get_trans_content_with_vendor('vendorpkg', 'en'))
            ->except($blacklist)
            ->toArray();

        $this->json('GET', 'i18n/en')
            ->assertStatus(200)
            ->assertJson([
                'en' => $english
            ]);
    }
}