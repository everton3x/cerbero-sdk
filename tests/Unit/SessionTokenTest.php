<?php

use Cerbero\Sdk\Exception\UserNotAuthorized;

describe('Token de sessão', function(){
    test('Cerbero::checkSessionToken -> true', function () {
        $sessionToken = $this->crb()->authenticate('admin', 'abc123');
        expect($this->crb()->checkSessionToken($sessionToken))->toBeTrue();
        $this->crb()->unauthenticate('admin');
    });
    
    test('Cerbero::checkSessionToken -> false', function () {
        expect($this->crb()->checkSessionToken('fake'))->toBeFalse();
    });
    
    test('Cerbero::checkSessionToken (null token) -> false', function () {
        expect($this->crb()->checkSessionToken(null))->toBeFalse();
    });
    
    
});