# Advanced Reports — Melhorias futuras / Backlog

Este backlog complementa o `docs/RELATORIOS_AVANCADOS_STATUS.md` com itens estruturais (qualidade, segurança, padronização) e com o que falta para cobrir os menus do doc executivo.

## 1) Documentos oficiais do aluno (Prioridade 1)

- Boletim (oficial)
- Histórico (regular/EJA)
- Declarações oficiais (matrícula, frequência, conclusão)
- Guia/declaração de transferência
- Escolaridade / vida escolar / “nada consta” (com escopo bem definido)

## 2) Pedagógico (Prioridade 1)

- Mapas de notas/frequência por turma/etapa
- Pendências de lançamento (notas/frequência)
- Espelho de diário
- Ficha individual (fundamental) e acompanhamento (infantil)

## 3) Atas e registros formais (Prioridade 1/2)

- Ata de resultados finais (**implementada versão inicial**; evoluções: mapas por etapa, quadro por componente e assinaturas avançadas)
- Lista de assinaturas (responsáveis) (**implementada**)
- Ata de conselho de classe
- Ata de entrega de resultados (assinaturas)
- Livro de chamadas (opcional)

## 4) Movimentações (Prioridade 1/2)

- Movimento mensal
- Relatório de alunos por situação
- Vagas/turmas (capacidade/ocupação) (**implementado: vagas por turma**; evoluções: regras por situação/considerar transferências/remanejamentos)

## 5) Validação/autenticidade (evolução)

- Gerar **código com assinatura criptográfica** (ex.: HMAC com chave da aplicação) e armazenar assinatura/versão.
- Incluir **QR Code** também em outros documentos oficiais quando implementados.
- Página pública de validação:
  - exibir “resumo amigável” (campos relevantes) e ocultar payload sensível por padrão
  - exibir aviso de LGPD e finalidade
- Rotacionar/expirar códigos (opcional por rede).
- Exportar trilha de auditoria (usuário emissor, IP, parâmetros) quando houver login.

## 6) Padronização visual

- Consolidar estilos do pacote (sem CSS duplicado nas views).
- Padronizar layout de tabelas PDF e quebras de página (Dompdf).
- Adicionar “bloco de filtros” no PDF (ex.: Instituição/Escola/Curso selecionados) quando aplicável.

## 8) UX (melhorias)

- Autocomplete/lookup (Aluno/Matrícula) em todas as telas que ainda usam ID manual.
- Prévia em modal para todos os documentos com modelos (onde aplicável).

## 7) Performance/robustez

- Paginação para listagens grandes na UI (evitar `limit(5000)` fixo).
- Excel por streaming/chunks para grandes volumes.
- Cache de agregações para dashboards (quando necessário).
