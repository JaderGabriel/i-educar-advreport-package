# Advanced Reports — Melhorias futuras / Backlog (com objetivo e estimativas)

Este backlog complementa o `docs/RELATORIOS_AVANCADOS_STATUS.md` com itens estruturais (qualidade, segurança, padronização) e com o que falta para cobrir os menus do doc executivo.

As estimativas abaixo assumem **1 dev** com domínio do i-Educar e acesso a uma base de homologação representativa. Variam bastante conforme **regras da rede**, **qualidade dos dados** e **exigências legais**.

## 0) Entregas recentes (já implementado no pacote)

Itens abaixo já foram implementados e ficam aqui apenas para histórico/visibilidade (o status detalhado está em `docs/RELATORIOS_AVANCADOS_STATUS.md`):

- **Histórico escolar (modelos)**:
  - modelos `classic`, `modern` e SIMADE (1/32/magistério);
  - **cabeçalho oficial** (formal_header) e **assinaturas** padronizadas (emissor + diretor/secretário quando aplicável).
- **Atas**:
  - Ata de entrega de resultados (assinaturas de responsáveis);
  - Ata de conselho de classe por turma (com quebra de página e assinaturas).
- **Espelho de diário** (primeira entrega) com presença `•` e falta `F`.
- **Movimentações (geral)**: PDF/Excel com padronização de cabeçalho/rodapé/validação e correções de contadores.
- **Auditoria**: tela + PDF + Excel e entrada de menu em “Escola → Relatórios”.
- **Indicadores socioeconômicos**:
  - gráficos com **UTF‑8/acentos** e **paleta de cores** consistente;
  - rótulos humanizados (raça/cor e gênero) em PDF/Excel.
- **Diploma/Certificado/Declaração (modelos)**:
  - refatoração do CSS para evitar **texto cortado**, **vazamento lateral** e **páginas em branco** (Dompdf).

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

- **Espelho de diário — completar versão “por componente/etapa”**
  - **Objetivo**: evoluir o espelho para contemplar componentes/etapas e regras de diário (a primeira entrega é mais simples).
  - **Estimativa**: 2–4 semanas.
  - **Fatores/decisões**:
    - depende fortemente do modelo de diário ativo na rede;
    - exige performance e paginação robustas.

## 3) Atas e registros formais (Prioridade 1/2)

- **Ata de conselho de classe**
  - **Objetivo**: documento formal de deliberações, geralmente com pautas/encaminhamentos.
  - **Estimativa**: 1–2 semanas (para evoluir conteúdo livre/pautas/encaminhamentos).
  - **Fatores/decisões**:
    - conteúdo varia por rede; pode exigir campos “livres” e anexos;
    - definir se terá validação pública (QR) ou somente autenticação interna.

- **Ata de entrega de resultados (assinaturas)**
  - **Objetivo**: lista formal para coleta de assinaturas dos responsáveis.
  - **Estimativa**: 3–5 dias (para refinamentos de regra/paginação/observações por rede).
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

## 4.1) Indicadores (Prioridade 2)

- **Indicadores de desempenho/resultado (placeholders)**:
  - baixo desempenho; alto desempenho; sem nota; não enturmados; comparativo de médias da turma.
  - **Objetivo**: sair de “orientação” e entregar relatório real (UI + PDF + Excel) no padrão do pacote.
  - **Estimativa**: 1–3 semanas (varia conforme regra de avaliação e volume de dados).
  - **Fatores/decisões**:
    - definir recortes (ano/instituição/escola/curso/série/turma, etapa, componente);
    - regra de média/limiar e como tratar RC/recuperação;
    - performance e paginação (UI e export).

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

## 8) Sugestões (referências de mercado) — ideias de documentos/relatórios ainda não cobertos

Os itens abaixo são **sugestões** com base em documentos frequentemente emitidos por redes/escolas e/ou presentes em outros sistemas de gestão escolar. Entram como “candidatos” a novos módulos, priorizáveis conforme demanda da rede.

### 8.1 Documentos do aluno e secretaria

- **Comprovante/Atestado de matrícula (variações)**: emissão rápida (por aluno/turma/ano) e em lote; útil para benefícios, transporte e processos administrativos.
  - **Status**: **parcial** (já existe Declaração de matrícula; faltam variações focadas em “atestado/comprovante” e emissão em lote).
  - **Prioridade**: **P1**
  - **Dificuldade**: **Baixa**
  - **Estimativa**: **1–2 dias**

- **Declaração de vaga/Reserva de vaga**: documento de confirmação de vaga (muito usado em educação infantil e transferências).
  - **Status**: **não implementado**
  - **Prioridade**: **P2**
  - **Dificuldade**: **Baixa**
  - **Estimativa**: **1–3 dias**

