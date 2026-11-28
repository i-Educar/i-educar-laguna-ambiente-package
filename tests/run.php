#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__.'/../src/Support/CatalogoModulos.php';
require __DIR__.'/../src/Support/Processos.php';
require __DIR__.'/../src/Support/Identidade.php';
require __DIR__.'/../src/Support/PermissaoMatriz.php';
require __DIR__.'/../src/Support/TemaBuriti.php';
require __DIR__.'/../src/Support/AprovacaoModulo.php';

use iEducar\Packages\BuritiLagunaAmbiente\Support\AprovacaoModulo;
use iEducar\Packages\BuritiLagunaAmbiente\Support\CatalogoModulos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\Identidade;
use iEducar\Packages\BuritiLagunaAmbiente\Support\PermissaoMatriz;
use iEducar\Packages\BuritiLagunaAmbiente\Support\Processos;
use iEducar\Packages\BuritiLagunaAmbiente\Support\TemaBuriti;

$ok = 0;
$fail = 0;
$assert = static function (bool $c, string $m) use (&$ok, &$fail): void {
    echo ($c ? '  OK  ' : ' FAIL ').$m."\n";
    $c ? $ok++ : $fail++;
};

$mods = CatalogoModulos::todos();
$assert(count($mods) >= 10, 'catálogo tem módulos Laguna');
$obrig = array_values(array_filter($mods, static fn ($m) => $m['nivel'] === 'obrigatorio'));
$assert(count($obrig) >= 5, 'há obrigatórios definidos');
$ids = array_column($mods, 'id');
$assert(in_array('alimentacao', $ids, true), 'alimentação no catálogo');
$assert(in_array('apps-api', $ids, true), 'apps-api no catálogo');
$assert(in_array('transporte', $ids, true), 'transporte no catálogo');

$assert(Processos::RAIZ === 889900, 'faixa processos admin');
$assert(Processos::MODULOS === 889910, 'módulos em configurações');
$assert(Processos::CHECK === 889914, 'check separado de módulos');
$assert(Identidade::COR === '#1a5f9e', 'cor identidade');

$perfis = PermissaoMatriz::perfis();
$assert(count($perfis) >= 15, 'matriz tem perfis práticos');
$admin = $perfis[0];
$assert($admin['cod'] === 1 && $admin['modulos'] === '*', 'admin com acesso total');
$assert(
    in_array('laguna-ambiente', $perfis[1]['excluir_modulos'] ?? [], true),
    'secretário sem Ambiente Laguna'
);

$almox = null;
foreach ($perfis as $p) {
    if ((int) ($p['cod'] ?? 0) === 8) {
        $almox = $p;
        break;
    }
}
$assert($almox !== null, 'perfil operador almox existe');
$modsAlmox = PermissaoMatriz::modulosComDependencias($almox['modulos']);
$assert(is_array($modsAlmox) && in_array('exportacao', $modsAlmox, true), 'almox puxa dependência exportação');
$assert(in_array('almoxarifado', $modsAlmox, true), 'almox mantém módulo origem');
$deps = PermissaoMatriz::dependenciasModulos();
$assert(($deps['biblioteca'][0] ?? '') === 'exportacao', 'biblioteca depende de exportação');
$assert(($deps['alimentacao'][0] ?? '') === 'exportacao', 'alimentação depende de exportação');
$assert(PermissaoMatriz::TIPO_DIRETOR === 3, 'constante diretor = 3');
$assert(PermissaoMatriz::TIPO_COORDENADOR === 4, 'constante coordenador = 4');

/* —— Skins / TemaBuriti —— */
$skins = TemaBuriti::catalogo();
$assert(count($skins) === 6, 'catálogo de skins = 6 (nenhum+5)');
foreach (['nenhum', 'buriti', 'ebano', 'horizonte', 'orbita', 'litoral'] as $sid) {
    $assert(isset($skins[$sid]), "skin {$sid} existe");
}
$assert(($skins['ebano']['conceito'] ?? '') === 'Elegante', 'Ébano elegante');
$assert(($skins['horizonte']['conceito'] ?? '') === 'Moderna', 'Horizonte moderna');
$assert(($skins['orbita']['conceito'] ?? '') === 'Futurista', 'Órbita futurista');
$assert(($skins['litoral']['conceito'] ?? '') === 'Elegante municipal', 'Litoral municipal');
$assert(($skins['buriti']['conceito'] ?? '') === 'Institucional', 'Buriti institucional');

