# Sistema de Financas (PHP + MySQL + MVC + Tailwind)

## Requisitos
- PHP 8.1+
- Composer
- MySQL 8+

## Configuracao
1. Configure as credenciais em `.env`.
2. Crie o banco e as tabelas:

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

## Modelo de dados
- Sem tabela de mes.
- O mes e identificado por `MONTH(data_referencia)` e `YEAR(data_referencia)`.
- Tabelas principais:
  - `rendas`
  - `despesas`
  - `rendas_fixas`
  - `despesas_fixas`
  - `tipos_movimentacao`

## Funcionalidades
- Filtro mensal por meses disponiveis.
- Totais planejados e reais para rendas e despesas.
- Diferenca por linha (`planejado - real`) e diferenca total do mes.
- Saldo final: `rendas_reais - despesas_reais`.
- Copiar estrutura do mes anterior quando o mes atual estiver vazio.
- Aplicar rendas/despesas fixas no mes para evitar recriacao manual.
- Cadastro de tipos de movimentacao (ex.: salario, venda, cartao, aluguel) na aba `Tipos`.
- Vinculacao de tipo obrigatoria em rendas e despesas (normais e fixas).

## Atualizando banco ja existente
Se voce ja tinha o banco criado antes dessa funcionalidade, rode novamente:

```bash
mysql -u root -p < database/schema.sql
```
