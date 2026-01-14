<?php

namespace Cerbero\Core\Entity;

use Cerbero\Core\Enum\SystemStatusEnum;
use Cerbero\Core\Enum\UserStatusEnum;
use Cerbero\Exception\DbException;
use Cerbero\Exception\SystemNotFoundException;
use Cerbero\Exception\UserNotFoundException;
use PDO;

final class System
{
    public string $name {
        set (string $name) => $this->name = $name;
        get => $this->name;
    }

    public ?SystemStatusEnum $status {
        set (?SystemStatusEnum $status) => $this->status = $status;
        get => $this->status;
    }

    public array $users = [] {
        set (array $users) => $this->users = $users;
        get => $this->users;
    }

    public function __construct(
        private readonly PDO $dbh,
        public readonly string $stoken
    )
    {
        $sth = $this->dbh->prepare("SELECT * FROM systems WHERE stoken LIKE :stoken;");
        if($sth === false) throw new DbException($this->dbh->errorInfo()[2], $this->dbh->errorInfo()[2]);
        $sth->bindValue('stoken', $stoken, PDO::PARAM_STR);
        if($sth->execute() === false) throw new DbException($this->dbh->errorInfo()[2], $this->dbh->errorInfo()[2]);
        $data = $sth->fetch(PDO::FETCH_ASSOC);
        if($data === false) throw new SystemNotFoundException($stoken);
        $this->name = $data['name'];
        $this->status = SystemStatusEnum::tryFrom((int) $data['status']);
        $this->users = $this->getUsersForSystem($this->stoken);
    }

    private function getUsersForSystem(string $stoken): array
    {
        $sth = $this->dbh->prepare("SELECT * FROM user_systems JOIN users ON users.identity = user_systems.identity WHERE stoken LIKE :stoken;");
        if($sth === false) throw new DbException($this->dbh->errorInfo()[2], $this->dbh->errorInfo()[2]);
        $sth->bindValue('stoken', $stoken, PDO::PARAM_INT);
        if($sth->execute() === false) throw new DbException($this->dbh->errorInfo()[2], $this->dbh->errorInfo()[2]);
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        if($data === false) return [];

        $users = [];
        foreach($data as $row){
            $users[] = new User($this->dbh, $row['identity']);
        }
        return $users;
    }
}