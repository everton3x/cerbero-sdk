<?php

namespace Cerbero\Sdk;

use Cerbero\Sdk\Exception\LimitLoginAttempts;
use Cerbero\Sdk\Exception\UserNotAuthenticated;
use Cerbero\Sdk\Exception\UserNotAuthorized;
use Cerbero\Sdk\Exception\UserOrPasswordInvalid;
use Cerbero\Sdk\Support\Enum\PermissionStatus;
use Cerbero\Sdk\Support\Enum\ProfileStatus;
use Cerbero\Sdk\Support\Enum\RelationStatus;
use Cerbero\Sdk\Support\Enum\SystemStatus;
use Cerbero\Sdk\Support\Enum\UserStatus;
use Cerbero\Sdk\Support\Helper\UserHelper;
use PDO;
use RuntimeException;

/**
 * Classe principal do SDK Cerbero.
 *
 * Fornece métodos para autenticação de usuários, verificação de sessão,
 * validação de acesso a sistemas e autorização de permissões (diretas ou por perfil).
 *
 * @package Cerbero\Sdk
 */
final class Cerbero
{
    /**
     * Instância de conexão PDO com o banco de dados.
     *
     * @var \PDO|null
     */
    private ?PDO $pdo = null;

    /**
     * Construtor da classe Cerbero.
     *
     * Inicializa a conexão PDO com base nas configurações fornecidas.
     *
     * @param array{
     *     pdoDsn: string,
     *     pdoUser?: string|null,
     *     pdoPass?: string|null,
     *     pdoOptions?: array<int|string, mixed>|null,
     *     maxLoginAttempts?: int|null
     * } $config Array associativo com as opções de conexão PDO (pdoDsn, pdoUser, pdoPass, pdoOptions).
     */
    public function __construct(
        public readonly array $config
    ) {
        $this->pdo = new PDO(
            $this->config['pdoDsn'],
            $this->config['pdoUser'] ?? null,
            $this->config['pdoPass'] ?? null,
            $this->config['pdoOptions'] ?? null
        );
    }
    
    /**
     * Verifica se o usuário está autenticado com um token de sessão válido e ativo.
     *
     * @param string $userId Identificador único do usuário.
     * @param string $sessionToken Token de sessão a ser validado.
     * @return bool Retorna true se o usuário e o token de sessão forem válidos e estiverem ativos; caso contrário, false.
     */
    public function authenticated(string $userId, string $sessionToken): bool {
        if(is_null($this->pdo)) throw new RuntimeException('No database connection');
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM crb_users WHERE id = :id AND session_token = :session_token AND status = :status;');
        $stmt->execute([
            ':id' => $userId,
            ':session_token' => $sessionToken,
            ':status' => UserStatus::Active->value
        ]);
        return (bool) $stmt->fetchColumn(0);
    }
    
    /**
     * Verifica se o usuário autenticado possui vínculo e permissão de acesso a um determinado sistema.
     *
     * @param string $userId Identificador único do usuário.
     * @param string $sessionToken Token de sessão do usuário.
     * @param string $systemSlug Identificador slug do sistema.
     * @return bool Retorna true se o usuário tiver acesso ativo ao sistema especificado; caso contrário, false.
     * @throws UserNotAuthenticated Lançada se o usuário não estiver autenticado ou a sessão for inválida.
     */
    public function access(string $userId, string $sessionToken, string $systemSlug): bool {
        if(!$this->authenticated($userId, $sessionToken)) throw new UserNotAuthenticated($userId);
        if(is_null($this->pdo)) throw new RuntimeException('No database connection');
        $stmt = $this->pdo->prepare('SELECT count(*) FROM crb_user_system r, crb_users u, crb_systems s WHERE r.user_id = :user_id AND r.system_slug = :system_slug AND r.status = :rstatus AND u.status = :ustatus AND s.status = :sstatus AND r.user_id = u.id AND r.system_slug = s.slug;');
        $stmt->execute([
            ':user_id' => $userId,
            ':system_slug' => $systemSlug,
            ':rstatus' => RelationStatus::Active->value,
            ':ustatus' => UserStatus::Active->value,
            ':sstatus' => SystemStatus::Active->value
        ]);
        return (bool) $stmt->fetchColumn(0);
        
    }
    
    /**
     * Verifica se o usuário autenticado está autorizado para uma permissão específica em um sistema.
     *
     * A verificação avalia primeiramente permissões diretas associadas ao usuário e,
     * caso não encontre, avalia as permissões concedidas por meio de perfis vinculados ao usuário.
     *
     * @param string $userId Identificador único do usuário.
     * @param string $sessionToken Token de sessão do usuário.
     * @param string $systemSlug Identificador slug do sistema.
     * @param string $permissionSlug Identificador slug da permissão solicitada.
     * @return bool Retorna true se o usuário possuir a permissão (direta ou por perfil); caso contrário, false.
     * @throws UserNotAuthorized Lançada se o usuário não tiver acesso concedido ao sistema.
     */
    public function authorizated(string $userId, string $sessionToken, string $systemSlug, string $permissionSlug): bool {
        if(!$this->access($userId, $sessionToken, $systemSlug)) throw new UserNotAuthorized($userId, $systemSlug);
        if(is_null($this->pdo)) throw new RuntimeException('No database connection');
        $stmt = $this->pdo->prepare('SELECT count(*) FROM crb_user_permission r, crb_users u, crb_systems s, crb_permissions p WHERE r.user_id = :user_id AND r.system_slug = :system_slug AND r.permission_slug = :permission_slug AND r.status = :rstatus AND u.status = :ustatus AND s.status = :sstatus AND p.status = :pstatus AND r.user_id = u.id AND r.system_slug = s.slug AND r.permission_slug = p.slug;');
        $stmt->execute([
            ':user_id' => $userId,
            ':system_slug' => $systemSlug,
            ':permission_slug' => $permissionSlug,
            ':rstatus' => RelationStatus::Active->value,
            ':ustatus' => UserStatus::Active->value,
            ':sstatus' => SystemStatus::Active->value,
            ':pstatus' => PermissionStatus::Active->value
        ]);
        if((bool) $stmt->fetchColumn(0)) return true;
        
        return $this->profileAuthorizated($userId, $systemSlug, $permissionSlug);
    }
    
