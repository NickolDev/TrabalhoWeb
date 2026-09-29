<?php

namespace App\Model\Service;

use App\Core\Config;
use App\Model\DAO\FotoDAO;
use InvalidArgumentException;

/**
 * Upload da foto do item (requisito bônus).
 *
 * A imagem é guardada no banco (tabela `fotos`), e não numa pasta, porque
 * na Vercel o disco é somente leitura. Ela é exibida pela rota /fotos/{arquivo}.
 *
 * Segurança:
 * - o tipo é verificado pelo CONTEÚDO do arquivo (getimagesize), não pela extensão enviada;
 * - o nome é gerado aleatoriamente (o nome original é descartado);
 * - como nada é gravado em disco, não há como enviar um .php e executá-lo.
 */
class FotoUpload
{
    private FotoDAO $fotos;

    public function __construct()
    {
        $this->fotos = new FotoDAO();
    }

    /**
     * Salva a foto enviada no campo do formulário.
     * Retorna o nome gerado para a foto, ou null se nenhum arquivo foi enviado.
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

        $nome = bin2hex(random_bytes(16)) . '.' . $tipos[$imagem['mime']];

        $this->fotos->salvar($nome, $imagem['mime'], file_get_contents($arquivo['tmp_name']));

        return $nome;
    }

    public function remover(?string $nome): void
    {
        if ($nome !== null && $nome !== '') {
            $this->fotos->remover($nome);
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
