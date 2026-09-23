<?php

namespace App\Model\DAO;

use App\Core\Database;
use PDO;

/**
 * Base dos DAOs (Data Access Objects).
 * Regra do projeto: TODA query usa prepare() + execute() com parâmetros,
 * nunca concatenação de valores vindos do usuário.
 */
abstract class DAO
{
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::conexao();
    }
}
