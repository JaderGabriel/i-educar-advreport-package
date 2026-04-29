# Documento executivo — Histórico escolar: fluxo nativo i-Educar × pacote Advanced Reports

## 1. Objetivo e público

Este documento compara, em linguagem **executiva e técnica resumida**, como o **histórico escolar** é tratado no **i-Educar nativo** (cadastro, processamento e relatórios legados) versus o **pacote `i-educar-advanced-reports-package`** (emissão em PDF com validação). Serve para decisões de **produto**, **governança de dados** e **arquitetura** (unificação de fluxos).

---

## 2. Natureza dos dados (“perenes”)

O histórico escolar no i-Educar é, por desenho, um **registro consolidado por aluno e por ano/série (sequencial)**, com **componentes curriculares** associados. Em geral:

- Trata-se de dado **oficial**, **versionado na prática** pelo par `(ref_cod_aluno, sequencial)` e pelas linhas de disciplinas.
- Pode ter **origem interna** (gerado a partir de matrícula/processamento) ou **externa** (digitado/importado), conforme campos de origem e cadastro legado.
- Alterações após consolidação dependem de **política institucional** (`restringir_historico_escolar`, vínculo usuário–escola, nível de permissão).

**Conclusão para o produto:** qualquer canal de **emissão** (nativo Jasper, API, pacote PHP) deve considerar o mesmo **conjunto canônico de tabelas** e as mesmas **regras de negócio institucionais**; divergências entre canais geram **risco reputacional e jurídico**.

---

## 3. Tabelas e objetos principais (fonte canônica)

| Área | Tabela / objeto | Papel |
|------|-----------------|--------|
| Aluno | `pmieducar.aluno` | Chave do estudante (`cod_aluno`). |
| Pessoa | `cadastro.pessoa` (+ documentos, município, etc.) | Nome e dados civis exibidos em documentos. |
| Histórico (cabeçalho por ano/série) | `pmieducar.historico_escolar` | Ano, série, escola (texto), frequência, livro/folha/registro, observações, aprovação, carga horária, etc. |
| Histórico (disciplinas) | `pmieducar.historico_disciplinas` | Notas, faltas, CH por componente, dependência, ordenação. |
| Instituição | `pmieducar.instituicao` | Parâmetros (ex.: restrição de edição, geração em transferência). |
| Matrícula (contexto) | `pmieducar.matricula`, `pmieducar.matricula_turma` | Usados no **processamento** e em filtros de turma; o PDF do pacote pode listar alunos por turma, mas o **conteúdo do histórico** continua sendo o consolidado por aluno. |

**Modelos Eloquent usados pelo pacote (leitura):** `LegacySchoolHistory`, `LegacySchoolHistoryDiscipline`, `LegacyIndividual`, etc., sobre as mesmas tabelas físicas.

---

## 4. Fluxo nativo (i-Educar)

### 4.1 Cadastro e consulta (intranet legada)

- **Listagem:** `ieducar/intranet/educar_historico_escolar_lst.php` — lista registros ativos de `historico_escolar` para um `ref_cod_aluno`, com filtros (ano, instituição, extra-curricular…).
- **Detalhe:** `ieducar/intranet/educar_historico_escolar_det.php` — exibe cabeçalho do sequencial e monta tabela HTML de disciplinas via `clsPmieducarHistoricoDisciplinas` (`historico_disciplinas`).
- **Cadastro / edição:** `ieducar/intranet/educar_historico_escolar_cad.php` — manutenção com permissão de processo (ex.: 578) e regras de restrição por escola do usuário quando `restringir_historico_escolar` está ativo.

**Ponto-chave:** o nativo é centrado em **CRUD e conferência** na intranet; a impressão “oficial” costuma ser outro pipeline (abaixo).

### 4.2 Processamento / API (módulo Histórico escolar)

- Existe o módulo `ieducar/modules/HistoricoEscolar/` (ex.: `ProcessamentoApiController`) com operações de **criar/atualizar/apagar** histórico e disciplinas a partir de matrícula/turma — alinhado ao fluxo de **fechamento** ou **reprocessamento** escolar.

### 4.3 Relatório impresso (Jasper / Portabilis)

- O i-Educar prevê fábrica de relatórios (`Portabilis_Report_ReportFactoryPHPJasper`, `ReportSources`, etc.) descrita no repositório em `docs/DOC-EXECUTIVO-PACOTE-RELATORIOS-PORTABILIS.md`.
- Neste workspace **não há** inventário completo de `.jrxml` de histórico; em muitas instalações o PDF “oficial” histórico vem do **pacote de relatórios Jasper** com parâmetros típicos de aluno/ano/instituição.

