<?php

namespace Cerbero\Sdk;

use Cerbero\Sdk\Exception\UserNotAuthenticated;
use Cerbero\Sdk\Exception\UserNotAuthorized;
use Cerbero\Sdk\Exception\UserOrPasswordInvalid;
use Cerbero\Sdk\Support\Enum\PermissionStatus;
use Cerbero\Sdk\Support\Enum\ProfileStatus;
use Cerbero\Sdk\Support\Enum\RelationStatus;
use Cerbero\Sdk\Support\Enum\SystemStatus;
use Cerbero\Sdk\Support\Enum\UserStatus;
use PDO;

final class Cerbero
{
    private ?PDO $pdo = null;
    
    public function __construct(
        public readonly array $config = []
    ) {
        $this->pdo = new PDO(
            $this->config['pdoDsn'],
            $this->config['pdoUser'],
            $this->config['pdoPass'],
            $this->config['pdoOptions']
        );
    }
    
    public function authenticated(string $userId, string $sessionToken): bool {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM crb_users WHERE id = :id AND session_token = :session_token;');
        $stmt->execute([
            ':id' => $userId,
            ':session_token' => $sessionToken
        ]);
        return (bool) $stmt->fetchColumn(0);
    }
    
    public function access(string $userId, string $sessionToken, string $systemSlug): bool {
        if(!$this->authenticated($userId, $sessionToken)) throw new UserNotAuthenticated($userId);
        
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
    
    public function authorizated(string $userId, string $sessionToken, string $systemSlug, string $permissionSlug): bool {
        if(!$this->access($userId, $sessionToken, $systemSlug)) throw new UserNotAuthorized($userId, $systemSlug);
        
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
    
    private function profileAuthorizated(string $userId, string $systemSlug, string $permissionSlug): bool {
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
    
    public function authenticate(string $userId, string $password): string {
        $stmt = $this->pdo->prepare('SELECT password_hash FROM crb_users WHERE id = :id;');
        $stmt->execute([
            ':id' => $userId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(($result === false) || (password_verify($password, $result['password_hash']) == false)) throw new UserOrPasswordInvalid($userId);
        
        $sessionToken = uniqid(prefix: '', more_entropy: true);
        $stmt = $this->pdo->prepare('UPDATE crb_users SET session_token = :session_token WHERE id = :id AND status = :status;');
        $stmt->execute([
            ':id' => $userId,
            ':session_token' => $sessionToken,
            ':status' => UserStatus::Active->value
        ]);
        return $sessionToken;
    }
    
    public function unauthenticate(string $userId): void {
        $stmt = $this->pdo->prepare('UPDATE crb_users SET session_token = NULL WHERE id = :id;');
        $stmt->execute([
            ':id' => $userId
        ]);
    }
    
    public function checkSessionToken(?string $sessionToken): bool {
        $stmt = $this->pdo->prepare('SELECT count(*) FROM crb_users WHERE session_token = :session_token;');
        $stmt->execute([
            ':session_token' => $sessionToken
        ]);
        return (bool) $stmt->fetchColumn(0);
    }
}