<?php

namespace App\Model\Service;

use App\Core\Config;
use InvalidArgumentException;

/**
 * Upload da foto do item (requisito bônus).
 *
 * Segurança:
 * - o tipo é verificado pelo CONTEÚDO do arquivo (getimagesize), não pela extensão enviada;
 * - o nome do arquivo é gerado aleatoriamente (o nome original é descartado);
 * - a pasta uploads/ tem .htaccess que impede executar PHP.
 */
class FotoUpload
{
    private string $pasta;

    public function __construct(?string $pasta = null)
    {
        $this->pasta = $pasta ?? dirname(__DIR__, 3) . '/public/uploads';
    }

    /**
     * Salva a foto enviada no campo do formulário.
     * Retorna o nome do arquivo salvo, ou null se nenhum arquivo foi enviado.
     */
    public function salvar(?array $arquivo): ?string
    {
        if ($arquivo === null || ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException($this->mensagemDeErro($arquivo['error']));
        }

        $tamanhoMaximo = (int) Config::get('upload.tamanho_maximo', 2 * 1024 * 1024);
        if ($arquivo['size'] > $tamanhoMaximo) {
            throw new InvalidArgumentException('A foto deve ter no máximo ' . round($tamanhoMaximo / 1048576, 1) . ' MB.');
        }

        if (!is_uploaded_file($arquivo['tmp_name'])) {
            throw new InvalidArgumentException('Upload inválido.');
        }

        // getimagesize() lê o cabeçalho do arquivo: se não for imagem de verdade, retorna false.
        // Assim, um "virus.php" renomeado para "foto.jpg" é recusado.
        $tipos  = Config::get('upload.tipos_permitidos', []);
        $imagem = getimagesize($arquivo['tmp_name']);

        if ($imagem === false || !isset($tipos[$imagem['mime']])) {
            throw new InvalidArgumentException('Formato de imagem não suportado. Envie JPG, PNG ou WEBP.');
        }

        if (!is_dir($this->pasta) && !mkdir($this->pasta, 0775, true)) {
            throw new \RuntimeException('Não foi possível criar a pasta de uploads.');
        }

        $nome = bin2hex(random_bytes(16)) . '.' . $tipos[$imagem['mime']];

        if (!move_uploaded_file($arquivo['tmp_name'], $this->pasta . '/' . $nome)) {
            throw new \RuntimeException('Não foi possível salvar a foto. Verifique a permissão da pasta public/uploads.');
        }

        return $nome;
    }

    public function remover(?string $nome): void
    {
        // basename() impede caminhos como "../../config/config.php"
        if ($nome === null || $nome === '' || $nome !== basename($nome)) {
            return;
        }

        $caminho = $this->pasta . '/' . $nome;
        if (is_file($caminho)) {
            unlink($caminho);
        }
    }

    private function mensagemDeErro(int $codigo): string
    {
        return match ($codigo) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'A foto é maior do que o servidor permite.',
            UPLOAD_ERR_PARTIAL => 'O envio da foto foi interrompido. Tente novamente.',
            default => 'Não foi possível enviar a foto.',
        };
    }
}
