<?php

namespace Cerbero\Core;

use PDO;
use Cerbero\Core\Db\Database;
use Cerbero\Core\Entity\User;
use Cerbero\Core\Config\Config;
use Cerbero\Exception\DbException;
use Cerbero\Core\Config\ConfigLoader;
use Cerbero\Core\Enum\RoleStatusEnum;
use Cerbero\Core\Enum\UserStatusEnum;
use Cerbero\Exception\TokenException;
use Cerbero\Core\Enum\GroupStatusEnum;
use Cerbero\Core\Enum\UserRoleStatusEnum;
use Cerbero\Exception\ForbiddenException;
use Cerbero\Core\Enum\GroupRoleStatusEnum;
use Cerbero\Core\Enum\UserGroupStatusEnum;
use Cerbero\Core\Enum\UserSystemStatusEnum;
use Cerbero\Exception\UserInactiveException;
use Cerbero\Exception\UserNotFoundException;
use Cerbero\Core\Message\AuthenticateMessage;
use Cerbero\Exception\NotAcceptableException;
use Cerbero\Exception\AuthenticationException;
use Cerbero\Exception\IncorrectPasswordException;

/**
 * Wrapper para as funções do Cerbero.
 */
final class Cerbero
{
    private static ?Config $config = null;
    private static ?PDO $dbh = null;

    /**
     * Inicia o Cerbero.
     *
     * @param string $configFile
     * @return void
     */
    public static function init(string $configFile): void
    {
        self::$config = ConfigLoader::loadFromIniFile($configFile);
        self::$dbh = Database::getHandler(
            dsn:        self::$config->dbDsn,
            user:       self::$config->dbUser,
            password:   self::$config->dbPassword
        );
    }

    /**
     * Existe usuário autenticado (logado)?
     *
     * @return boolean
     */
    public static function hasUserAuthenticated(): bool
    {
        // verifica se tem as chaves de sessão necessárias

        if(!key_exists('cerbero', $_SESSION)) return false;
        if(!key_exists('identity', $_SESSION['cerbero'])) return false;
        if(!key_exists('utoken', $_SESSION['cerbero'])) return false;

        // verifica se usuário e token correspondem no db
        $user = self::findUserByIdentity(Cerbero::identity());
        if(is_null($user)) return false;

        return true;
    }

    /**
     * Autentica/loga um usuário.
     *
     * @param string $identity
     * @param string $password
     * @return AuthenticateMessage
     */
    public static function authenticate(string $identity, string $password): AuthenticateMessage
    {
        $success = true;
        $utoken = null;
        $errors = [];
        
        // busca o usuário, se houver
        $user = self::findUserByIdentity($identity);
        if(is_null($user)) {
            $errors[] = new UserNotFoundException($identity);
            $success = false;
        }

        // verifica se usuário não está inativo
        if(!is_null($user) && $user->status === UserStatusEnum::Inactive){
            $errors[] = new UserInactiveException($identity);
            $success = false;
        }

        // testa a senha
        if($success){
            if(!password_verify($password, $user->passwordHash)){
                $errors[] = new IncorrectPasswordException($identity);
                $success = false;
            }
        }
        
        if($success){
            // salva um token e os dados na sessão
            $utoken = self::generateUserToken();
            self::updateUserToken($identity, $utoken);

            self::updateSession($identity, $utoken, $errors);
        }
        
        // retorna o resultado
        return new AuthenticateMessage($success, $identity, $utoken, $errors);
    }

    /**
     * Desautentica/logoff do usuário.
     *
     * @param string $identity
     * @return AuthenticateMessage
     */
    public static function unauthenticate(string $identity): AuthenticateMessage
    {
        $success = true;
        $utoken = null;
        $errors = [];
        
        // busca o usuário, se houver
        $user = self::findUserByIdentity($identity);
        if(is_null($user)) {
            $errors[] = new UserNotFoundException($identity);
            $success = false;
        }

        if($success){
            // salva um token e os dados na sessão
            $utoken = self::generateUserToken();
            self::updateUserToken($identity, null);

            self::closeSession();
        }
        
        // retorna o resultado
        return new AuthenticateMessage($success, $identity, $utoken, $errors);
    }

    private static function closeSession(): void
    {
        unset($_SESSION['cerbero']);
    }

    private static function generateUserToken(): string
    {
        return sha1(uniqid(prefix: microtime(), more_entropy: true));
    }

    private static function hashPassword(string $password): string
    {
        return password_hash($password, algo: PASSWORD_BCRYPT);
    }

    private static function findUserByIdentity(string $identity): ?User
    {
        try {
            return new User(self::$dbh, $identity);
        } catch (UserNotFoundException $ex) {
            return null;
        }
    }

