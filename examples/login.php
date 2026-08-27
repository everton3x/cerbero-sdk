<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Cerbero SDK Login</title>
        
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
            <h3 class="ui dividing header">Login</h3>
            
            <form class="ui form" method="post" action="logon.php">
                <div class="field">
                  <label>Usuário</label>
                  <input type="text" name="user-id" placeholder="Use: admin, editor ou guest" autofocus required>
                </div>
                <div class="field">
                  <label>Senha</label>
                  <input type="password" name="password" required>
                  <div>Use: abc123</div>
                </div>
                <button class="ui button" type="submit">Logon</button>
              </form>
        </div>
    </body>
</html>