foreach (['buriti', 'ebano', 'horizonte', 'orbita', 'litoral'] as $sid) {
    $body = (string) ($skins[$sid]['body'] ?? '');
    $assert(str_contains($body, 'tema-buriti-skin'), "{$sid} carrega chrome compartilhado");
    $assert(str_contains($body, 'tema-'.$sid) || str_contains($body, 'tema-buriti-laguna'), "{$sid} tem classe própria");
    $pv = $skins[$sid]['preview'] ?? [];
    $assert(isset($pv['fundo'], $pv['topo'], $pv['lateral'], $pv['acento']), "{$sid} tem prévia completa");
}

$assert(TemaBuriti::normalizarId('0') === TemaBuriti::SKIN_NENHUM, 'normaliza off→nenhum');
$assert(TemaBuriti::normalizarId('true') === 'buriti', 'normaliza true→buriti');
$assert(TemaBuriti::normalizarId('BURITI-LAGUNA') === 'buriti', 'normaliza alias buriti');
$assert(TemaBuriti::normalizarId('Orbita') === 'orbita', 'normaliza orbita');
$assert(TemaBuriti::normalizarId('desconhecida') === 'desconhecida', 'id desconhecido preservado');

$diario = dirname(__DIR__, 3).'/portabilis/i-diario';
if (is_dir($diario)) {
    $flagSkin = $diario.'/.laguna-tema-skin';
    $flagLegado = $diario.'/.laguna-tema-buriti';
    $antesSkin = is_file($flagSkin) ? (string) file_get_contents($flagSkin) : null;
    $antesLegado = is_file($flagLegado);

    TemaBuriti::sincronizarIdiario('orbita');
    $assert(is_file($flagSkin) && trim((string) file_get_contents($flagSkin)) === 'orbita', 'sync i-Diário grava skin orbita');
    $assert(is_file($flagLegado), 'sync i-Diário mantém flag legado');

    TemaBuriti::sincronizarIdiario('nenhum');
    $assert(! is_file($flagSkin), 'sync nenhum remove flag skin');
    $assert(! is_file($flagLegado), 'sync nenhum remove flag legado');

    // restaura estado anterior
    if ($antesSkin !== null) {
        TemaBuriti::sincronizarIdiario(trim($antesSkin) !== '' ? trim($antesSkin) : 'buriti');
    } elseif ($antesLegado) {
        TemaBuriti::sincronizarIdiario('buriti');
    }
} else {
    echo "  SKIP sync i-Diário (pacote ausente)\n";
}

$palettes = dirname(__DIR__).'/resources/assets/buriti-tema/skins-palettes.css';
$assert(is_file($palettes), 'asset skins-palettes.css presente');
$css = (string) file_get_contents($palettes);
$assert(str_contains($css, 'body.tema-orbita'), 'CSS Órbita (dark) definido');
$assert(str_contains($css, 'body.tema-ebano'), 'CSS Ébano definido');
$assert(str_contains($css, 'color-scheme: dark'), 'Órbita declara color-scheme dark');

$idiarioCss = $diario.'/public/laguna/skins.css';
if (is_file($idiarioCss)) {
    $icss = (string) file_get_contents($idiarioCss);
    $assert(str_contains($icss, '#header'), 'i-Diário CSS mira #header SmartAdmin');
    $assert(str_contains($icss, '#left-panel'), 'i-Diário CSS mira #left-panel');
    $assert(str_contains($icss, 'body.tema-orbita'), 'i-Diário tem paleta Órbita');
} else {
    echo "  SKIP CSS i-Diário (arquivo ausente)\n";
}

/* —— Aprovação de módulos —— */
$statuses = AprovacaoModulo::statusValidos();
$assert(count($statuses) === 4, '4 status de aprovação');
foreach (['funciona', 'ressalva', 'reprovado', 'testar'] as $st) {
    $assert(in_array($st, $statuses, true), "status {$st} válido");
    $meta = AprovacaoModulo::meta($st);
    $assert(($meta['status'] ?? '') === $st, "meta {$st} status");
    $assert(($meta['rotulo'] ?? '') !== '', "meta {$st} tem rótulo");
    $assert(($meta['tom'] ?? '') !== '', "meta {$st} tem tom");
}

