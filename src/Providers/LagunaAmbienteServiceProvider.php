<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Providers;

use App\Support\PublicModuleRegistry;
use iEducar\Packages\BuritiLagunaAmbiente\Console\LagunaCheckCommand;
use iEducar\Packages\BuritiLagunaAmbiente\Console\LagunaEnsaiarCommand;
use iEducar\Packages\BuritiLagunaAmbiente\Console\LagunaIdiarioCommand;
use iEducar\Packages\BuritiLagunaAmbiente\Console\LagunaPovoarCidadeCommand;
use iEducar\Packages\BuritiLagunaAmbiente\Console\LagunaSetupCommand;
use iEducar\Packages\BuritiLagunaAmbiente\Http\Middleware\ExigeModuloAtivo;
use iEducar\Packages\BuritiLagunaAmbiente\Support\BuritiUi;
use iEducar\Packages\BuritiLagunaAmbiente\Support\Identidade;
use iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao;
use iEducar\Packages\BuritiLagunaAmbiente\Support\TemaBuriti;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class LagunaAmbienteServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-laguna-ambiente-package';

    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/laguna-ambiente.php', 'laguna-ambiente');
    }

    public function boot(): void
    {
        $root = dirname(__DIR__, 2);
        $this->loadViewsFrom($root.'/resources/views', 'laguna-ambiente');
        BuritiUi::registrar();
        $this->publicarAssetsTema($root);

        PublicModuleRegistry::register('laguna-ambiente', [
            'package' => self::PACKAGE,
            'titulo' => (string) config('laguna-ambiente.public.titulo', 'Ambiente municipal'),
            'descricao' => 'Ferramentas admin de verificação/setup (não público)',
            'url' => null,
            'icone' => 'fa-heartbeat',
            'css_class' => 'public-module',
            'aparecer_na_home' => (bool) config('laguna-ambiente.public.aparecer_na_home', false),
            'ordem' => (int) config('laguna-ambiente.public.ordem', 90),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                LagunaCheckCommand::class,
                LagunaSetupCommand::class,
                LagunaEnsaiarCommand::class,
                LagunaPovoarCidadeCommand::class,
                LagunaIdiarioCommand::class,
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

        // Bloqueia rotas de módulos desativados na configuração municipal
        $this->app['router']->pushMiddlewareToGroup('web', ExigeModuloAtivo::class);

        try {
            ModuloConfiguracao::aplicarTodasRuntime();
        } catch (\Throwable) {
            // settings pode ainda não existir em migrate fresca
        }

        View::composer('laguna-ambiente::*', function ($view): void {
            $view->with('identidade', Identidade::tokens());
        });

        View::composer(['layout.default', 'layout.base'], function ($view): void {
            $view->with([
                'temaBuritiAtivo' => TemaBuriti::ativo(),
                'temaBuritiBodyClass' => TemaBuriti::bodyClass(),
                'temaBuritiAssets' => TemaBuriti::assetUrls(),
            ]);
        });
    }

    private function publicarAssetsTema(string $root): void
    {
        $origem = $root.'/resources/assets/buriti-tema';
        $destino = public_path('vendor/buriti-tema');

        $this->publishes([$origem => $destino], 'buriti-tema');

        if (! is_dir($origem)) {
            return;
        }

        try {
            if (! is_dir($destino)) {
                File::ensureDirectoryExists($destino);
            }
            foreach (['tokens.css', 'skin-core.css'] as $arquivo) {
                $src = $origem.'/'.$arquivo;
                $dst = $destino.'/'.$arquivo;
                if (! is_file($src)) {
                    continue;
                }
                if (! is_file($dst) || filemtime($src) > filemtime($dst)) {
                    File::copy($src, $dst);
                }
            }
        } catch (\Throwable) {
            // publicação best-effort (permissões / ambiente read-only)
        }
    }
}
