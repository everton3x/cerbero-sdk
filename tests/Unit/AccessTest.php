<?php

use Cerbero\Sdk\Exception\UserNotAuthenticated;

describe('Acesso aos sistemas', function(){
    test('Cerbero::access -> true', function () {
        $sessionToken = $this->crb()->authenticate('admin', 'abc123');
        expect($this->crb()->access('admin', $sessionToken, 'example'))->toBeTrue();
        $this->crb()->unauthenticate('admin');
    });
    
    test('Cerbero::access -> false', function () {
        $sessionToken = $this->crb()->authenticate('admin', 'abc123');
        expect($this->crb()->access('admin', $sessionToken, 'fake'))->toBeFalse();
        $this->crb()->unauthenticate('admin');
    });
    
    // Precisa vir no final, caso contrário, os testes subsequentes disparam PDOException SQLSTATE[HY000]: General error: 5 database is locked.
    test('Cerbero::access -> UserNotAuthenticated', function () {
        $this->crb()->access('admin', 'token fake', 'example');
    })->throws(UserNotAuthenticated::class);

});