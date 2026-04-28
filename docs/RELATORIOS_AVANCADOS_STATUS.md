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
- [x] PDF
- [x] Excel
- [x] Autocomplete de usuário (lookup)

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

### Fichas

- [x] Menu criado
- [ ] Controller + Service
- [ ] View filtros/resultados

### Históricos

- [x] Menu criado
- [x] Controller + Service (Histórico sem Jasper)
- [x] Rota `/relatorios-avancados/historico`
- [x] PDF com cabeçalho oficial + QR/HMAC
- [x] Múltiplos modelos (classic/modern + SIMADE 1/32/magistério) + prévia (modal)

### Documentos oficiais do aluno

- [x] Menu criado
- [x] Rota `/relatorios-avancados/documentos`
- [x] PDF (matrícula, frequência, guia/transferência) com QR/HMAC

### Diplomas/Certificados (modelos)

- [x] Menu criado
- [x] Prévia em modal na UI
- [x] Certificado/Declaração com cabeçalho oficial e rodapé profissional

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
