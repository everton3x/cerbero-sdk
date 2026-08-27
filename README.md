# Cerbero SDK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.5.7-8892BF.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Type Coverage](https://img.shields.io/badge/type--coverage-100%25-brightgreen.svg)]()

O **Cerbero SDK** é uma biblioteca PHP robusta e leve projetada para centralizar e simplificar as operações de **autenticação**, **verificação de sessão**, **controle de acesso a múltiplos sistemas** e **autorização granular de permissões** (diretas e baseadas em perfis de usuário / RBAC).

---

## Sumário

- [Recursos](#recursos)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Estrutura do Banco de Dados](#estrutura-do-banco-de-dados)
- [Configuração](#configuração)
- [Guia de Uso](#guia-de-uso)
  - [1. Inicializando o SDK](#1-inicializando-o-sdk)
  - [2. Autenticação de Usuários (`authenticate`)](#2-autenticação-de-usuários-authenticate)
  - [3. Verificação de Autenticação (`authenticated` e `checkSessionToken`)](#3-verificação-de-autenticação-authenticated-e-checksessiontoken)
  - [4. Controle de Acesso a Sistemas (`access`)](#4-controle-de-acesso-a-sistemas-access)
  - [5. Autorização de Permissões (`authorizated`)](#5-autorização-de-permissões-authorizated)
  - [6. Encerramento de Sessão / Logoff (`unauthenticate`)](#6-encerramento-de-sessão--logoff-unauthenticate)
- [Tratamento de Exceções](#tratamento-de-exceções)
- [Enumerações de Status](#enumerações-de-status)
- [Exemplo Prático (Web Demo)](#exemplo-prático-web-demo)
- [Testes e Qualidade de Código](#testes-e-qualidade-de-código)
- [Licença](#licença)

---

## Recursos

- 🔐 **Autenticação Segura**: Suporte nativo à validação de hash de senhas (`password_verify`), compatível com Argon2id, Bcrypt, etc.
- 🛡️ **Proteção contra Brute Force**: Controle opcional de tentativas consecutivas de login (`maxLoginAttempts`).
- 🎟️ **Gestão de Sessão**: Geração e validação de tokens únicos de sessão por usuário.
- 🏢 **Multi-Sistemas**: Controle de acesso isolado por slug de sistema.
- 🔑 **Autorização Flexível (RBAC)**:
  - Permissões atribuídas diretamente ao usuário no sistema.
  - Permissões herdadas através de perfis (*roles*) atribuídos ao usuário.
- 🧱 **Enums Tipados**: Status padronizados para usuários, sistemas, permissões, perfis e relacionamentos.
- 🧪 **Alta Qualidade**: 100% de cobertura de tipos e análise estática avançada com PHPStan.

---

## Requisitos

- **PHP**: `>= 8.5.7`
- **Extensão PDO** ativa com o driver do banco de dados desejado (SQLite, MySQL, PostgreSQL, etc.).

---

## Instalação

Adicione o Cerbero SDK ao seu projeto via [Composer](https://getcomposer.org/):

```bash
composer require cerbero/cerbero-sdk
```

---

## Estrutura do Banco de Dados

O Cerbero SDK utiliza uma estrutura relacional simples e eficiente com o prefixo `crb_`:

| Tabela | Descrição |
| :--- | :--- |
| `crb_users` | Registra os usuários, hash de senha, token de sessão atual, status e tentativas de login. |
| `crb_systems` | Sistemas cadastrados identificados por `slug` e `status`. |
| `crb_permissions` | Permissões disponíveis por sistema (`system_slug`, `slug`). |
| `crb_profiles` | Perfis/papéis disponíveis por sistema (`system_slug`, `slug`). |
| `crb_user_system` | Vínculo de acesso de usuários a sistemas. |
| `crb_user_permission` | Permissões concedidas diretamente a usuários específicos. |
| `crb_user_profile` | Perfis atribuídos a cada usuário por sistema. |
| `crb_profile_permission` | Permissões vinculadas a perfis específicos por sistema. |

> Os scripts SQL de criação do esquema e dados de exemplo estão localizados no diretório [`migrations/`](migrations/).

---

## Configuração

O SDK é instanciado recebendo um array associativo de configurações:

```php
use Cerbero\Sdk\Cerbero;

$config = [
    'pdoDsn' => 'sqlite:/caminho/para/o/banco.db', // DSN de conexão PDO
    'pdoUser' => null,                             // Usuário do banco (opcional)
    'pdoPass' => null,                             // Senha do banco (opcional)
    'pdoOptions' => [                              // Opções do driver PDO (opcional)
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
    'maxLoginAttempts' => 3                        // Limite de tentativas de login antes do bloqueio (opcional)
];

$crb = new Cerbero($config);
```

---

## Guia de Uso

### 1. Inicializando o SDK

```php
require_once __DIR__ . '/vendor/autoload.php';

use Cerbero\Sdk\Cerbero;

$crb = new Cerbero([
    'pdoDsn' => 'sqlite:./cerbero.db',
    'maxLoginAttempts' => 3
]);
```

---

### 2. Autenticação de Usuários (`authenticate`)

Valida o identificador do usuário e a senha em texto plano. Se corretos, gera um novo token de sessão, reseta as tentativas de login e retorna o token gerado.

```php
use Cerbero\Sdk\Exception\UserOrPasswordInvalid;
use Cerbero\Sdk\Exception\LimitLoginAttempts;

try {
    $sessionToken = $crb->authenticate('admin', 'minha_senha_123');
    
    // Armazena na sessão da aplicação web
    $_SESSION['user_id'] = 'admin';
    $_SESSION['session_token'] = $sessionToken;
} catch (UserOrPasswordInvalid $e) {
    echo "Usuário ou senha incorretos.";
} catch (LimitLoginAttempts $e) {
    echo "Limite de tentativas de login excedido. Tente novamente mais tarde.";
}
```

---

### 3. Verificação de Autenticação (`authenticated` e `checkSessionToken`)

- `authenticated(string $userId, string $sessionToken): bool`: Valida se o usuário existe, está ativo e possui o token de sessão correspondente.
- `checkSessionToken(?string $sessionToken): bool`: Consulta se determinado token de sessão existe atribuído a algum usuário.

```php
$userId = $_SESSION['user_id'] ?? '';
$sessionToken = $_SESSION['session_token'] ?? '';

if ($crb->authenticated($userId, $sessionToken)) {
    echo "Sessão válida para o usuário $userId.";
} else {
    echo "Usuário não autenticado ou sessão expirada.";
}
```

---

### 4. Controle de Acesso a Sistemas (`access`)

Verifica se o usuário possui vínculo ativo com o sistema solicitado (`systemSlug`).

```php
use Cerbero\Sdk\Exception\UserNotAuthenticated;

try {
    if ($crb->access($userId, $sessionToken, 'financeiro')) {
        echo "Acesso permitido ao módulo Financeiro.";
    } else {
        echo "Acesso negado ao módulo Financeiro.";
    }
} catch (UserNotAuthenticated $e) {
    // Redirecionar para tela de login
}
```

---

### 5. Autorização de Permissões (`authorizated`)

Verifica se o usuário possui determinada permissão (`permissionSlug`) em um sistema (`systemSlug`). A validação avalia:
1. Permissões concedidas diretamente ao usuário (`crb_user_permission`).
2. Permissões concedidas por meio dos perfis do usuário (`crb_user_profile` + `crb_profile_permission`).

```php
use Cerbero\Sdk\Exception\UserNotAuthorized;

try {
    // Verifica permissão de exclusão no sistema financeiro
    if ($crb->authorizated($userId, $sessionToken, 'financeiro', 'delete')) {
        echo "Usuário autorizado a excluir registros.";
    } else {
        echo "Permissão negada.";
    }
} catch (UserNotAuthorized $e) {
    echo "Usuário não possui acesso ao sistema solicitado.";
}
```

---

### 6. Encerramento de Sessão / Logoff (`unauthenticate`)

Invalida o token de sessão do usuário no banco de dados.

```php
$crb->unauthenticate($userId);

// Limpa a sessão local da aplicação
session_destroy();
```

---

## Tratamento de Exceções

O SDK dispara exceções específicas derivadas de `\RuntimeException`:

| Exceção | Quando ocorre |
| :--- | :--- |
| [`UserNotAuthenticated`](sdk/Exception/UserNotAuthenticated.php) | Disparada ao tentar verificar acesso a sistemas sem uma sessão ativa e autenticada. |
| [`UserNotAuthorized`](sdk/Exception/UserNotAuthorized.php) | Disparada ao tentar verificar permissões sem possuir acesso prévio ao sistema. |
| [`UserOrPasswordInvalid`](sdk/Exception/UserOrPasswordInvalid.php) | Disparada quando o ID de usuário não existe ou a senha informada é incorreta. |
| [`LimitLoginAttempts`](sdk/Exception/LimitLoginAttempts.php) | Disparada quando a quantidade de falhas consecutivas de login atinge o limite configurado em `maxLoginAttempts`. |

---

## Enumerações de Status

Todas as entidades e relacionamentos utilizam enums inteiros (`int`) tipados:

- **`UserStatus`**: `Undefined (0)`, `Active (1)`, `Pending (2)`, `Disabled (3)`
- **`SystemStatus`**: `Undefined (0)`, `Active (1)`, `Disabled (3)`
- **`PermissionStatus`**: `Undefined (0)`, `Active (1)`, `Disabled (3)`
- **`ProfileStatus`**: `Undefined (0)`, `Active (1)`, `Disabled (3)`
- **`RelationStatus`**: `Undefined (0)`, `Active (1)`, `Disabled (3)`

---

## Exemplo Prático (Web Demo)

O diretório [`examples/`](examples/) contém uma aplicação web demonstrativa completa utilizando Fomantic-UI.

Para rodar o exemplo localmente com o servidor embutido do PHP:

```bash
php -S localhost:8000 -t examples/
```

Em seguida, acesse no navegador: `http://localhost:8000`

**Credenciais de teste padrão (senha: `abc123`):**
- Usuário `admin`: Acesso total e permissões completas.
- Usuário `editor`: Permissões via perfil (*create*, *read*, *update*).
- Usuário `guest`: Apenas leitura (*read*).

---

## Testes e Qualidade de Código

Para rodar os testes automatizados e ferramentas de análise estática:

```bash
# Executar a suíte de testes (Pest)
composer test

# Executar a análise estática (PHPStan Nível 6)
composer static

# Verificar cobertura de tipos (100%)
composer type-coverage
```

---

## Licença

Este projeto é distribuído sob a licença **MIT**. Consulte o arquivo `composer.json` para mais informações.

**Autor**: Everton da Rosa (<everton3x@gmail.com>)
