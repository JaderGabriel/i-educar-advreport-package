# Advanced Reports (i-Educar) — Executivo/Técnico

Este documento consolida **o que foi implementado** no pacote `serventec/i-educar-advanced-reports-package`, **como funciona**, e como isso se conecta à proposta de **menus temáticos** do `docs/DOC-EXECUTIVO-PACOTE-RELATORIOS-PORTABILIS.md`.

## 1) O que o pacote resolve (escopo)

- Emissão de **relatórios** em **PDF** e/ou **Excel**, sem Java/Jasper.
- Organização em **menus temáticos** (Escola → Relatórios/Documentos).
- Implementação de relatórios **indicadores** (gestão) com filtros base (ano, instituição, escola, curso).
- Emissão de **modelos de documentos** (ex.: diploma/certificado/declaração).
- **Validação pública** de documentos (sem login) via **código** + **QR Code**.

## 2) Padrões técnicos adotados

- **PDF**: `dompdf/dompdf`, usando views Blade e serviços reutilizáveis:
  - `PdfRenderService`
  - Layout padrão: `resources/views/pdf/layout.blade.php` (retrato) e `pdf/layout-landscape.blade.php` (paisagem)
  - Cabeçalho oficial: `pdf/header.blade.php`
  - Rodapé com paginação: `pdf/footer.blade.php` (desligável por documento)
- **Gráficos em PDF**: `ChartImageService` (PNG data-uri) quando `with_charts=1`.
- **Excel**: `maatwebsite/excel` (múltiplas abas quando faz sentido).
- **QR Code**: `QrCodeService` (`chillerlan/php-qrcode`), gerando PNG data-uri para uso em PDF.

## 3) Menus temáticos (doc executivo) x status de implementação

Fonte: `docs/DOC-EXECUTIVO-PACOTE-RELATORIOS-PORTABILIS.md` seção **8.4**.

### 3.1 Prioridade 1 (núcleo)

- **Documentos do aluno (Oficiais)** (8.4.1)
  - **Faltando**: Boletim, Histórico, Atestado/Declarações “oficiais” (matrícula/frequência), Guia/Transferência, Escolaridade/nada consta.
- **Avaliação e frequência (Pedagógico)** (8.4.2)
  - **Faltando**: mapas, espelho de diário, pendências, fichas individuais.
- **Atas e registros formais (Arquivo)** (8.4.3)
  - **Faltando**: atas (resultados finais, conselho, entrega), livro de chamadas (opcional).
- **Movimentações e gestão de matrículas** (8.4.4)
  - **Implementado (parcial)**: Movimentações (Geral) com PDF/Excel.
  - **Faltando**: Movimento mensal; alunos por situação; vagas/turmas.

### 3.2 Indicadores (Socioeconômicos e Inclusão) (8.4.8)

Implementados (com PDF/Excel e opção de gráficos):

- **Socioeconômicos**
- **Inclusão (NEE)**
- **Distorção idade/série**
- **Vulnerabilidade / Assistência social**

### 3.3 Prioridade 2 (administrativo/gestão)

- **Gestão escolar (Administrativo)** (8.4.6)
  - **Faltando**: listas/etiquetas; relatórios gerenciais adicionais.

### 3.4 Prioridade 3 (depende de políticas locais/integrações)

- **Transporte e benefícios** (8.4.5)
  - **Faltando**: carteirinhas, listagens por rota/benefício (depende de dados/integrações).

## 4) Relatórios/documentos implementados (rotas)

### 4.1 Indicadores

- `/relatorios-avancados/socioeconomicos` (+ PDF/Excel)
- `/relatorios-avancados/inclusao` (+ PDF/Excel)
- `/relatorios-avancados/distorcao-idade-serie` (+ PDF/Excel)
- `/relatorios-avancados/vulnerabilidade` (+ PDF/Excel)

### 4.2 Movimentações

- `/relatorios-avancados/movimentacoes` (+ PDF/Excel)

### 4.3 Modelos de documentos (com validação)

- `/relatorios-avancados/diplomas` → gera PDF com:
  - **Diploma** (cabeçalho oficial, **sem rodapé**)
  - **Certificado** (cabeçalho + **rodapé com dados + QR Code**)
  - **Declaração** (cabeçalho + **rodapé com dados + QR Code**)

Validação pública:

- `/documentos/validar/{codigo}`

## 5) Auditoria/validação de documentos (sem login)

- Tabela: `advanced_reports_documents`
  - `code`, `type`, `issued_at`, `payload`
- Ao emitir PDF (certificado/declaração), o pacote:
  - gera `code`
  - persiste o registro com `payload`
  - imprime no rodapé: **código**, **URL**, **QR Code**

