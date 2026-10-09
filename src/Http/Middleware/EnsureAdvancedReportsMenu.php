<?php

namespace iEducar\Packages\AdvancedReports\Http\Middleware;

use App\Menu;
use App\Process;
use App\Services\MenuCacheService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class EnsureAdvancedReportsMenu
{
    /**
     * Garante que menu lateral e superior estejam disponíveis
     * e com o grupo Escola selecionado para o módulo de Relatórios Avançados.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $menu = $user ? app(MenuCacheService::class)->getMenuByUser($user) : collect();

        // Usa o menu "Escola" como raiz visual para Relatórios Avançados
        $schoolMenu = Menu::query()->where('process', Process::MENU_SCHOOL)->first();
        $ancestors = $schoolMenu ? Menu::getMenuAncestors($schoolMenu) : [];

        if ($schoolMenu) {
            View::share([
                'mainmenu' => $schoolMenu->root()->getKey(),
                'currentMenu' => $schoolMenu,
                'menuPaths' => $ancestors,
            ]);
        }

        View::share([
            'menu' => $menu,
            'root' => $schoolMenu?->root()?->getKey(),
        ]);

        return $next($request);
    }
}

