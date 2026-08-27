<?php

session_start();

require_once realpath(__DIR__.'/../vendor/autoload.php');

$crb = new Cerbero\Sdk\Cerbero(require realpath(__DIR__.'/config.example.php'));
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Cerbero SDK basic example</title>
        
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
            
            <div class="ui violet segment">
                <h2 class="ui dividing header">
                    <i class="lock icon"></i>
                    <div class="content">
                      Autenticação
                      <div class="sub header">Controle de autenticação de usuários</div>
                    </div>
                </h2>
                
                <div class="ui cards">
                    <?php if(!$crb->checkSessionToken($_SESSION['crb_token'] ?? null)): ?>
                    <div class="card">
                        <div class="content">
                          <div class="header">Não autenticado</div>
                          <div class="description">
                            Não há usuário autenticado.
                          </div>
                        </div>
                        <a class="ui button" href="login.php">
                          <i class="sign in alternate icon"></i>
                          Logon
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="card">
                        <div class="content">
                          <div class="header"><?=$_SESSION['crb_user_id'];?></div>
                          <div class="description">
                            <p>Usuário autenticado com o token <?=$_SESSION['crb_token'];?>.</p>
                            <p>Usuário acessa o sistema <em>example</em>: <?=($crb->access($_SESSION['crb_user_id'], $_SESSION['crb_token'], 'example'))? '<i class="green check circle icon"></i>' : '<i class="red circle icon"></i>';?></p>
                            <p>Usuário acessa o sistema <em>test</em>: <?=($crb->access($_SESSION['crb_user_id'], $_SESSION['crb_token'], 'test'))? '<i class="green check circle icon"></i>' : '<i class="red circle icon"></i>';?></p>
                          </div>
                        </div>
                        <a class="ui button" href="logoff.php">
                          <i class="sign out alternate icon"></i>
                          Logoff
                        </a>
                    </div>
                    
                    <div class="ui card">
                        <div class="content">
                          <div class="header">Permissões:</div>
                        </div>
                        <div class="content">
                            <div class="ui list">
                                <div class="item">
                                    <i class="add icon"></i>
                                    <div class="content">Create: <?=($crb->authorizated($_SESSION['crb_user_id'], $_SESSION['crb_token'], 'example', 'create'))? '<i class="green check circle icon"></i>' : '<i class="red circle icon"></i>';?></div>
                                </div>
                                <div class="item">
                                    <i class="list icon"></i>
                                    <div class="content">Read: <?=($crb->authorizated($_SESSION['crb_user_id'], $_SESSION['crb_token'], 'example', 'read'))? '<i class="green check circle icon"></i>' : '<i class="red circle icon"></i>';?></div>
                                </div>
                                <div class="item">
                                    <i class="edit icon"></i>
                                    <div class="content">Update: <?=($crb->authorizated($_SESSION['crb_user_id'], $_SESSION['crb_token'], 'example', 'update'))? '<i class="green check circle icon"></i>' : '<i class="red circle icon"></i>';?></div>
                                </div>
                                <div class="item">
                                    <i class="trash icon"></i>
                                    <div class="content">Delete: <?=($crb->authorizated($_SESSION['crb_user_id'], $_SESSION['crb_token'], 'example', 'delete'))? '<i class="green check circle icon"></i>' : '<i class="red circle icon"></i>';?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif;?>
                </div>
            </div>
            
        </div>
    </body>
</html>
