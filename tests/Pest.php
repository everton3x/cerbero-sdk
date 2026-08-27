<?php

use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');
pest()->extend(TestCase::class)->in('Unit');

pest()->beforeAll(function(){
    prepareDb();
});

pest()->afterAll(function() {
    if(file_exists(getCacheDb())){
        unlink(getCacheDb());
    }
});