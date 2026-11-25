<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Providers;

use iEducar\Packages\BuritiLagunaAmbiente\Console\LagunaCheckCommand;
use iEducar\Packages\BuritiLagunaAmbiente\Console\LagunaEnsaiarCommand;
use iEducar\Packages\BuritiLagunaAmbiente\Console\LagunaSetupCommand;
use iEducar\Packages\BuritiLagunaAmbiente\Support\Identidade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class LagunaAmbienteServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-laguna-ambiente-package';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $root = dirname(__DIR__, 2);
        $this->loadViewsFrom($root.'/resources/views', 'laguna-ambiente');

        if ($this->app->runningInConsole()) {
            $this->commands([
                LagunaCheckCommand::class,
                LagunaSetupCommand::class,
                LagunaEnsaiarCommand::class,
            ]);

            if (\App\Support\Tenancy\PackageTenant::allows(self::PACKAGE)) {
                $this->loadMigrationsFrom($root.'/database/migrations');
            }
        }

        Route::middleware([
            'web',
            'ieducar.navigation',
            'ieducar.footer',
            'ieducar.xssbypass',
            'ieducar.suspended',
            'auth',
            'ieducar.checkresetpassword',
        ])->group($root.'/routes/web.php');

        View::composer('laguna-ambiente::*', function ($view): void {
            $view->with('identidade', Identidade::tokens());
        });
    }
}
