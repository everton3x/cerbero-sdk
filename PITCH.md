# PITCH: Cerbero - Sistema de Autenticação e Autorização para Sistemas Web

## Problema
Necessidade de gestão centralizada de usuários com permissões flexíveis para fornecer autenticação e autorização multi-sistemas.

## Solução
Desenvolvimento de um sistema de autenticação e autorização para centralizar a gestão de usuários e permissões capaz de fornecer controle de autenticação e autorização para múltiplos sistemas.

## Conceitos e definições
Usuário
:   Entidade que representa uma pessoa autenticada no sistema.

Permissão
:   Direito específico concedido a um usuário para executar determinada ação.

Perfil
:   Conjunto de permissões agrupadas para facilitar a atribuição a usuários.

Sistema
:   Aplicação cliente que utiliza o Cerbero para autenticação e autorização.

Template
:   Sistema modelo utilizado para replicar perfis e permissões em outros sistemas.

## Características e requisitos
- Cadastro único de usuários com campos de login. Usuário caracterizado apenas por UID numérico, nome e senha. Demais campos que podem ser usados como identificador (CPF, e-mail, matrícula, telefone etc.) compõem um cadastro auxiliar. Demais informações, como endereço, cargo, departamento, foto etc. devem ser incluídas em sistema externo e vinculadas através de foreign key;
- Acesso à autenticação e autorização tanto por meio de API, quanto por meio de classes PHP;
- Além do cadastro de usuários (cadastro principal e auxiliar), também conterá cadastros de sistemas (clientes do Cerbero), de permissões e de perfis de usuários;
- Os cadastros de permis e de permissões serão individuais para cada sistema;
- Os sistemas cadastrados serão do tipo `template` ou `production`. Sistemas `template` não poderão ter usuários associados, pois terão a função de servir de modelos de conjuntos de perfis e permissões;
- Possibilidade de copiar permissões e perfis de um sistema para outro;
- O Cerbero terá dois tipos de configurações: configurações ~de instalação, que são definidas na sua instalação e dificilmente serão modificadas após a instalação, que serão definidas em arquivos de configuração; e configurações de produção, que podem ser modificadas sem grande impacto ou riscos após a instalação e estas terão uma interface para alteração no módulo de administração;
- Cada usuário deverá ter atribuído um ou mais sistemas e apenas os usuários atribuídos a determinado sistema podem ter acesso a ele;
- Em cada sistema, para cada usuários serão atribuídas permissões diretamente ou indiretamente por meio de perfis;
- O sistema poderá enviar ao Cerbero (por API ou usando internamente as classes PHP) 3 tipos básicos de verificação: se existe usuário logado e qual usuário é; se o usuários X (logado) tem acesso ao sistema Y (responde true|false); e se o usuário X (logado) tem as permições K, W, Z para o sistema Y (true|false para cada permissão);
- Cerbero fornecerá uma interface gráfica de administração de usuários, sistemas, permissões e perfis, configurações;
- Possibilidade de utilizar uma tela de login personalizada;
- As configurações de padrão de senha, expiração de senhas, número de tentativas erradas, métodos de logins etc. serão definidas nas configurações de produção;
- Suporte a plugins para usar sistemas de autenticação de terceiros (Google, Github, Microsoft, Facebook, Gov.br etc.) e outros métodos de autenticação (certificado digital, SO pass key etc.);
- Níveis configuráveis de log, podendo abranger apenas a autenticação ou autenticação + autorizações;
- O controle efetivo do acessos fica por conta da programação dos sistemas clientes;
