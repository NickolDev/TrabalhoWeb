<?php

namespace App\Controller;

use App\Core\Controller;
use App\Core\HttpException;
use App\Model\DAO\FotoDAO;

/** Entrega a imagem guardada no banco para a tag <img>. */
class FotoController extends Controller
{
    public function mostrar(string $arquivo): void
    {
        $foto = (new FotoDAO())->buscar($arquivo);

        if ($foto === null) {
            throw HttpException::naoEncontrado('Foto não encontrada.');
        }

        header('Content-Type: ' . $foto['tipo']);
        // O nome é aleatório e a foto nunca muda: o navegador pode guardar em cache por 1 ano
        header('Cache-Control: public, max-age=31536000, immutable');
        header('X-Content-Type-Options: nosniff');

        echo $foto['dados'];
    }
}