    private static function updateUserToken(string $identity, ?string $utoken): void
    {
        $sth = self::$dbh->prepare('UPDATE users SET utoken = :utoken WHERE identity = :identity;');
        if($sth === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $sth->bindValue('identity', $identity, PDO::PARAM_STR);
        $sth->bindValue('utoken', $utoken, PDO::PARAM_STR);
        if($sth->execute() === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        if($sth->rowCount() === 0) throw new TokenException($utoken);
    }

    private static function updateSession(?string $identity, ?string $utoken, array $errors): void
    {
        $_SESSION['cerbero']['identity'] = $identity;
        $_SESSION['cerbero']['utoken'] = $utoken;
        $_SESSION['cerbero']['errors'] = $errors;
    }

    /**
     * Identidade do usuário atual.
     *
     * @return string|null
     */
    public static function identity(): ?string
    {
        return $_SESSION['cerbero']['identity'] ?? null;
    }
    
    /**
     * Token do usuário atual.
     *
     * @return string|null
     */
    public static function utoken(): ?string
    {
        return $_SESSION['cerbero']['utoken'] ?? null;
    }
    
    /**
     * Erros da sessão atual.
     *
     * @return array
     */
    public static function errors(): array
    {
        return $_SESSION['cerbero']['errors'];
    }

    /**
     * O usuário `identity` tem acesso ao sistema `stoken`?
     *
     * @param string $identity
     * @param string $stoken
     * @return boolean
     */
    public static function systemGrantedToUser(string $identity, string $stoken): bool
    {
        if(!self::systemOnline($stoken)) return false;

        $sth = self::$dbh->prepare("SELECT * FROM user_systems WHERE identity = :identity AND stoken = :stoken and status = :status;");
        if($sth === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $sth->bindValue('identity', $identity, PDO::PARAM_STR);
        $sth->bindValue('stoken', $stoken, PDO::PARAM_STR);
        $sth->bindValue('status', UserSystemStatusEnum::Active->value, PDO::PARAM_INT);
        if($sth->execute() === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if($data === false) return false;
        return true;
    }
    
    /**
     * O sistema `stoken` está ativo/up/online?
     *
     * @param string $stoken
     * @return boolean
     */
    public static function systemOnline(string $stoken): bool
    {
        $sth = self::$dbh->prepare("SELECT * FROM systems WHERE stoken = :stoken AND status = :status;");
        if($sth === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $sth->bindValue('stoken', $stoken, PDO::PARAM_STR);
        $sth->bindValue('status', UserSystemStatusEnum::Active->value, PDO::PARAM_INT);
        if($sth->execute() === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if($data === false) return false;
        return true;
    }

    /**
     * O usuário `identity` está autenticado/logado?
     * 
     * Ele tem acesso ao sistema `stoken`?
     * 
     * Ele está vinculado à regra `role`?
     *
     * @param string $stoken
     * @param string $identity
     * @param string $role
     * @return boolean
     */
    public static function authorize(string $stoken, string $identity, string $role): bool
    {
        if(!self::hasUserAuthenticated()) throw new AuthenticationException();

        if(!self::systemGrantedToUser($identity, $stoken)) throw new NotAcceptableException();

        if(!self::hasRole($stoken, $identity, $role)) throw new ForbiddenException();

        return true;
    }

    /**
     * O usuário `identity` está vinculado à `role` do sistema `stoken`?
     *
     * @param string $stoken
     * @param string $identity
     * @param string $role
     * @return boolean
     */
    public static function hasRole(string $stoken, string $identity, string $role): bool
    {
        $role_id = self::getRoleId($stoken, $role);
        $sth = self::$dbh->prepare("SELECT * FROM user_roles WHERE identity = :identity AND role_id = :role_id AND status = :status;");
        if($sth === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $sth->bindValue('identity', $identity, PDO::PARAM_STR);
        $sth->bindValue('role_id', $role_id, PDO::PARAM_INT);
        $sth->bindValue('status', UserRoleStatusEnum::Active->value, PDO::PARAM_INT);
        if($sth->execute() === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if($data !== false) return true;

        return self::hasRoleByGroup($identity, $role_id);
    }

    private static function getRoleId(string $stoken, string $role): int
    {
        $sth = self::$dbh->prepare("SELECT id FROM roles WHERE stoken = :stoken AND role = :role AND status = :status;");
        if($sth === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $sth->bindValue('stoken', $stoken, PDO::PARAM_STR);
        $sth->bindValue('role', $role, PDO::PARAM_STR);
        $sth->bindValue('status', RoleStatusEnum::Active->value, PDO::PARAM_INT);
        if($sth->execute() === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if($data === false) return 0;
        return $data['id'];
    }
    
    private static function hasRoleByGroup(string $identity, int $role_id): bool
    {
        $sth = self::$dbh->prepare("SELECT * FROM user_groups ug INNER JOIN group_roles gr ON ug.group_id = gr.group_id LEFT JOIN groups g ON ug.group_id = g.id WHERE ug.identity = :identity AND gr.role_id = :role_id AND ug.status = :ugstatus AND gr.status = :grstatus AND g.status = :gstatus;");
        if($sth === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $sth->bindValue('identity', $identity, PDO::PARAM_STR);
        $sth->bindValue('role_id', $role_id, PDO::PARAM_INT);
        $sth->bindValue('ugstatus', UserGroupStatusEnum::Active->value, PDO::PARAM_INT);
        $sth->bindValue('grstatus', GroupRoleStatusEnum::Active->value, PDO::PARAM_INT);
        $sth->bindValue('gstatus', GroupStatusEnum::Active->value, PDO::PARAM_INT);
        if($sth->execute() === false) throw new DbException(self::$dbh->errorInfo()[2], self::$dbh->errorInfo()[2]);
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if($data !== false && $data !== []) return true;
        return false;
    }
}