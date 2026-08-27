<?php

namespace Cerbero\Sdk\Support\Helper;

use PDO;

/**
 * Classe utilitária para operações auxiliares de usuário.
 *
 * Fornece métodos estáticos para gerenciar a contagem e o controle
 * de tentativas consecutivas de login de usuários no banco de dados.
 *
 * @package Cerbero\Sdk\Support\Helper
 */
final class UserHelper
{
    /**
     * Incrementa e registra uma nova tentativa de login para o usuário informado.
     *
     * @param PDO $pdo Instância de conexão PDO com o banco de dados.
     * @param string $userId Identificador único do usuário.
     * @return int Quantidade total atualizada de tentativas de login registradas.
     */
    public static function registerLoginAttempt(PDO $pdo, string $userId): int
    {
        $stmt = $pdo->prepare('SELECT login_attempts FROM crb_users WHERE id = :user_id;');
        $stmt->execute([
            ':user_id' => $userId,
        ]);

        $attempts = (int) $stmt->fetchColumn(0);
        $attempts++;

        $stmt = $pdo->prepare('UPDATE crb_users SET login_attempts = :attempts WHERE id = :user_id;');
        $stmt->execute([
            ':user_id' => $userId,
            ':attempts' => $attempts,
        ]);

        return $attempts;
    }

    /**
     * Redefine o contador de tentativas de login do usuário para zero.
     *
     * @param PDO $pdo Instância de conexão PDO com o banco de dados.
     * @param string $userId Identificador único do usuário.
     * @return void
     */
    public static function resetLoginAttempt(PDO $pdo, string $userId): void
    {
        $stmt = $pdo->prepare('UPDATE crb_users SET login_attempts = :attempts WHERE id = :user_id;');
        $stmt->execute([
            ':user_id' => $userId,
            ':attempts' => 0,
        ]);
    }
}