$assert(AprovacaoModulo::classificarTecnico([]) === AprovacaoModulo::STATUS_FUNCIONA, 'técnico ok sem falhas');
$assert(
    AprovacaoModulo::classificarTecnico([['nome' => 'x', 'ok' => false, 'detalhe' => 'a', 'critico' => true]]) === AprovacaoModulo::STATUS_REPROVADO,
    'técnico crítico → reprovado'
);
$assert(
    AprovacaoModulo::classificarTecnico([['nome' => 'x', 'ok' => false, 'detalhe' => 'a']]) === AprovacaoModulo::STATUS_RESSALVA,
    'técnico mole → ressalva'
);
$assert(
    AprovacaoModulo::classificarTecnico([['nome' => 'x', 'ok' => true, 'detalhe' => 'ok']]) === AprovacaoModulo::STATUS_FUNCIONA,
    'técnico ok com checks verdes'
);

$metaFunciona = AprovacaoModulo::meta(AprovacaoModulo::STATUS_FUNCIONA);
$assert(($metaFunciona['tom'] ?? '') === 'ok', 'tom funciona = ok');
$metaFail = AprovacaoModulo::meta(AprovacaoModulo::STATUS_REPROVADO);
$assert(($metaFail['tom'] ?? '') === 'fail', 'tom reprovado = fail');

$pctVazio = AprovacaoModulo::percentualChecks([]);
$assert($pctVazio['percentual'] === 0 && $pctVazio['total'] === 0, 'percentual vazio = 0');
$pctMeio = AprovacaoModulo::percentualChecks([
    ['nome' => 'a', 'ok' => true],
    ['nome' => 'b', 'ok' => false],
]);
$assert($pctMeio['percentual'] === 50 && $pctMeio['ok'] === 1 && $pctMeio['total'] === 2, 'percentual 1/2 = 50%');
$pctCheio = AprovacaoModulo::percentualChecks([
    ['nome' => 'a', 'ok' => true],
    ['nome' => 'b', 'ok' => true],
    ['nome' => 'c', 'ok' => true],
]);
$assert($pctCheio['percentual'] === 100 && $pctCheio['ok'] === 3, 'percentual 3/3 = 100%');

$painelView = dirname(__DIR__).'/resources/views/painel.blade.php';
$checkView = dirname(__DIR__).'/resources/views/check.blade.php';
$cfgView = dirname(__DIR__).'/resources/views/modulos-config.blade.php';
$layoutView = dirname(__DIR__).'/resources/views/layout.blade.php';
$assert(is_file($painelView) && str_contains((string) file_get_contents($painelView), 'modulo-pct'), 'painel exibe percentual no card');
$assert(is_file($checkView) && str_contains((string) file_get_contents($checkView), 'modulo-pct'), 'check exibe percentual no card');
$checkTxt = (string) file_get_contents($checkView);
$assert(str_contains($checkTxt, '/laguna-ambiente/check/testar'), 'check tem POST para rodar testes');
$assert(str_contains($checkTxt, 'Rodar testes'), 'check tem botão Rodar testes');
$assert(str_contains($checkTxt, 'O que não está pronto'), 'check lista o que não está pronto');
$assert(str_contains($checkTxt, 'Rodar teste deste módulo'), 'check tem botão por módulo');
$assert(is_file($cfgView) && str_contains((string) file_get_contents($cfgView), 'cfg-card-pct'), 'config exibe percentual no card');
$assert(is_file($layoutView) && str_contains((string) file_get_contents($layoutView), '.modulo-pct'), 'layout tem CSS do anel percentual');
$assert(str_contains((string) file_get_contents($layoutView), '.pendencia-item'), 'layout tem CSS de pendências');
$setupView = dirname(__DIR__).'/resources/views/setup.blade.php';
$setupTxt = is_file($setupView) ? (string) file_get_contents($setupView) : '';
$assert(str_contains($setupTxt, 'comandos-artisan'), 'setup tem seção Comandos Artisan');
$assert(str_contains($setupTxt, 'prematricula:povoar'), 'setup documenta prematricula:povoar');
$povoarCmd = dirname(__DIR__, 3).'/portabilis/pre-matricula-digital/src/Console/Commands/PrematriculaPovoarCommand.php';
$povoarSvc = dirname(__DIR__, 3).'/portabilis/pre-matricula-digital/src/Services/PrematriculaPovoarService.php';
if (is_file($povoarCmd) && is_file($povoarSvc)) {
    $assert(true, 'PrematriculaPovoarCommand presente');
    $assert(str_contains((string) file_get_contents($povoarSvc), 'criarProcessos'), 'PrematriculaPovoarService cria processos');
} else {
    echo "  SKIP PrematriculaPovoar (pré-matrícula ausente neste smoke)\n";
}
$assert(str_contains((string) file_get_contents(dirname(__DIR__).'/src/Support/CatalogoModulos.php'), 'prematricula:povoar'), 'catálogo vagas lista prematricula:povoar');
$svcTestes = dirname(__DIR__).'/src/Services/LagunaModuloTestesService.php';
$assert(is_file($svcTestes), 'LagunaModuloTestesService presente');
$assert(str_contains((string) file_get_contents($svcTestes), 'function pendencias'), 'serviço expõe pendencias()');
$routesWeb = dirname(__DIR__).'/routes/web.php';
$assert(is_file($routesWeb) && str_contains((string) file_get_contents($routesWeb), "check/testar"), 'rota POST check/testar registrada');
$ctrl = dirname(__DIR__).'/src/Http/Controllers/AmbienteController.php';
$assert(str_contains((string) file_get_contents($ctrl), 'function testarModulos'), 'controller tem testarModulos');

