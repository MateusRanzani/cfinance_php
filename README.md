# Sistema de Financas de Casal (PHP + MySQL + MVC + Tailwind)

## Requisitos
- PHP 8.1+
- Composer
- MySQL 8+

## Configuracao
1. Configure as credenciais em `.env`.
2. Crie banco e tabelas:

```bash
mysql -u root -p < database/schema.sql
```

3. Instale dependencias:

```bash
composer install
```

4. Suba o servidor local:

```bash
composer run serve
```

5. Abra no navegador: `http://127.0.0.1:8000`

## Login inicial
- E-mail: `admin@casal.com`
- Senha: `admin123`

## Estrutura MVC
- `src/Core`: kernel, roteador e engine de views.
- `src/Controllers`: controllers de autenticacao, dashboard e usuarios.
- `src`: servicos de dominio e acesso a dados.
- `views`: templates PHP por modulo com layout compartilhado.

## Tailwind
- O layout principal (`views/layouts/app.php`) carrega Tailwind via CDN.

