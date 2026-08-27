<?php

session_start();

require_once realpath(__DIR__.'/../vendor/autoload.php');

$crb = new Cerbero\Sdk\Cerbero(require realpath(__DIR__.'/config.example.php'));

$crb->unauthenticate($_SESSION['crb_user_id']);

session_destroy();

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Cerbero SDK Logoff</title>
        
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.4/dist/semantic.min.css">
        <script src="https://cdn.jsdelivr.net/npm/fomantic-ui@2.9.4/dist/semantic.min.js"></script>
    </head>
    <body>
        <div class="ui container">
            
            <div class="ui secondary  menu">
                <a class="header item" href="index.php">
                  Cerbero SDK
                </a>
            </div>
            
            
            <div class="ui positive message">
                <div class="header">
                  Logoff realizado.
                </div>
                <p>
                    <a href="index.php">Clique aqui para voltar à página inicial.</a>
                </p>
            </div>
            
            
        </div>
    </body>
</html>
