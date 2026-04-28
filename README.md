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
1. Ative via plug-and-play:

```bash
composer plug-and-play
php artisan package:discover --ansi
```

1. Rode migrations:

```bash
php artisan migrate --force
```

1. Limpe caches (importante para o menu aparecer):

```bash
php artisan optimize:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:clear
php artisan advanced-reports:flush-menus
```

> Este pacote contém migrations que **removem menus de relatórios do pacote Portabilis (Jasper)** antes de criar os menus temáticos (ver “Compatibilidade e migração”).

## Atualização do pacote (upgrade de versão)

Para ambientes onde o pacote **já está instalado** e você está apenas **atualizando a versão**:

1. Atualize o código do pacote no diretório `packages/serventec/i-educar-advanced-reports-package`.
1. Refaça o plug-and-play e descubra providers (se necessário):

```bash
composer plug-and-play
php artisan package:discover --ansi
```

1. Rode **apenas as migrations do pacote** (recomendado em updates):

```bash
php artisan migrate --path=packages/serventec/i-educar-advanced-reports-package/database/migrations --force
```

1. Limpe caches e o cache de menus:

```bash
php artisan optimize:clear
php artisan advanced-reports:flush-menus
```

## Validação pública (QR Code)

Documentos emitidos pelo módulo de **modelos** (ex.: certificados/declarações) incluem:

- **código de validação**
- **URL de validação pública** (sem login)
- **QR Code** apontando para a URL

Rota de validação:

- `/documentos/validar/{codigo}`

## Comportamento do PDF (prévia no navegador)

Ao emitir PDF, o pacote retorna o arquivo com `Content-Disposition: inline`, abrindo no navegador como **prévia** (com opções nativas de imprimir/baixar), sem download automático.

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

### 4) Documentos oficiais do aluno (PDF)

- **Rota (UI)**: `/relatorios-avancados/documentos`
- **PDF**: `/relatorios-avancados/documentos/pdf`
- **Documentos**:
  - Declaração de matrícula
  - Declaração de frequência
  - Guia/Declaração de transferência

### 5) Boletim do aluno (PDF)

- **Rota (UI)**: `/relatorios-avancados/boletim`
- **PDF**: `/relatorios-avancados/boletim/pdf`

### 6) Histórico escolar (PDF) — modelos

- **Rota (UI)**: `/relatorios-avancados/historico`
- **PDF**: `/relatorios-avancados/historico/pdf`
- **Modelos**:
  - `classic`, `modern` (padrões do pacote)
  - `simade_model_1`, `simade_model_32`, `simade_magisterio` (adaptações inspiradas em modelos do SIMADE)

### 7) Vagas por turma (Secretaria/Gestão)

- **Rota (UI)**: `/relatorios-avancados/vagas-turmas`
- **PDF**: `/relatorios-avancados/vagas-turmas/pdf`
- **Excel**: `/relatorios-avancados/vagas-turmas/excel`
- **Objetivo**: capacidade (`turma.max_aluno`), matriculados (enturmações ativas) e vagas disponíveis.

### 8) Atas (resultado final / assinaturas)

- **Rota (UI)**: `/relatorios-avancados/atas`
- **PDF**: `/relatorios-avancados/atas/pdf`
- **Documentos**:
  - Ata de resultados finais (com opção de detalhes por componente/etapa + frequência)
  - Lista de assinaturas (responsáveis)

## Compatibilidade e migração (remoção de Jasper/Portabilis)

Este pacote foi projetado para coexistir com o i-Educar, mas ao ser instalado:

- remove **submenus** clássicos de relatórios Jasper (Portabilis) na tabela `public.menus` (sem remover os nós padrão `Escola → Relatórios/Documentos`);
- cria uma árvore de **menus temáticos** que aponta para rotas deste pacote.

## Desenvolvimento

- **Padrão de views**: Blade, com `resources/views/partials/filters.blade.php` como base de filtros.
- **PDF**: `dompdf/dompdf` (HTML → PDF) via `PdfRenderService`.
- **Excel**: `maatwebsite/excel`.
- **QR Code**: `chillerlan/php-qrcode` via `QrCodeService`.

## API (autocomplete/lookup)

Para facilitar o uso nas telas (sem exigir que o usuário saiba IDs):

- `/relatorios-avancados/api/matriculas?q=...`
- `/relatorios-avancados/api/alunos?q=...`

## Documentação (executiva/técnica) e roadmap

- **Status do que já foi feito e pendências**: `docs/RELATORIOS_AVANCADOS_STATUS.md`
- **Resumo executivo/técnico do pacote**: `docs/EXECUTIVO_TECNICO.md`
- **Backlog e melhorias futuras**: `docs/MELHORIAS_FUTURAS.md`