**Resumo:** nativo = **dados + políticas + (opcional) Jasper**; o usuário percebe “emissão” como algo acoplado ao **menu de relatórios** ou exportações legadas, não necessariamente à mesma URL do pacote PHP.

---

## 5. Fluxo do pacote Advanced Reports (atual)

- **Entrada:** rota `GET /relatorios-avancados/historico` — filtros em cascata (ano, instituição, escola, curso, série, turma), seleção múltipla de alunos (por enturmação), modelo de layout (clássico, moderno, SIMADE, MG…).
- **Montagem de dados:** `SchoolHistoryService::build($alunoId)` — lê `historico_escolar` + `historico_disciplinas` + pessoa/documentos para cabeçalho civil.
- **Saída:** `GET /relatorios-avancados/historico/pdf` — `PdfRenderService` (Dompdf), com **registro de documento** em `advanced_reports_documents` (tipo `historico`), **MAC** e **QR** para validação pública.
- **Lote:** vários alunos geram vários registros de validação e um PDF composto (modelos SIMADE limitados a um aluno por questão de layout).

**Ponto-chave:** o pacote **não grava** histórico; apenas **lê** o que já está consolidado e **emite** com trilha de autenticidade própria do pacote.

---

## 6. Matriz comparativa (sintética)

| Dimensão | Nativo (intranet + Jasper típico) | Pacote Advanced Reports |
|----------|-----------------------------------|-------------------------|
| Fonte de dados | `historico_escolar` / `historico_disciplinas` | Idem |
| Alteração de dados | Cadastro + API processamento | Não altera |
| Emissão PDF | Motor Jasper / pacote reports | Dompdf + Blade |
| Validação pública | Depende do desenho do relatório Jasper | QR + endpoint do pacote |
| Filtros de emissão | Parâmetros do relatório | Cascata por rede + turma |
| Permissões | Processos AP legados + regras institucionais | Middleware `auth` + menu do pacote |
| Risco de divergência | Dois motores de layout (Jasper × PHP) se coexistirem | Mitigar com política única de “documento oficial” |

---

## 7. Sugestões de unificação de fluxo (viáveis)

1. **Um único “serviço de leitura” compartilhado**  
   Extrair (ou reutilizar) a montagem de DTO de histórico a partir de `LegacySchoolHistory` / queries equivalentes, consumida tanto por relatórios Jasper (via API interna) quanto pelo pacote — evita duas interpretações de ordenação (`posicao`, `ordenamento`) ou campos opcionais.

2. **Política explícita de “documento oficial”**  
   Definir por secretaria: **Jasper** OU **pacote PHP** como padrão de histórico com validação; o outro fica como cópia de trabalho ou pré-visualização. Dados continuam únicos nas tabelas `pmieducar`.

3. **Deep link intranet ↔ pacote**  
   Na tela `educar_aluno_det.php` (link “Atualizar histórico”), oferecer “Emitir PDF (validado)” apontando para o pacote com `aluno_id` pré-preenchido — um fluxo cognitivo para o usuário.

4. **Respeitar `restringir_historico_escolar` no pacote (futuro)**  
   Hoje o pacote filtra por rede/turma na emissão em lote; avaliar checagem adicional contra escolas do usuário logado espelhando a regra da intranet.

5. **SIMADE / modelos estaduais**  
   Manter um único catálogo de modelos e textos legais; o nativo pode continuar em Jasper enquanto o pacote cobre subset em Blade — documentar qual modelo é “fonte” para auditoria externa.

---

## 8. Observação sobre XSS e planilhas

- **XSS em navegador** não é o vetor típico de arquivos `.xlsx` gerados no servidor.
- O risco relevante é **injeção de fórmula** (às vezes chamada de “CSV injection” / Excel formula injection): valores que começam com `=`, `+`, `-`, `@`, etc., podem ser interpretados por Excel ao abrir o arquivo.
- O pacote passou a **sanitizar células** nas exportações que usam `SimpleArraySheet` e na exportação de vagas (`VacanciesBySchoolClassExport`). Ver `SpreadsheetFormulaInjectionGuard` e notas em código.

---

## 9. Referências internas (código)

- Pacote: `SchoolHistoryService.php`, `SchoolHistoryController.php`, views `resources/views/school-history/`.
- Nativo: `educar_historico_escolar_*.php`, `clsPmieducarHistoricoEscolar`, módulo `HistoricoEscolar/`.
- Relatórios Jasper (visão geral): `docs/DOC-EXECUTIVO-PACOTE-RELATORIOS-PORTABILIS.md`.

---

*Documento gerado para apoio à decisão; ajustar nomes de processos, rotas e políticas conforme a realidade da rede implantada.*