/* —— TR 5.8 Relatórios (exportação unificada, sem WhatsApp) —— */
$relatoriosTr = dirname(__DIR__, 3).'/portabilis/pre-matricula-digital/src/Services/RelatoriosTrService.php';
$migration58 = dirname(__DIR__, 3).'/portabilis/pre-matricula-digital/database/migrations/2026_11_26_051200_laguna_tr_58_campos.php';
$viewRel = dirname(__DIR__, 3).'/portabilis/pre-matricula-digital/resources/views/relatorios.blade.php';
if (is_file($relatoriosTr) && is_file($migration58) && is_file($viewRel)) {
    $assert(true, 'RelatoriosTrService presente');
    $assert(true, 'migration TR 5.8 presente');
    $assert(true, 'view relatórios TR presente');
} else {
    echo "  SKIP Relatórios TR 5.8 (pré-matrícula ausente neste smoke)\n";
}
$autoload = dirname(__DIR__, 4).'/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}
if (class_exists(\iEducar\Packages\PreMatricula\Services\RelatoriosTrService::class)) {
    $cat = \iEducar\Packages\PreMatricula\Services\RelatoriosTrService::catalogo();
    $assert(count($cat) >= 18, 'catálogo operacional ≥ 18 relatórios');
    $vals = array_column($cat, 'value');
    $assert(in_array('pmd-previsao-matricula', $vals, true), 'catálogo inclui previsão de matrícula');
    $assert(in_array('pmd-carta-renuncia', $vals, true), 'catálogo inclui carta de renúncia');
    $assert(! in_array('pmd-whatsapp', $vals, true), 'catálogo sem WhatsApp');
} else {
    echo "  SKIP RelatoriosTrService (autoload ausente neste smoke)\n";
}
$status58 = dirname(__DIR__, 3).'/portabilis/pre-matricula-digital/docs/laguna/STATUS-5.8-TR.md';
if (is_file($status58)) {
    $assert(true, 'STATUS-5.8-TR.md presente');
    $statusTxt = (string) file_get_contents($status58);
    $assert(str_contains($statusTxt, '95%'), 'STATUS declara meta ≥95%');
    $assert(str_contains($statusTxt, '5.8.33') && str_contains($statusTxt, 'WhatsApp'), 'STATUS documenta skip WhatsApp');
} else {
    echo "  SKIP STATUS-5.8-TR.md (pré-matrícula ausente neste smoke)\n";
}

/* —— Config módulos (percentual / contato) —— */
require_once dirname(__DIR__).'/src/Support/SetorResponsavel.php';
require_once dirname(__DIR__).'/src/Support/RedeApoio.php';
require_once dirname(__DIR__).'/src/Support/DiretivasModulos.php';
$setorDirs = \iEducar\Packages\BuritiLagunaAmbiente\Support\SetorResponsavel::diretivas();
$assert(count($setorDirs) >= 5, 'setor tem diretivas de contato');
$essenciais = array_values(array_filter($setorDirs, static fn ($d) => ! empty($d['essencial'])));
$assert(count($essenciais) >= 3, 'setor marca essenciais (nome/tel/email)');
$chavesEss = array_column($essenciais, 'chave');
$assert(in_array('setor_nome', $chavesEss, true) && in_array('setor_email', $chavesEss, true), 'essenciais incluem nome e e-mail');
$cfgBlade = dirname(__DIR__).'/resources/views/modulos-config.blade.php';
$assert(is_file($cfgBlade) && str_contains((string) file_get_contents($cfgBlade), 'campos_ok'), 'config card usa campos_ok no title');
$assert(str_contains((string) file_get_contents($cfgBlade), 'contato_ok') || str_contains((string) file_get_contents($cfgBlade), 'Contato incompleto'), 'config sinaliza contato incompleto');

