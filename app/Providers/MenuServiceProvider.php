<?php

namespace App\Providers;

use Auth;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        view()->composer('*', function ($view): void {
            $route            = request()->route() ?? false;
            $firstPartOfRoute = '';

            if ($route) {
                $routeName        = request()->route()->getName() ?? '';
                $firstPartOfRoute = explode('.', $routeName)[0];
            }

            $view->with('menu', $this->menuArray($firstPartOfRoute));
            $view->with('adminMenu', $this->adminMenuArray($firstPartOfRoute));
        });
    }

    private function adminMenuArray(string $firstPartOfRoute): array
    {
        $return = [];

//        if (Auth::user()?->is_organisation_admin) {
//            $return['API'] = [
//                'currentlySelected' => 'admin.api' === $firstPartOfRoute,
//                'parentRoute'       => 'admin.api',
//                'routePrefix'       => 'admin.api',
//                'svg'               => '<i class="fa-solid fa-tower-cell"></i>',
//                'items'             => [],
//            ];
//
//            $return['Users'] = [
//                'currentlySelected' => 'admin.user' === $firstPartOfRoute,
//                'parentRoute'       => 'admin.user',
//                'routePrefix'       => 'admin.user',
//                'svg'               => '<i class="fa-solid fa-users"></i>',
//                'items'             => [],
//            ];
//        }

        return $return;
    }

    private function menuArray(string $firstPartOfRoute): array
    {
        $return['Home'] = [
            'currentlySelected' => 'home' === $firstPartOfRoute,
            'parentRoute'       => 'home',
            'routePrefix'       => 'home',
            'svg'               => '<i class="fa-solid fa-house"></i>',
            'items'             => [],
        ];

        $return['Ports'] = [
            'currentlySelected' => 'port' === $firstPartOfRoute,
            'parentRoute'       => 'port.index',
            'routePrefix'       => 'port',
            'svg'               => '<i class="fa-solid fa-photo-film"></i>',
            'items'             => [],
        ];

        $return['Wads'] = [
            'currentlySelected' => 'wad' === $firstPartOfRoute,
            'parentRoute'       => 'wad.index',
            'routePrefix'       => 'wad',
            'svg'               => '<i class="fa-solid fa-photo-film"></i>',
            'items'             => [],
        ];

        return $return;
    }
}
