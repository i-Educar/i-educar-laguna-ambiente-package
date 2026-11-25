<?php

namespace iEducar\Packages\BuritiLagunaAmbiente\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Upload padronizado (PDF e imagens) para módulos Buriti.
 */
final class AnexoFluxo
{
    public const string DISK = 'local';

    /**
     * @param  list<string>  $extensoes
     * @return array{nome:string,path:string,extensao:string,mime:string,tamanho:int}
     */
    public static function salvar(
        UploadedFile $file,
        string $prefixo,
        array $extensoes = ['pdf'],
        int $maxKb = 5120,
    ): array {
        $ext = mb_strtolower($file->getClientOriginalExtension() ?: 'bin');
        $permitidas = array_map('strtolower', $extensoes);
        if ($permitidas !== [] && ! in_array($ext, $permitidas, true)) {
            throw new \InvalidArgumentException('Extensão .'.$ext.' não permitida ('.implode(', ', $permitidas).').');
        }
        if ($file->getSize() > $maxKb * 1024) {
            throw new \InvalidArgumentException("Arquivo excede {$maxKb} KB.");
        }

        $nome = $file->getClientOriginalName();
        $base = Str::slug(pathinfo($nome, PATHINFO_FILENAME)) ?: 'anexo';
        $path = $file->storeAs(
            trim($prefixo, '/'),
            Str::uuid()->toString().'-'.$base.'.'.$ext,
            self::DISK
        );
        if ($path === false) {
            throw new \RuntimeException('Falha ao gravar anexo.');
        }

        return [
            'nome' => $nome,
            'path' => $path,
            'extensao' => $ext,
            'mime' => (string) $file->getMimeType(),
            'tamanho' => (int) $file->getSize(),
        ];
    }

    public static function caminhoAbsoluto(string $path): ?string
    {
        if ($path === '' || ! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        return Storage::disk(self::DISK)->path($path);
    }
}
