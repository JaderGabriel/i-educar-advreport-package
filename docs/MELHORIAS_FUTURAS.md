# Advanced Reports — Melhorias futuras / Backlog (com objetivo e estimativas)

Este backlog complementa o `docs/RELATORIOS_AVANCADOS_STATUS.md` com itens estruturais (qualidade, segurança, padronização) e com o que falta para cobrir os menus do doc executivo.

As estimativas abaixo assumem **1 dev** com domínio do i-Educar e acesso a uma base de homologação representativa. Variam bastante conforme **regras da rede**, **qualidade dos dados** e **exigências legais**.

## 1) Documentos oficiais do aluno (Prioridade 1)

- **Escolaridade / vida escolar / “nada consta”**
  - **Objetivo**: emitir um documento “oficial” resumindo vínculo e situação, sem expor dados sensíveis na validação pública.
  - **Estimativa**: 1–2 dias (mínimo) / 3–5 dias (com campos e variações por rede).
  - **Fatores/decisões**:
    - definir **conteúdo mínimo** (LGPD) e “o que entra no resumo público”;
    - padronizar textos legais e layout (rede/município);
    - decidir se é por **matrícula** (ano) ou por **aluno** (trajetória).

- **Declaração de frequência — frequência mensal real**
  - **Objetivo**: substituir o quadro mensal (hoje “—”) por cálculo mês a mês.
  - **Estimativa**: 2–4 dias (se houver fonte clara) / 1–2 semanas (se depender de regra/diário por rede).
  - **Fatores/decisões**:
    - fonte de dados por data (diário/chamada) varia por implantação;
    - regra de presença pode ser **global** ou **por componente**;
    - decisões de arredondamento e como tratar meses sem lançamento.

## 2) Pedagógico (Prioridade 1)

- **Mapas de notas/frequência por turma/etapa**
  - **Objetivo**: relatórios pedagógicos para conferência e entrega (padronização rede).
  - **Estimativa**: 1–2 semanas por variação principal (nota × parecer; etapas; composição de média).
  - **Fatores/decisões**:
    - diversidade de regras de avaliação;
    - carga de consultas e paginação;
    - definir “modo oficial” vs “modo conferência”.

- **Pendências de lançamento — refinamentos por regra**
  - **Objetivo**: tornar a identificação de pendências compatível com regras/etapas específicas por rede.
  - **Estimativa**: 2–5 dias.
  - **Fatores/decisões**:
    - compatibilidade com diário, períodos encerrados e permissões;
    - definição do que é “pendência” em cenários de RC/recuperação.

- **Espelho de diário**
  - **Objetivo**: emitir um espelho do diário consolidando lançamentos e frequências por componente/etapa.
  - **Estimativa**: 2–4 semanas.
  - **Fatores/decisões**:
    - depende fortemente do modelo de diário ativo na rede;
    - exige performance e paginação robustas.

## 3) Atas e registros formais (Prioridade 1/2)

- **Ata de conselho de classe**
  - **Objetivo**: documento formal de deliberações, geralmente com pautas/encaminhamentos.
  - **Estimativa**: 1–2 semanas.
  - **Fatores/decisões**:
    - conteúdo varia por rede; pode exigir campos “livres” e anexos;
    - definir se terá validação pública (QR) ou somente autenticação interna.

- **Ata de entrega de resultados (assinaturas)**
  - **Objetivo**: lista formal para coleta de assinaturas dos responsáveis.
  - **Estimativa**: 3–5 dias (reaproveita base de lista/ata).
  - **Fatores/decisões**:
    - regras de exibição (responsáveis, 1 por aluno, observações);
    - paginação e quebras de página.

## 4) Movimentações / Fluxo de Alunos (Prioridade 1/2)

- **Movimento mensal**
  - **Objetivo**: consolidar movimentações mês a mês (admissão, abandono, transferência etc.).
  - **Estimativa**: 1–2 semanas.
  - **Fatores/decisões**:
    - fonte de verdade pode ser view/relatório legado;
    - padronizar o que é considerado “entrada/saída” em redes com regras específicas.

- **Vagas por turma — regras por situação**
  - **Objetivo**: permitir configurar quais situações entram em “matriculados” (ex.: cursando vs transferidos).
  - **Estimativa**: 2–4 dias.
  - **Fatores/decisões**:
    - definir padrão e permitir override por rede;
    - impacto em performance (joins adicionais).

## 5) Auditoria / Validação / Autenticidade (evolução)

- **Página pública com “resumo amigável” e LGPD explícita**
  - **Objetivo**: melhorar compreensão da validação e reduzir exposição.
  - **Estimativa**: 2–5 dias.
  - **Fatores/decisões**:
    - definir quais campos são “públicos por padrão”;
    - texto legal e política de retenção.

- **Expiração/rotação de códigos (opcional por rede)**
  - **Objetivo**: permitir redes exigirem validade temporal de documentos.
  - **Estimativa**: 2–4 dias.
  - **Fatores/decisões**:
    - regra de expiração (por tipo, por rede, por data);
    - impacto na auditabilidade (documento “expirado” ainda valida?).

## 6) Padronização visual

- **Bloco “Filtros aplicados” em PDFs (quando aplicável)**
  - **Objetivo**: tornar PDFs autoexplicativos em auditoria/arquivamento.
  - **Estimativa**: 1–3 dias (base) + ajustes por documento.
  - **Fatores/decisões**:
    - evitar poluição visual em documentos oficiais;
    - padronizar nomes/labels por rede.

## 7) Performance/robustez

- **Paginação para listagens grandes na UI**
  - **Objetivo**: evitar limites fixos e garantir UX responsiva.
  - **Estimativa**: 3–5 dias por relatório crítico.
  - **Fatores/decisões**:
    - padrão de paginação (server-side) e persistência de filtros;
    - compatibilidade com exportações.

- **Excel por streaming/chunks**
  - **Objetivo**: exportar grandes volumes sem estourar memória/timeout.
  - **Estimativa**: 1–2 semanas (infra + adaptação por export).
  - **Fatores/decisões**:
    - trade-off entre simplicidade e robustez;
    - limite por rede e fila/assíncrono (job).
