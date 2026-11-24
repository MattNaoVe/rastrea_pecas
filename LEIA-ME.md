# rastrea_pecas — Laravel + SQLite + front HTML/CSS/JS

- Front: `public/index.html`, `public/style.css`, `public/script.js`
- API (`routes/api.php`):
  - `GET  /api/pecas/{id}`  consulta a peça e o histórico
  - `PUT  /api/pecas/{id}`  muda o local (corpo: `{"local": "..."}`)
  - `POST /api/pecas`       cadastra uma peça
  - `GET  /api/locais`      lista dos locais (preenche as listas da tela)
- Banco: SQLite, tabelas `pecas`, `locais` e `movimentacoes`
- Locais: Biblioteca, Auditório, Centro de Usinagem (para incluir outro, adicione em `database/seeders/PecasSeeder.php`)

## Instalação

```bash
# 1) projeto Laravel base
composer create-project laravel/laravel rastrea_pecas
cd rastrea_pecas

# 2) habilitar as rotas de API (cria routes/api.php). Se perguntar sobre migrations, pode responder "no".
php artisan install:api

# 3) copie as pastas deste ZIP (app, database, public, routes) para dentro do projeto,
#    aceitando substituir os arquivos existentes (routes/api.php, routes/web.php, DatabaseSeeder.php)

# 4) no arquivo .env confira:
#      DB_CONNECTION=sqlite
#      APP_TIMEZONE=America/Sao_Paulo     (adicione esta linha, senão os horários saem em UTC)

# 5) criar tabelas e dados de exemplo (apaga o que já existir no banco)
php artisan migrate:fresh --seed

# 6) rodar
php artisan serve
```

Abra http://127.0.0.1:8000 .

## Como usar
- **Consultar:** escaneie o QR (texto = código da peça, ex.: `B04`; `peca:B04` e `PEC:B04` também funcionam).
- **Mover:** escolha o novo setor na lista e clique em "Registrar Movimentação".
- **Cadastrar:** formulário "Cadastrar Peça" no final da página. Depois de cadastrar, a peça aparece na tabela.
- **PDF:** "Baixar PDF" gera o relatório na hora, com os dados e o histórico da peça que está na tabela.
- Peças de exemplo: B04, 001, 002, 003, 004.

## Observações
- Os códigos das peças são gravados em MAIÚSCULAS (o front converte o que lê do QR).
- O PDF é gerado no navegador (jsPDF) e a leitura do QR usa html5-qrcode; as duas bibliotecas vêm de CDN, então precisam de internet.
- A câmera só funciona em `localhost` ou HTTPS.