- **Declaração de comparecimento**: comprova presença do aluno/responsável na unidade (atendimento, reunião, retirada de documento).
  - **Status**: **não implementado**
  - **Prioridade**: **P2**
  - **Dificuldade**: **Baixa**
  - **Estimativa**: **1–2 dias**

- **Requerimentos padronizados (secretaria)**: solicitação de 2ª via, cancelamento, revisão de dados, transferência, etc. (modelo “formulário” + emissão PDF).
  - **Status**: **não implementado**
  - **Prioridade**: **P2**
  - **Dificuldade**: **Média** (depende de fluxo: protocolo, assinatura, histórico, anexos)
  - **Estimativa**: **1–2 semanas** (primeiro conjunto + base reutilizável)

- **Ficha de matrícula (formulário completo)**: export/print da ficha com dados do aluno/responsáveis/contatos/saúde/NEE.
  - Referência (conceito/uso): `https://gestaoescolar.org.br/conteudo/557/como-usar-as-informacoes-das-fichas-de-matricula`
  - **Status**: **não implementado**
  - **Prioridade**: **P2**
  - **Dificuldade**: **Média** (foto, layout, validação e variações por rede)
  - **Estimativa**: **3–7 dias**

- **Carteirinha escolar / declaração para carteirinha**: emissão com foto e dados mínimos, com validade por ano.
  - Referências: `https://atendimento.educacao.sp.gov.br/artigos-de-base-de-conhecimento/~/knowledgebase/article/SED-04527/pt-br` e `https://blog.documentodoestudante.com.br/blog/2026/02/19/carteirinha-escolar-guia-completo-para-estudantes/`
  - **Status**: **não implementado**
  - **Prioridade**: **P3** (alta demanda em algumas redes; opcional)
  - **Dificuldade**: **Média**
  - **Estimativa**: **3–7 dias**

### 8.2 Pedagógico (turma/aluno)

- **Relatório pedagógico descritivo do aluno**: modelo com campos orientados (aprendizagem, participação, socioemocional, recomendações) + assinaturas.
  - **Status**: **não implementado**
  - **Prioridade**: **P2**
  - **Dificuldade**: **Média** (depende de campos e política da rede; pode ser “modelo” com campos livres)
  - **Estimativa**: **1–2 semanas**

- **Plano de aula / registro de atividades**: impressão de planejamento (semanal/mensal) e registro de conteúdo trabalhado por aula/data.
  - Referência (ideias): `https://blog.clipescola.com/diario-de-classe-organizado-sem-retrabalho/`
  - **Status**: **não implementado**
  - **Prioridade**: **P3**
  - **Dificuldade**: **Alta** (depende do modelo de diário/lançamentos adotado e de performance)
  - **Estimativa**: **2–6 semanas**

- **Mapas operacionais (conferência)**: mapas de notas e frequência “para conferência” com recortes por turma/etapa/componente.
  - Referência (modelo): `https://proen.ufopa.edu.br/proen/midias/arquivos/secao-mais/formularios/modelo-mapa-de-notas-e-frequencia/`
  - **Status**: **parcial** (placeholders no bloco pedagógico; pendências e atas já cobrem parte)
  - **Prioridade**: **P1**
  - **Dificuldade**: **Alta** (regras de avaliação/frequência variam por rede)
  - **Estimativa**: **2–6 semanas** (por variação principal)

### 8.3 Inclusão / AEE (documentos pedagógicos)

- **Plano de AEE (Atendimento Educacional Especializado)**: documento com metas, barreiras, estratégias, recursos, periodicidade e registros.
  - **Status**: **não implementado**
  - **Prioridade**: **P2**
  - **Dificuldade**: **Alta** (regras e campos variam; pode iniciar como modelo com campos livres)
  - **Estimativa**: **2–6 semanas**

- **PEI/PDI (quando adotado pela rede)**: modelos opcionais, com atenção às particularidades locais.
  - Referência (discussão e diferença): `https://diversa.org.br/noticias/plano-de-aee-pei-ou-pdi-entenda-a-diferenca-entre-eles/`
  - **Status**: **não implementado**
  - **Prioridade**: **P3** (dependente de política local)
  - **Dificuldade**: **Alta**
  - **Estimativa**: **2–6 semanas**

### 8.4 Transporte e benefícios (gestão)

