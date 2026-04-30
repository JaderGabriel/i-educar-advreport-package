# Relatórios Avançados – Status

## Escola > Relatórios

### Movimentações

- [x] Menu criado
- [x] Rota `/relatorios-avancados/movimentacoes`
- [x] Filtros base (ano, instituição, escola, curso)
- [ ] Serviço `MovementsGeneralReportService` (equivalente à Consulta de movimento geral)
- [ ] View de resultados (tabela com colunas do `educar_consulta_movimento_geral_lst.php`)
- [x] PDF
- [x] Excel

### Auditoria

- [x] Menu criado
- [x] Rota `/relatorios-avancados/auditoria/acessos-acoes`
- [x] View com filtros + resumo + listagens (acessos + alterações)
- [x] PDF (tabelas com largura fixa / quebra de texto para não vazar da página)
- [x] Excel
- [x] Autocomplete de usuário (lookup)
  - [x] Entrada no menu “Escola → Relatórios” após Indicadores (ajuste de ordem/compatibilidade)

### Avaliação e frequência (Pedagógico)

- [x] Menu criado (grupo)
- [x] Placeholders (telas de orientação) para itens do roadmap:
  - `/relatorios-avancados/pedagogico/mapa-notas`
  - `/relatorios-avancados/pedagogico/mapa-frequencia`
  - `/relatorios-avancados/pedagogico/espelho-diario`
  - `/relatorios-avancados/pedagogico/pendencias-lancamento`
- [ ] Implementação completa (serviços, PDF/Excel) por variação de regra/etapas/diário

### Lançamentos

- [x] Menu criado
- [ ] Controller + Service
- [ ] View filtros/resultados
- [ ] PDF

### Cadastrais

- [x] Menu criado
- [ ] Controller + Service
- [ ] View filtros/resultados
- [ ] PDF

### Matrículas

- [x] Menu criado
- [ ] Controller + Service
- [ ] View filtros/resultados
- [ ] PDF

### Indicadores

- [x] Menu criado
- [x] Submenu Socioeconômicos
- [x] Rota `/relatorios-avancados/socioeconomicos`
- [x] Serviço `SocioeconomicReportService` (agregações básicas)
- [ ] Ajustar métricas para espelhar exatamente o report oficial
- [x] PDF com gráficos (opção `with_charts=1`)
- [x] PDF com cabeçalho formal + rodapé de validação (QR) + registro `socioeconomic_report`
- [x] Excel

### Vagas por turma

- [x] Menu criado (item sob Movimentações)
- [x] Rota `/relatorios-avancados/vagas-turmas`
- [x] PDF
- [x] Excel
- [ ] Refinar regras de “matriculados” por situação (quando necessário por rede)

### Alunos por situação

- [x] Menu criado (item sob Movimentações)
- [x] Rota `/relatorios-avancados/alunos-por-situacao`
- [x] View com filtros + resumo + listagem
- [x] PDF
- [x] Excel

#### Inclusão (NEE)

- [x] Menu criado
- [x] Rota `/relatorios-avancados/inclusao`
- [x] PDF (opção `with_charts=1`)
- [x] Excel (múltiplas abas)

#### Distorção idade/série

- [x] Menu criado
- [x] Rota `/relatorios-avancados/distorcao-idade-serie`
- [x] PDF (opção `with_charts=1`)
- [x] Excel (múltiplas abas)

#### Vulnerabilidade / Assistência social

- [x] Menu criado
- [x] Rota `/relatorios-avancados/vulnerabilidade`
- [x] PDF (opção `with_charts=1`)
- [x] Excel (múltiplas abas)

## Validação pública de documentos

- [x] Rota pública `/documentos/validar/{codigo}` (sem login)
- [x] QR Code nos PDFs (certificado/declaração)
- [x] Persistência em `advanced_reports_documents`

## UX

- [x] PDF abre como prévia no navegador (inline, sem download automático)
- [x] Autocomplete/lookup para Aluno/Matrícula em telas de emissão
- [x] Hardening de Excel contra injeção de fórmula (sanitização de células em exports do pacote)

## Escola > Documentos

### Atestados

- [x] Menu criado
- [ ] Controller + Service
- [ ] View filtros/resultados

### Boletins

- [x] Menu criado
- [x] Controller + Service (Boletim sem Jasper)
- [x] Rota `/relatorios-avancados/boletim`
- [x] PDF com cabeçalho oficial + QR/HMAC

### Resultados

- [x] Menu criado
- [ ] Controller + Service
- [ ] View filtros/resultados

### Históricos

- [x] Menu criado
- [x] Controller + Service (Histórico sem Jasper)
- [x] Rota `/relatorios-avancados/historico`
- [x] PDF com cabeçalho oficial + QR/HMAC
- [x] Múltiplos modelos (classic/modern + SIMADE 1/32/magistério) + prévia (modal)
- [x] Emissão em lote (multi-aluno) com validação por aluno (SIMADE restrito a 1 aluno)
- [x] Documento executivo comparativo nativo × pacote: `docs/EXECUTIVO-HISTORICO-ESCOLAR-FLUXO-NATIVO-VS-PACOTE.md`

### Documentos oficiais do aluno

- [x] Menu criado
- [x] Rota `/relatorios-avancados/documentos`
- [x] PDF (matrícula, frequência, guia/transferência) com QR/HMAC
- [x] Submenu **Fichas** (primeiro item do grupo): **Ficha individual** e **Ficha de matrícula**
  - [x] Rotas UI: `/relatorios-avancados/fichas/ficha-individual`, `/relatorios-avancados/fichas/ficha-matricula`
  - [x] PDF: `/relatorios-avancados/fichas/ficha-individual/pdf`, `/relatorios-avancados/fichas/ficha-matricula/pdf` (individual e lote; prévia sem persistir registro)
  - [x] Ficha individual: assinaturas secretário(a) e diretor(a) da escola (quando cadastrados)
  - [x] Ficha de matrícula: bloco destacado de autorização imagem/voz + assinaturas responsável/emissor

### Comunicados (Escola → Documentos)

- [x] Menu criado (antes de **Atas e registros formais**)
- [x] Submenus: Convocações, Reuniões, Advertências, Comunicado geral (item 8.6 do backlog; **sem** livro/ficha de ocorrências)
- [x] Rotas placeholder: `/relatorios-avancados/comunicados/{convocacao|reuniao|advertencia|comunicado-geral}`
- [ ] PDF modelo + parametrização por rede

### Diplomas/Certificados (modelos)

- [x] Menu criado
- [x] Prévia em modal na UI
- [x] Certificado/Declaração com cabeçalho oficial e rodapé profissional
- [x] Diploma ajustado para o mesmo padrão (sem cortes/páginas em branco)

### Atas e registros formais

- [x] Menu criado
- [x] Rota `/relatorios-avancados/atas`
- [x] PDF: Ata de resultados finais e Lista de assinaturas (responsáveis)
- [x] Opção de detalhes por componente/etapa e frequência (quando disponível)

### Registros

- [x] Menu criado
- [ ] Controller + Service
- [ ] View filtros/resultados

## Funcionários > Relatórios

### Cadastrais (Funcionários)

- [x] Menu criado
- [ ] Controller + Service
- [ ] View filtros/resultados

## Funcionários > Documentos

- [x] Menu criado
- [ ] Submenus definidos conforme reports Portabilis
- [ ] Controllers + Services
- [ ] Views filtros/resultados
