<?php

use Cerbero\Sdk\Exception\UserOrPasswordInvalid;

describe('Autenticação', function(){
    test('Cerbero::authenticated -> true', function () {
        $sessionToken = $this->crb()->authenticate('admin', 'abc123');
        expect($this->crb()->authenticated('admin', $sessionToken))->toBeTrue();
        $this->crb()->unauthenticate('admin');
    });

    test('Cerbero::authenticated -> false', function () {
        expect($this->crb()->authenticated('admin', ''))->toBeFalse();
    });

    test('Cerbero::authenticate -> success', function () {
        expect($this->crb()->authenticate('admin', 'abc123'))->toBeString();
        $this->crb()->unauthenticate('admin');
    });
    
    test('Cerbero::unauthenticate', function () {
        $sessionToken = $this->crb()->authenticate('admin', 'abc123');
        expect($this->crb()->authenticated('admin', $sessionToken))->toBeTrue();
        $this->crb()->unauthenticate('admin');
        expect($this->crb()->authenticated('admin', $sessionToken))->toBeFalse();
    });

    // Precisa vir no final, caso contrário, os testes subsequentes disparam PDOException SQLSTATE[HY000]: General error: 5 database is locked.
    test('Cerbero::authenticate -> UserOrPasswordInvalid', function () {
        $this->crb()->authenticate('admin', 'wrong password');
    })->throws(UserOrPasswordInvalid::class);

});