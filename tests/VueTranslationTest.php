<?php
namespace Tests\Appointer\VueTranslation;

use Appointer\VueTranslation\TranslationResolver;
use Appointer\VueTranslation\VueTranslation;
use Illuminate\Foundation\Auth\User;
use Mockery;

class VueTranslationTest extends TestCase
{
    public function test_that_route_change_is_respected()
    {
        // Register additional routes.
        VueTranslation::routes([
            'prefix' => 'my_locale_stuff'
        ]);

        $this->json('GET', 'my_locale_stuff/en')
            ->assertJson([
                'en' => $this->get_trans_content('en')
            ]);
    }

    public function test_that_middleware_is_respected_unauthenticated()
    {
        // Register additional routes.
        VueTranslation::routes([
            'middleware' => ['auth:api']
        ]);

        $this->json('GET', 'i18n/en')
            ->assertStatus(401);
    }

    public function test_that_middleware_is_respected_authenticated()
    {
        // Register additional routes.
        VueTranslation::routes([
            'middleware' => ['auth:api']
        ]);

        $this->actingAs(Mockery::mock(User::class), 'api');

        $this->json('GET', 'i18n/en')
            ->assertStatus(200)
            ->assertJson([
                'en' => $this->get_trans_content('en')
            ]);
    }

    public function test_that_expose_called_with_defaults()
    {
        $this->mock_resolver(function ($resolver) {
            return $resolver->shouldReceive('expose')->once()->with('en', 'en');
        });

        $result = VueTranslation::expose();
    }

    public function test_that_expose_called_with_locale()
    {
        $this->mock_resolver(function ($resolver) {
            return $resolver->shouldReceive('expose')->once()->with('de', 'en');
        });

        $result = VueTranslation::expose('de');
    }

    public function test_that_expose_called_with_fallback()
    {
        $this->mock_resolver(function ($resolver) {
            return $resolver->shouldReceive('expose')->once()->with('fr', 'de');
        });

        $result = VueTranslation::expose('fr', 'de');
    }

    public function test_that_whitelist_can_be_set()
    {
        $whitelist = [
            'my',
            'new',
            'whitelist'
        ];

        VueTranslation::whitelist($whitelist);

        $this->assertSame($whitelist, $this->app['config']->get('vue-translation.whitelist'));
    }

    public function test_that_blacklist_can_be_set()
    {
        $blacklist = [
            'my',
            'new',
            'blacklist'
        ];

        VueTranslation::blacklist($blacklist);

        $this->assertSame($blacklist, $this->app['config']->get('vue-translation.blacklist'));
    }

    /**
     * Mock the resolver, as it is tested already.
     * We just want to check the "facade" here.
     *
     * @param callable $expectations
     */
    private function mock_resolver(callable $expectations)
    {
        // Mock the resolver.
        $resolver = Mockery::mock(TranslationResolver::class);

        // Apply expectations and return mock.
        $mock = $expectations($resolver)->getMock();

        // Register in app container.
        $this->app->singleton(TranslationResolver::class, function ($app) use ($mock) {
            return $mock;
        });
    }
}