<?php

use Cerbero\Sdk\Cerbero;
use Cerbero\Sdk\Exception\UserOrPasswordInvalid;

session_start();

require_once realpath(__DIR__.'/../vendor/autoload.php');

$crb = new Cerbero(require realpath(__DIR__.'/config.example.php'));

try {
    $session_token = $crb->authenticate($_POST['user-id'], $_POST['password']);
    $_SESSION['crb_token'] = $session_token;
    $_SESSION['crb_user_id'] = $_POST['user-id'];
} catch (UserOrPasswordInvalid $ex) {
    $session_token = null;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Cerbero SDK Logon</title>
        
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
            
            <?php if(is_null($session_token)):?>
            <div class="ui negative message">
                <div class="header">
                  Falha no logon.
                </div>
                <p>Usuário ou senha inválido.</p>
                <p>
                    <a href="login.php">Clique aqui para voltar à página de login.</a>
                </p>
            </div>
            <?php else:?>
            <div class="ui positive message">
                <div class="header">
                  Logon realizado.
                </div>
                <p>
                    <a href="index.php">Clique aqui para voltar à página inicial.</a>
                </p>
            </div>
            <?php endif;?>
            
        </div>
    </body>
</html>