- **Relatórios de transporte escolar**:
  - alunos transportados por rota/parada/turno;
  - carteirinha do transporte;
  - frequência de embarque/desembarque (quando há solução de apontamento).
  - Referências (módulos/ideias): `https://suporte.proesc.com/hc/pt-br/articles/26482484579223--FAQ-M%C3%B3dulo-de-Transporte-Escolar` e `https://help.sistemaquality.com.br/2024/05/14/2-17-transporte-escolar/`
  - **Status**: **não implementado**
  - **Prioridade**: **P3**
  - **Dificuldade**: **Alta** (depende de modelagem de rotas/paradas/terceiros e, às vezes, apontamento)
  - **Estimativa**: **2–8 semanas**

### 8.5 Biblioteca / patrimônio (administração)

- **Relatório de empréstimos e pendências da biblioteca**: por aluno/turma, atrasos, multas (quando aplicável).
  - Referência (ideias): `https://suporte.proesc.com/hc/pt-br/articles/1500003008722-Empr%C3%A9stimos-devolu%C3%A7%C3%A3o-e-relat%C3%B3rio`
  - **Status**: **não implementado**
  - **Prioridade**: **P3**
  - **Dificuldade**: **Média/Alta** (depende se a rede usa módulo de biblioteca e quais tabelas existem)
  - **Estimativa**: **1–3 semanas**

- **Termo de responsabilidade**: por equipamentos/armários/livros (assinatura do responsável).
  - Referência (exemplo PDF): `https://www.ifmg.edu.br/betim/noticias/cadastro-para-uso-dos-escaninhos/modelo-termo-de-responsabilidade.pdf/view`
  - **Status**: **não implementado**
  - **Prioridade**: **P3**
  - **Dificuldade**: **Baixa**
  - **Estimativa**: **1–3 dias**

### 8.6 Comunicação e ocorrências

- **Comunicados oficiais** (modelo): convocações, reuniões, advertências, comunicado geral (com cabeçalho padrão).
  - Referência (ideias): `https://blog.clipescola.com/dicas-e-modelos-de-comunicados-escolares/`
  - **Status**: **não implementado**
  - **Prioridade**: **P3**
  - **Dificuldade**: **Baixa/Média** (modelo; pode evoluir para envio/registro)
  - **Estimativa**: **2–5 dias**

- **Livro/Ficha de ocorrências**: registro de incidentes/indisciplina, encaminhamentos, ciência do responsável, histórico por aluno.
  - Referência (conceito): `https://educador.brasilescola.uol.com.br/trabalho-docente/livro-ocorrencias.htm`
  - **Status**: **não implementado**
  - **Prioridade**: **P2**
  - **Dificuldade**: **Alta** (fluxo, permissões, anexos, trilha/auditoria e LGPD)
  - **Estimativa**: **3–8 semanas**

### 8.7 LGPD/Consentimentos/Autorizações

- **Termo de autorização de uso de imagem/voz** (modelos configuráveis por rede).
  - Referência (LGPD/boas práticas): `https://ibee.com.br/materia/parecer-e-modelo-de-uso-de-imagem-para-escolas-lgpd-e-eca-digital/`
  - **Status**: **não implementado**
  - **Prioridade**: **P2**
  - **Dificuldade**: **Média** (texto/variações por rede + assinatura/responsável)
  - **Estimativa**: **1–2 semanas**

### 8.8 Indicadores gerenciais adicionais (gestão)

- **Evasão/abandono (painéis/relatórios)**: recortes por turma/escola/idade/série/sexo/cor-raça, com histórico e taxa.
  - Referência (indicadores): `https://imdsbrasil.org/indicador/abandono-e-evasao/`
  - **Status**: **não implementado** (há “Movimentações” e “Alunos por situação”, mas não o painel/taxas)
  - **Prioridade**: **P2**
  - **Dificuldade**: **Alta** (conceitos e regras por rede; definição de “evasão” vs “abandono”)
  - **Estimativa**: **2–6 semanas**

- **Lista de espera e fila de vagas**: relatórios por etapa/unidade/região (com transparência e trilha).
  - Referência (exemplos em portais de transparência municipais): `https://portaldatransparencia.barreiras.ba.gov.br/pdc-lista-de-espera-para-unidades-escolares/`
  - **Status**: **não implementado**
  - **Prioridade**: **P3**
  - **Dificuldade**: **Alta** (exige modelagem de demanda/fila, regras de priorização e auditoria)
  - **Estimativa**: **4–12 semanas**

### 8.9 Observação: integração com referências oficiais (INEP)

- Itens relacionados ao **Censo Escolar/Educacenso** podem render relatórios de conferência e validação de cadastros (antes do envio).
  - Referência: `https://www.gov.br/inep/pt-br/acesso-a-informacao/perguntas-frequentes/censo-escolar`