/* —— Informes (setor / rede) —— */
require_once dirname(__DIR__).'/src/Support/InformesModulo.php';
use iEducar\Packages\BuritiLagunaAmbiente\Support\InformesModulo;

$assert(InformesModulo::temComplementares('transporte'), 'transporte tem informes complementares');
$assert(InformesModulo::temComplementares('busca-ativa'), 'busca-ativa tem informes complementares');
$assert(InformesModulo::temComplementares('atendimento'), 'atendimento tem informes complementares');
$assert(InformesModulo::temComplementares('alimentacao'), 'alimentação tem informes (setor RT)');
$assert(! InformesModulo::temComplementares('horario'), 'horário automático sem informes complementares');
$assert(InformesModulo::idDaRequisicao('transporte-escolar/rotas') === 'transporte', 'resolve id pela rota transporte');
$assert(InformesModulo::idDaRequisicao('busca-ativa/painel') === 'busca-ativa', 'resolve id pela rota busca-ativa');
$assert(str_contains(InformesModulo::urlConfiguracao('act'), 'modulo=act'), 'URL config aponta módulo');
$assert(str_contains(InformesModulo::urlConfiguracao('act'), 'abrir=1'), 'URL config força abrir modal');
$viewInformes = dirname(__DIR__).'/resources/views/ui/informes-modulo.blade.php';
$assert(is_file($viewInformes), 'view informes-modulo presente');
$informesSrc = (string) file_get_contents($viewInformes);
$assert(str_contains($informesSrc, 'fa-question') && str_contains($informesSrc, 'botao icone ajuda'), 'cabeçalho inclui botão Ajuda (?)');
$assert(is_file(dirname(__DIR__).'/resources/views/ui/informes-modulo-corpo.blade.php'), 'view informes-modulo-corpo presente');
$estilosSrc = (string) file_get_contents(dirname(__DIR__).'/resources/views/ui/estilos.blade.php');
$assert(str_contains($estilosSrc, '.botao.icone') && str_contains($estilosSrc, '.botao.ajuda'), 'estilos padronizam botão ícone e ajuda');
$exportPartial = dirname(__DIR__, 2).'/i-educar-exportacao-unificada-package/resources/views/partials/botoes-exportar.blade.php';
$exportSrc = is_file($exportPartial) ? (string) file_get_contents($exportPartial) : '';
if ($exportSrc !== '') {
    $assert(str_contains($exportSrc, 'informes-modulo'), 'Download inclui Informes');
    $assert(str_contains($exportSrc, 'botao icone') && str_contains($exportSrc, 'aria-label'), 'Download só com ícone');
} else {
    echo "  SKIP partial exportação (pacote ausente neste smoke)\n";
}
$assert(InformesModulo::ajudaJaRenderizada() === false, 'ajuda ainda não renderizada no boot do teste');
InformesModulo::marcarAjudaRenderizada();
$assert(InformesModulo::ajudaJaRenderizada() === true, 'ajuda marcada como renderizada');

/* —— Providers no config/app.php (cenário pós-reinstalação do BI) —— */
$appConfig = dirname(__DIR__, 4).'/config/app.php';
if (is_file($appConfig)) {
    $appPhp = (string) file_get_contents($appConfig);
    $assert(str_contains($appPhp, 'BisProvider'), 'config/app.php mantém BisProvider');
    $assert(str_contains($appPhp, 'garantirProvidersCatalogo') || str_contains($appPhp, 're-registra o catálogo'), 'config/app.php documenta proteção pós-BI');
    foreach ($mods as $mod) {
        $admin = trim((string) ($mod['admin_url'] ?? ''), '/');
        $provider = (string) ($mod['provider'] ?? '');
        if ($admin === '' || $provider === '') {
            continue;
        }
        $short = str_contains($provider, '\\') ? substr($provider, strrpos($provider, '\\') + 1) : $provider;
        $assert(
            str_contains($appPhp, $short) || str_contains($appPhp, $provider),
            "config/app.php lista provider {$short} ({$mod['id']})"
        );
    }
    $providerFile = dirname(__DIR__).'/src/Providers/LagunaAmbienteServiceProvider.php';
    $provSrc = is_file($providerFile) ? (string) file_get_contents($providerFile) : '';
    $assert(str_contains($provSrc, 'garantirProvidersCatalogo'), 'LagunaAmbiente re-registra providers do catálogo');
} else {
    echo "  SKIP config/app.php (fora do monorepo)\n";
}

echo "\nResultado: $ok ok, $fail falhas\n";
exit($fail > 0 ? 1 : 0);
