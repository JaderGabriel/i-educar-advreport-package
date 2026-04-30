# Advanced Reports (i-Educar) — Executivo/Técnico

Este documento consolida **o que foi implementado** no pacote `buriti/i-educar-advanced-reports-package`, **como funciona**, e como isso se conecta à proposta de **menus temáticos** do `docs/DOC-EXECUTIVO-PACOTE-RELATORIOS-PORTABILIS.md`.

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
- **Padrão visual (título)**: títulos (`<h1>`) centralizados via CSS nos layouts base (inclui prévias).
- **Gráficos em PDF**: `ChartImageService` (PNG data-uri) quando `with_charts=1`.
- **Excel**: `maatwebsite/excel` (múltiplas abas quando faz sentido).
  - **Hardening**: sanitização de células contra injeção de fórmula (OWASP CSV/Excel Injection) via `SpreadsheetFormulaInjectionGuard` aplicado em `SimpleArraySheet` e exports específicos.
- **QR Code**: `QrCodeService` (`chillerlan/php-qrcode`), gerando PNG data-uri para uso em PDF.

**Comportamento padrão**: PDFs são emitidos como **prévia no navegador** (header `Content-Disposition: inline`), permitindo imprimir/baixar sem download automático.

## 3) Menus temáticos (doc executivo) x status de implementação

Fonte: `docs/DOC-EXECUTIVO-PACOTE-RELATORIOS-PORTABILIS.md` seção **8.4**.

### 3.1 Prioridade 1 (núcleo)

- **Documentos do aluno (Oficiais)** (8.4.1)
  - **Implementado**: Boletim, Histórico (múltiplos modelos), Declarações oficiais (matrícula/frequência) e Guia/Transferência.
  - **Faltando**: Escolaridade / vida escolar / “nada consta” (definir escopo e LGPD).
- **Avaliação e frequência (Pedagógico)** (8.4.2)
  - **Implementado (orientação)**: placeholders de menu/tela para mapas, espelho e pendências (para fluxo claro ao usuário leigo).
  - **Faltando**: implementação completa (regras por rede: etapas, diários, alocações docentes e lançamentos).
- **Atas e registros formais (Arquivo)** (8.4.3)
  - **Implementado (inicial)**: Atas (resultado final / lista de assinaturas), com QR/validação e opção de detalhes.
  - **Implementado (orientação)**: placeholders para “Ata de conselho” e “Ata de entrega de resultados”.
  - **Faltando**: implementação completa dessas variações (layout e regras por rede).
- **Movimentações e gestão de matrículas** (8.4.4)
  - **Implementado (parcial)**: Movimentações (Geral) com PDF/Excel.
  - **Implementado (inicial)**: Vagas por turma (capacidade/ocupação/vagas) com PDF/Excel.
  - **Implementado**: Alunos por situação (resumo + listagem, com PDF/Excel).
  - **Faltando**: Movimento mensal; refinamentos por regra/situação.

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
- `/relatorios-avancados/vagas-turmas` (+ PDF/Excel)
- `/relatorios-avancados/alunos-por-situacao` (+ PDF/Excel)

### 4.3 Modelos de documentos (com validação)

- `/relatorios-avancados/diplomas` → gera PDF com:
  - **Diploma** (cabeçalho oficial + **rodapé com dados + QR Code**)
  - **Certificado** (cabeçalho + **rodapé com dados + QR Code**)
  - **Declaração** (cabeçalho + **rodapé com dados + QR Code**)

Notas técnicas:

- Layouts foram ajustados para evitar problemas comuns do Dompdf:
  - evitar `height` fixo em mm com `overflow: hidden`;
  - evitar rodapé “fixo/absoluto” que pode forçar páginas em branco por overflow.

Validação pública:

- `/documentos/validar/{codigo}`

### 4.4 Documentos oficiais do aluno (com validação)

- `/relatorios-avancados/documentos` (UI)
- `/relatorios-avancados/documentos/pdf` (PDF: matrícula, frequência, guia/transferência)

### 4.5 Boletim e Histórico (com validação)

- `/relatorios-avancados/boletim` (+ PDF)
- `/relatorios-avancados/historico` (+ PDF, múltiplos modelos + prévia modal)
  - **Observação (política atual)**: o histórico é **impresso a partir do histórico nativo consolidado** (o pacote não “cria histórico”; apenas imprime e adiciona validação/QR).

### 4.6 Atas (com validação)

- `/relatorios-avancados/atas` (+ PDF)
  - Ata de resultados finais (opção `with_details=1`: notas por componente/etapa + frequência quando disponível)
  - Lista de assinaturas (responsáveis)

## 5) Auditoria/validação de documentos (sem login)

- Tabela: `advanced_reports_documents`
  - `code`, `type`, `issued_at`, `payload`
- Ao emitir PDF (certificado/declaração), o pacote:
  - gera `code`
  - persiste o registro com `payload`
  - imprime no rodapé: **código**, **URL**, **QR Code**
