<?php

use Cerbero\Sdk\Exception\UserNotAuthorized;

describe('Autorização', function(){
    test('Cerbero::authorizated -> true', function () {
        $sessionToken = $this->crb()->authenticate('admin', 'abc123');
        expect($this->crb()->authorizated('admin', $sessionToken, 'example', 'create'))->toBeTrue();
        $this->crb()->unauthenticate('admin');
    });
    
    test('Cerbero::access -> false', function () {
        $sessionToken = $this->crb()->authenticate('admin', 'abc123');
        expect($this->crb()->authorizated('admin', $sessionToken, 'example', 'fake'))->toBeFalse();
        $this->crb()->unauthenticate('admin');
    });

    test('Cerbero::authorizated (profile) -> true', function () {
        $sessionToken = $this->crb()->authenticate('editor', 'abc123');
        expect($this->crb()->authorizated('editor', $sessionToken, 'example', 'create'))->toBeTrue();
        $this->crb()->unauthenticate('editor');
    });
    
    test('Cerbero::authorizated (profile) -> false', function () {
        $sessionToken = $this->crb()->authenticate('editor', 'abc123');
        expect($this->crb()->authorizated('editor', $sessionToken, 'example', 'delete'))->toBeFalse();
        $this->crb()->unauthenticate('editor');
    });
    
    // Precisa vir no final, caso contrário, os testes subsequentes disparam PDOException SQLSTATE[HY000]: General error: 5 database is locked.
    test('Cerbero::access -> UserNotAuthorized', function () {
        $sessionToken = $this->crb()->authenticate('admin', 'abc123');
        $this->crb()->authorizated('admin', $sessionToken, 'fake system', 'create');
    })->throws(UserNotAuthorized::class);

});