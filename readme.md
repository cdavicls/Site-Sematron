---
type: submodule
status: active
updated: "2026-07-16"
tags: [submodule, sematron, web-dev, laravel, tailwind]
---

## 🚀 Como rodar o projeto localmente

Siga os passos abaixo para clonar e configurar o projeto na sua máquina.

### Pré-requisitos

Certifique-se de ter as seguintes ferramentas instaladas:
- [PHP](https://www.php.net/) (versão compatível com o projeto)
- [Composer](https://getcomposer.org/)
- [Node.js & NPM](https://nodejs.org/)
- [XAMPP](https://www.apachefriends.org/) (ou outro gerenciador de banco de dados)

---

### 🔧 Instalação Passo a Passo

1. **Clone o repositório**
   ```bash
   git clone https://github.com/ynalbizz/Site-Sematron
   cd Site-Sematron
   ```

2. **Instale as dependências do PHP**
Caso o comando abaixo mostre algum erro ou aviso, tente atualizar o PHP seguindo o tutorial presente no arquivo [phpupdate.md](https://github.com/ynalbizz/Site-Sematron/blob/main/phpupdate.md) e, após isso, rode `composer update`.
```bash
composer install
```


3. **Instale as dependências do Frontend**
```bash
npm install
```


4. **Configure o arquivo de ambiente**
Faça uma cópia do arquivo `.env.example` e renomeie para `.env`:
*No Windows:*
```bash
copy .env.example .env
```


*No Linux/Mac:*
```bash
cp .env.example .env
```


5. **Gere a chave da aplicação**
```bash
php artisan key:generate
```


6. **Configure o Banco de Dados**
Crie um banco de dados vazio no seu gerenciador (phpMyAdmin, Workbench, DBeaver). Em seguida, abra o arquivo `.env` (no VS Code) e ajuste as credenciais:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_seu_banco
DB_USERNAME=root
DB_PASSWORD=
```


7. **Configuração do XAMPP e Migrations**
⚠️ **Atenção (Usuários de XAMPP):** Antes de rodar as migrations, é necessário aumentar o limite de pacotes do MySQL para evitar erros.
- 7.1. Abra o painel do XAMPP.
- 7.2. Clique no botão **Config** na linha do MySQL e selecione **my.ini**.
- 7.3. Pressione `Ctrl + F` e procure por: `max_allowed_packet`.
- 7.4. Altere o valor para: `max_allowed_packet=256M`.
- 7.5. Salve o arquivo e **reinicie o módulo MySQL** no painel do XAMPP (Stop > Start).


Agora, rode o comando para criar as tabelas e inserir os dados de teste:
```bash
php artisan migrate:fresh --seed
```


8. **Inicie o Servidor**
inicie o servidor do Laravel usnado o:
```bash
php artisan serve
```
ou
```bash
composer run dev
```



🎉 **Pronto!** O projeto estará rodando em: `http://localhost:8000`
