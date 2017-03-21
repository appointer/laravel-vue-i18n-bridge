<?php
namespace Appointer\VueTranslation;

use Illuminate\Contracts\Routing\Registrar as Router;

class RouteRegistrar
{
    /**
     * The router implementation.
     *
     * @var Router
     */
    protected $router;

    /**
     * Create a new route registrar instance.
     *
     * @param  Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Register routes for transient tokens, clients, and personal access tokens.
     *
     * @return void
     */
    public function all()
    {
        $this->forLocalization();
    }

    /**
     * Register the routes needed for localization.
     *
     * @return void
     */
    public function forLocalization()
    {
        $this->router->get('/{locale?}', [
            'uses' => 'TranslationController@show',
        ]);
    }
}