    /**
     * Verifica se o usuário possui a permissão requerida no sistema por meio de perfis associados.
     *
     * @param string $userId Identificador único do usuário.
     * @param string $systemSlug Identificador slug do sistema.
     * @param string $permissionSlug Identificador slug da permissão solicitada.
     * @return bool Retorna true se o usuário possuir a permissão via perfil ativo; caso contrário, false.
     */
    private function profileAuthorizated(string $userId, string $systemSlug, string $permissionSlug): bool {
        if(is_null($this->pdo)) throw new RuntimeException('No database connection');
        $stmt = $this->pdo->prepare('SELECT count(*) FROM crb_profile_permission pp INNER JOIN crb_user_profile up ON up.system_slug = pp.system_slug AND up.profile_slug = pp.profile_slug AND up.user_id = :user_id AND up.status = :rstatus INNER JOIN crb_permissions p ON p.system_slug = pp.system_slug AND p.slug = pp.permission_slug AND p.status = :pstatus INNER JOIN crb_profiles pr ON pr.system_slug = pp.system_slug AND pr.slug = pp.profile_slug AND pr.status = :prstatus INNER JOIN crb_systems s ON s.slug = pp.system_slug AND s.status = :sstatus INNER JOIN crb_user_profile usp ON usp.system_slug = pp.system_slug AND usp.profile_slug = pp.profile_slug AND usp.status = :rstatus INNER JOIN crb_user_system us ON us.system_slug = pp.system_slug AND us.user_id = up.user_id AND us.status = :rstatus INNER JOIN crb_users u ON u.id = up.user_id AND u.status = :ustatus WHERE pp.system_slug = :system_slug AND pp.permission_slug = :permission_slug AND pp.status = :rstatus;');
        $stmt->execute([
            ':user_id' => $userId,
            ':system_slug' => $systemSlug,
            ':permission_slug' => $permissionSlug,
            ':rstatus' => RelationStatus::Active->value,
            ':ustatus' => UserStatus::Active->value,
            ':sstatus' => SystemStatus::Active->value,
            ':pstatus' => PermissionStatus::Active->value,
            ':prstatus' => ProfileStatus::Active->value
        ]);
        return (bool) $stmt->fetchColumn(0);
    }
    
    /**
     * Realiza a autenticação de um usuário validando suas credenciais (ID e senha).
     *
     * Se a senha corresponder ao hash registrado, um novo token de sessão é gerado,
     * persistido no banco de dados e retornado.
     *
     * @param string $userId Identificador único do usuário.
     * @param string $password Senha do usuário em texto plano para conferência com o hash armazenado.
     * @return string Retorna o novo token de sessão gerado.
     * @throws UserOrPasswordInvalid Lançada se o usuário não for encontrado ou a senha for inválida.
     * @throws LimitLoginAttempts Lançada se o número máximo de tentativas de login for excedido.
     */
    public function authenticate(string $userId, string $password): string {
        if(is_null($this->pdo)) throw new RuntimeException('No database connection');

        if(isset($this->config['maxLoginAttempts'])){
            $attempts = UserHelper::registerLoginAttempt($this->pdo, $userId);
            if($attempts > $this->config['maxLoginAttempts']) throw new LimitLoginAttempts($userId);
        }

        $stmt = $this->pdo->prepare('SELECT password_hash FROM crb_users WHERE id = :id;');
        $stmt->execute([
            ':id' => $userId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!is_array($result) || !isset($result['password_hash']) || !is_string($result['password_hash'])) throw new UserOrPasswordInvalid($userId);
        if(password_verify($password, $result['password_hash']) == false) throw new UserOrPasswordInvalid($userId);
        
        $sessionToken = uniqid(prefix: '', more_entropy: true);
        $stmt = $this->pdo->prepare('UPDATE crb_users SET session_token = :session_token WHERE id = :id AND status = :status;');
        $stmt->execute([
            ':id' => $userId,
            ':session_token' => $sessionToken,
            ':status' => UserStatus::Active->value
        ]);
        UserHelper::resetLoginAttempt($this->pdo, $userId);
        return $sessionToken;
    }
    
    /**
     * Encerra a sessão do usuário, invalidando o token de sessão atual no banco de dados.
     *
     * @param string $userId Identificador único do usuário.
     * @return void
     */
    public function unauthenticate(string $userId): void {
        if(is_null($this->pdo)) throw new RuntimeException('No database connection');
        $stmt = $this->pdo->prepare('UPDATE crb_users SET session_token = NULL WHERE id = :id;');
        $stmt->execute([
            ':id' => $userId
        ]);
    }
    
    /**
     * Verifica se um token de sessão está registrado para algum usuário.
     *
     * @param string|null $sessionToken Token de sessão a ser consultado.
     * @return bool Retorna true se o token existir e estiver atribuído a um usuário; caso contrário, false.
     */
    public function checkSessionToken(?string $sessionToken): bool {
        if(is_null($this->pdo)) throw new RuntimeException('No database connection');
        $stmt = $this->pdo->prepare('SELECT count(*) FROM crb_users WHERE session_token = :session_token;');
        $stmt->execute([
            ':session_token' => $sessionToken
        ]);
        return (bool) $stmt->fetchColumn(0);
    }
}