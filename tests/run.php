#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__.'/../src/Support/CatalogoModulos.php';
require __DIR__.'/../src/Support/Processos.php';
require __DIR__.'/../src/Support/Identidade.php';

$ok = 0;
$fail = 0;
$assert = static function (bool $c, string $m) use (&$ok, &$fail): void {
    echo ($c ? '  OK  ' : ' FAIL ').$m."\n";
    $c ? $ok++ : $fail++;
};

$mods = \iEducar\Packages\BuritiLagunaAmbiente\Support\CatalogoModulos::todos();
$assert(count($mods) >= 10, 'catálogo tem módulos Laguna');
$obrig = array_values(array_filter($mods, static fn ($m) => $m['nivel'] === 'obrigatorio'));
$assert(count($obrig) >= 5, 'há obrigatórios definidos');
$ids = array_column($mods, 'id');
$assert(in_array('alimentacao', $ids, true), 'alimentação no catálogo');
$assert(in_array('apps-api', $ids, true), 'apps-api no catálogo');
$assert(in_array('transporte', $ids, true), 'transporte no catálogo');

$assert(\iEducar\Packages\BuritiLagunaAmbiente\Support\Processos::RAIZ === 889900, 'faixa processos admin');
$assert(\iEducar\Packages\BuritiLagunaAmbiente\Support\Identidade::COR === '#16384a', 'cor identidade');

echo "\nResultado: $ok ok, $fail falhas\n";
exit($fail > 0 ? 1 : 0);
