# 📘 Envelopei

**Envelopei** é um sistema de controle financeiro pessoal baseado no método de **envelopes**, desenvolvido com foco em simplicidade, clareza e expansão futura para aplicações mobile.

---

## 🎯 Objetivo do Projeto

- Controlar receitas, despesas e transferências  
- Dividir o dinheiro em **envelopes virtuais**  
- Manter conciliação entre:
  - **onde o dinheiro realmente está** (contas)
  - **como ele é dividido** (envelopes)
- Oferecer histórico completo de lançamentos  
- Facilitar evolução para aplicativo mobile  

---

## 🧠 Conceitos Importantes

### 🔹 Contas
Representam onde o dinheiro está de fato:
- banco
- carteira
- poupança
- investimento

### 🔹 Envelopes
São divisões **fictícias** do dinheiro:
- moradia
- alimentação
- transporte
- lazer
- etc.

O saldo real está nas **contas**.  
Os envelopes são apenas uma forma de organização.

### 🔹 Lançamentos
Todo evento financeiro gera um lançamento:
- receita
- despesa
- transferência
- ajuste

Cada lançamento pode gerar:
- movimentação em conta(s)
- movimentação em envelope(s)

---

## 🧱 Arquitetura

### API First

Todas as regras de negócio são expostas via **API REST (JSON)**.

As views do CodeIgniter consomem a API da mesma forma que um app externo faria.


Vantagens:
- reaproveitamento de lógica
- fácil integração com Cordova
- manutenção mais simples

---

## 🛠 Tecnologias Utilizadas

### Backend
- PHP 8+
- CodeIgniter 4
- MySQL / MariaDB (ou SQL Server, com adaptação)
- Migrations
- Seeders
- Filters (auth)

### Frontend (Web)
- HTML5
- CSS3
- JavaScript
- Bootstrap 5 (local)
- jQuery 3.7.1
- Font Awesome 7.1

### Mobile (planejado)
- Apache Cordova
- Consumo da mesma API

---

## 🗄 Banco de Dados

### Tabelas principais
- `tb_usuarios`
- `tb_contas`
- `tb_envelopes`
- `tb_lancamentos`
- `tb_itens_conta`
- `tb_itens_envelope`
- `tb_rateios_receita`
- `tb_categorias`

### Regras de cálculo
- **Saldo da conta** = SaldoInicial + soma dos itens de conta  
- **Saldo do envelope** = soma dos itens de envelope  

---

## 🔐 Autenticação

- Login via API (`/api/login`)
- Sessão PHP para web
- Filter `authEnvelopei` protege:
  - rotas da API
  - rotas web
- Preparado para evolução futura com token (JWT)

---

## 🌱 Seed Inicial

O projeto possui um seeder com:
- Usuário padrão
- Contas iniciais
- Envelopes básicos

### Usuário seed
Email: lucas@teste.com

Senha: 2212

### Rodar o seed
```bash
php spark db:seed EnvelopeiSeeder
```

🔄 Funcionalidades Implementadas

✅ Login / Logout

✅ Dashboard com conciliação

✅ Cadastro de contas

✅ Cadastro de envelopes

✅ Lançamento de receitas com rateio

✅ Lançamento de despesas

✅ Transferência entre envelopes

✅ Listagem de lançamentos

✅ Filtros por:

período

tipo

conta

envelope

✅ Visualização de detalhes

✅ Exclusão de lançamentos

✅ Feedback visual (toast)

🧑‍💻 Autor

Lucas Alcântara
Gestor de TI · Desenvolvedor Full Stack
Projeto pessoal para estudo e uso próprio.

📜 Licença

Uso pessoal e educacional.
Sinta-se livre para estudar, adaptar e evoluir.