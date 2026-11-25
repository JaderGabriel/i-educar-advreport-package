# Changelog

## 2.12.1 — 2026-11-24

### Adicionado
- Catálogo público de relatórios em `/relatorios-avancados` (`ReportsCatalog` + `CatalogController`) para demonstrar cobertura típica de edital (PDF, XLSX, QR).
- Menu **Escola → Relatórios → Catálogo (editais)** (old `9999705`).
- Config `advanced-reports.php` com formatos nativos vs. formatos de referência de TR.
- Campo `version` no `composer.json` (`2.12.1`) e leitura via `ReportsCatalog::versaoPacote()`.

### Alterado
- Descrição do pacote enfatiza emissão sem Jasper/Java e validação por QR.
- Homepage padrão aponta para o espelho na org `i-Educar`.

## 2.12.0

- Compatibilidade i-Educar 2.12 / Laravel 13 / PHP ≥ 8.3.
- Menus temáticos, documentos oficiais, indicadores, auditoria e validação pública.
