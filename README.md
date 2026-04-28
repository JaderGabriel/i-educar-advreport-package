# i-Educar Advanced Reports Package (sem Java)

Pacote de **Relatórios e Documentos** para o i-Educar, com emissão em **PDF** (Laravel) e opção de **Excel** (quando fizer sentido), alinhado ao **DOC Executivo** do projeto (menus temáticos e priorização educacional).

## Objetivos

- Substituir/evitar dependências de relatórios Jasper/Java no consumo diário.
- Organizar relatórios e documentos em **menus temáticos** (Secretaria, Pedagógico, Arquivo, etc.).
- Permitir:
  - **PDF** com tabelas e (quando aplicável) **gráficos no próprio PDF**;
  - **Excel** para dados tabulares de análise.
  - **Validação pública de documentos** (sem login) via **QR Code** e código de autenticação.

## Instalação (plug-and-play)

1. Garanta o pacote em `packages/serventec/i-educar-advanced-reports-package`.
2. Ative via plug-and-play:

```bash
composer plug-and-play
php artisan package:discover --ansi
```

3. Rode migrations:

```bash
php artisan migrate --force
```

> Este pacote contém migrations que **removem menus de relatórios do pacote Portabilis (Jasper)** antes de criar os menus temáticos (ver “Compatibilidade e migração”).

## Validação pública (QR Code)

Documentos emitidos pelo módulo de **modelos** (ex.: certificados/declarações) incluem:

- **código de validação**
- **URL de validação pública** (sem login)
- **QR Code** apontando para a URL

Rota de validação:

- `/documentos/validar/{codigo}`

## Relatórios e documentos disponíveis

### 1) Relatório Socioeconômico (Indicadores)

- **Rota (UI)**: `/relatorios-avancados/socioeconomicos`
- **PDF**: `/relatorios-avancados/socioeconomicos/pdf`
  - filtro `with_charts=1` inclui **gráficos** no PDF.
- **Excel**: `/relatorios-avancados/socioeconomicos/excel`
- **Objetivo**: visão agregada de distribuição por raça/cor, sexo, benefícios e concentração por escola, no recorte filtrado.

### 1.1) Indicadores de Inclusão (NEE)

- **Rota (UI)**: `/relatorios-avancados/inclusao`
- **PDF**: `/relatorios-avancados/inclusao/pdf`
  - filtro `with_charts=1` inclui **gráficos** no PDF.
- **Excel**: `/relatorios-avancados/inclusao/excel`
- **Objetivo**: apoiar gestão e planejamento com base em dados disponíveis no i-Educar:
  - alunos com **deficiência** (cadastro físico),
  - alunos com **NIS** informado,
  - alunos com **benefícios** associados,
  - distribuição por **tipo de deficiência** e por **escola** (top).

### 1.2) Distorção idade/série (Indicadores)

- **Rota (UI)**: `/relatorios-avancados/distorcao-idade-serie`
- **PDF**: `/relatorios-avancados/distorcao-idade-serie/pdf`
  - filtro `with_charts=1` inclui **gráfico** (% distorção por série) no PDF.
- **Excel**: `/relatorios-avancados/distorcao-idade-serie/excel`
- **Objetivo**: indicador de distorção idade/série por série, com base em:
  - idade aproximada \(ano - ano_nascimento\), faixa 5–17;
  - idade ideal configurada em `pmieducar.serie.idade_ideal`.

### 1.3) Vulnerabilidade / Assistência social (Indicadores)

- **Rota (UI)**: `/relatorios-avancados/vulnerabilidade`
- **PDF**: `/relatorios-avancados/vulnerabilidade/pdf`
  - filtro `with_charts=1` inclui **gráficos** no PDF.
- **Excel**: `/relatorios-avancados/vulnerabilidade/excel`
- **Objetivo**: consolidar, em um recorte filtrado, alunos com marcadores de vulnerabilidade com base em dados existentes:
  - NIS preenchido;
  - benefícios associados;
  - deficiência cadastrada;
  - transporte rural;
  - renda mensal quando informada (qualidade varia por rede).

### 2) Relatório de Movimentações (Geral)

- **Rota (UI)**: `/relatorios-avancados/movimentacoes`
- **PDF**: `/relatorios-avancados/movimentacoes/pdf`
- **Excel**: `/relatorios-avancados/movimentacoes/excel`
- **Objetivo**: consolidar movimentações de matrícula em janela de tempo (admissões, transferências, abandono, remanejamento, reclassificação, óbito), com agrupamento por escola.

### 3) Diplomas/Certificados (modelos)

- **Rota (UI)**: `/relatorios-avancados/diplomas`
- **PDF**: `/relatorios-avancados/diplomas/pdf`
- **Objetivo**: emitir modelos “print-friendly” sem depender de Jasper.
- **Documentos**:
  - Diploma (**sem rodapé**)
  - Certificado (**com rodapé + QR Code**)
  - Declaração (**com rodapé + QR Code**)

## Compatibilidade e migração (remoção de Jasper/Portabilis)

Este pacote foi projetado para coexistir com o i-Educar, mas ao ser instalado:

- remove **submenus** clássicos de relatórios Jasper (Portabilis) na tabela `public.menus` (sem remover os nós padrão `Escola → Relatórios/Documentos`);
- cria uma árvore de **menus temáticos** que aponta para rotas deste pacote.

## Desenvolvimento

- **Padrão de views**: Blade, com `resources/views/partials/filters.blade.php` como base de filtros.
- **PDF**: `dompdf/dompdf` (HTML → PDF) via `PdfRenderService`.
- **Excel**: `maatwebsite/excel`.
- **QR Code**: `chillerlan/php-qrcode` via `QrCodeService`.

## Documentação (executiva/técnica) e roadmap

- **Status do que já foi feito e pendências**: `docs/RELATORIOS_AVANCADOS_STATUS.md`
- **Resumo executivo/técnico do pacote**: `docs/EXECUTIVO_TECNICO.md`
- **Backlog e melhorias futuras**: `docs/MELHORIAS_FUTURAS.md`

