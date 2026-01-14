<?php

namespace Cerbero\Core\Entity;

use Cerbero\Core\Enum\UserStatusEnum;
use Cerbero\Exception\DbException;
use Cerbero\Exception\UserNotFoundException;
use PDO;

final class User
{
    public string $name {
        set (string $name) => $this->name = $name;
        get => $this->name;
    }

    public string $passwordHash {
        set (string $hash) => $this->passwordHash = $hash;
        get => $this->passwordHash;
    }
    
    public ?string $utoken {
        set (?string $utoken) => $this->utoken = $utoken;
        get => $this->utoken;
    }

    public ?UserStatusEnum $status {
        set (?UserStatusEnum $status) => $this->status = $status;
        get => $this->status;
    }

    public array $systems = [] {
        set (array $systems) => $this->systems;
        get => $this->systems;
    }

    public function __construct(
        private readonly PDO $dbh,
        public readonly string $identity
    )
    {
        $sth = $this->dbh->prepare("SELECT * FROM users WHERE identity LIKE :identity;");
        if($sth === false) throw new DbException($this->dbh->errorInfo()[2], $this->dbh->errorInfo()[2]);
        $sth->bindValue('identity', $identity, PDO::PARAM_STR);
        if($sth->execute() === false) throw new DbException($this->dbh->errorInfo()[2], $this->dbh->errorInfo()[2]);
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if($data === false) throw new UserNotFoundException($identity);
        $this->name = $data['name'];
        $this->passwordHash = $data['password_hash'];
        $this->status = UserStatusEnum::tryFrom((int) $data['status']);
        $this->utoken = $data['utoken'];
        $this->systems = $this->getSystemsForUser($identity);
    }

    private function getSystemsForUser(string $identity): array
    {
        $sth = $this->dbh->prepare("SELECT * FROM user_systems WHERE identity = :identity;");
        if($sth === false) throw new DbException($this->dbh->errorInfo()[2], $this->dbh->errorInfo()[2]);
        $sth->bindValue('identity', $identity, PDO::PARAM_STR);
        if($sth->execute() === false) throw new DbException($this->dbh->errorInfo()[2], $this->dbh->errorInfo()[2]);
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        if($data === false) return [];
        
        $systems = [];
        foreach($data as $row){
            $systems[] = new System($this->dbh, $row['stoken']);
        }
        return $systems;
    }
}