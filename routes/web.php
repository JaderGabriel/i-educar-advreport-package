<?php

use iEducar\Packages\AdvancedReports\Http\Controllers\SocioeconomicReportController;
use iEducar\Packages\AdvancedReports\Http\Controllers\DiplomaReportController;
use iEducar\Packages\AdvancedReports\Http\Controllers\MovementsReportController;
use iEducar\Packages\AdvancedReports\Http\Controllers\InclusionIndicatorsController;
use iEducar\Packages\AdvancedReports\Http\Controllers\AgeDistortionController;
use iEducar\Packages\AdvancedReports\Http\Controllers\SocialVulnerabilityController;
use iEducar\Packages\AdvancedReports\Http\Controllers\DocumentValidationController;
use iEducar\Packages\AdvancedReports\Http\Controllers\StudentDocumentsController;
use iEducar\Packages\AdvancedReports\Http\Controllers\BoletimController;
use iEducar\Packages\AdvancedReports\Http\Controllers\SchoolHistoryController;
use iEducar\Packages\AdvancedReports\Http\Controllers\LookupController;
use iEducar\Packages\AdvancedReports\Http\Controllers\VacanciesBySchoolClassController;
use iEducar\Packages\AdvancedReports\Http\Controllers\MinutesController;
use iEducar\Packages\AdvancedReports\Http\Controllers\PedagogicalController;
use iEducar\Packages\AdvancedReports\Http\Controllers\PendingEntriesController;
use iEducar\Packages\AdvancedReports\Http\Controllers\StudentsBySituationController;
use iEducar\Packages\AdvancedReports\Http\Controllers\AuditUsersReportController;
use Illuminate\Support\Facades\Route;

// Validação pública (sem login)
Route::middleware(['web'])->group(function () {
    Route::get('/documentos/validar/{code}', [DocumentValidationController::class, 'show'])
        ->name('advanced-reports.documents.validate');
});

Route::middleware([
    'web',
    'ieducar.navigation',
    'ieducar.footer',
    'ieducar.xssbypass',
    'ieducar.suspended',
    'auth',
    'ieducar.checkresetpassword',
    'ieducar.advanced-reports.menu',
])->group(function () {
    Route::get('/relatorios-avancados/api/matriculas', [LookupController::class, 'matriculas'])
        ->name('advanced-reports.lookup.matriculas');
    Route::get('/relatorios-avancados/api/alunos', [LookupController::class, 'alunos'])
        ->name('advanced-reports.lookup.alunos');
    Route::get('/relatorios-avancados/api/usuarios', [LookupController::class, 'users'])
        ->name('advanced-reports.lookup.users');

    Route::get('/relatorios-avancados/socioeconomicos', [SocioeconomicReportController::class, 'index'])
        ->name('advanced-reports.socioeconomic.index');
    Route::get('/relatorios-avancados/socioeconomicos/pdf', [SocioeconomicReportController::class, 'pdf'])
        ->name('advanced-reports.socioeconomic.pdf');
    Route::get('/relatorios-avancados/socioeconomicos/excel', [SocioeconomicReportController::class, 'excel'])
        ->name('advanced-reports.socioeconomic.excel');

    Route::get('/relatorios-avancados/inclusao', [InclusionIndicatorsController::class, 'index'])
        ->name('advanced-reports.inclusion.index');
    Route::get('/relatorios-avancados/inclusao/pdf', [InclusionIndicatorsController::class, 'pdf'])
        ->name('advanced-reports.inclusion.pdf');
    Route::get('/relatorios-avancados/inclusao/excel', [InclusionIndicatorsController::class, 'excel'])
        ->name('advanced-reports.inclusion.excel');

    Route::get('/relatorios-avancados/distorcao-idade-serie', [AgeDistortionController::class, 'index'])
        ->name('advanced-reports.age-distortion.index');
    Route::get('/relatorios-avancados/distorcao-idade-serie/pdf', [AgeDistortionController::class, 'pdf'])
        ->name('advanced-reports.age-distortion.pdf');
    Route::get('/relatorios-avancados/distorcao-idade-serie/excel', [AgeDistortionController::class, 'excel'])
        ->name('advanced-reports.age-distortion.excel');

    Route::get('/relatorios-avancados/vulnerabilidade', [SocialVulnerabilityController::class, 'index'])
        ->name('advanced-reports.social-vulnerability.index');
    Route::get('/relatorios-avancados/vulnerabilidade/pdf', [SocialVulnerabilityController::class, 'pdf'])
        ->name('advanced-reports.social-vulnerability.pdf');
    Route::get('/relatorios-avancados/vulnerabilidade/excel', [SocialVulnerabilityController::class, 'excel'])
        ->name('advanced-reports.social-vulnerability.excel');

    Route::get('/relatorios-avancados/movimentacoes', [MovementsReportController::class, 'index'])
        ->name('advanced-reports.movements.index');
    Route::get('/relatorios-avancados/movimentacoes/pdf', [MovementsReportController::class, 'pdf'])
        ->name('advanced-reports.movements.pdf');
    Route::get('/relatorios-avancados/movimentacoes/excel', [MovementsReportController::class, 'excel'])
        ->name('advanced-reports.movements.excel');

    Route::get('/relatorios-avancados/diplomas', [DiplomaReportController::class, 'index'])
        ->name('advanced-reports.diplomas.index');
    Route::get('/relatorios-avancados/diplomas/pdf', [DiplomaReportController::class, 'pdf'])
        ->name('advanced-reports.diplomas.pdf');

    Route::get('/relatorios-avancados/documentos', [StudentDocumentsController::class, 'index'])
        ->name('advanced-reports.student-documents.index');
    Route::get('/relatorios-avancados/documentos/pdf', [StudentDocumentsController::class, 'pdf'])
        ->name('advanced-reports.student-documents.pdf');

    Route::get('/relatorios-avancados/boletim', [BoletimController::class, 'index'])
        ->name('advanced-reports.boletim.index');
    Route::get('/relatorios-avancados/boletim/pdf', [BoletimController::class, 'pdf'])
        ->name('advanced-reports.boletim.pdf');

    Route::get('/relatorios-avancados/historico', [SchoolHistoryController::class, 'index'])
        ->name('advanced-reports.school-history.index');
    Route::get('/relatorios-avancados/historico/pdf', [SchoolHistoryController::class, 'pdf'])
        ->name('advanced-reports.school-history.pdf');

    Route::get('/relatorios-avancados/vagas-turmas', [VacanciesBySchoolClassController::class, 'index'])
        ->name('advanced-reports.vacancies.index');
    Route::get('/relatorios-avancados/vagas-turmas/pdf', [VacanciesBySchoolClassController::class, 'pdf'])
        ->name('advanced-reports.vacancies.pdf');
    Route::get('/relatorios-avancados/vagas-turmas/excel', [VacanciesBySchoolClassController::class, 'excel'])
        ->name('advanced-reports.vacancies.excel');

    Route::get('/relatorios-avancados/atas', [MinutesController::class, 'index'])
        ->name('advanced-reports.minutes.index');
    Route::get('/relatorios-avancados/atas/pdf', [MinutesController::class, 'pdf'])
        ->name('advanced-reports.minutes.pdf');

    // Placeholders pedagógicos / atas adicionais (roadmap)
    Route::get('/relatorios-avancados/pedagogico/{slug}', [PedagogicalController::class, 'show'])
        ->where('slug', '(mapa-notas|mapa-frequencia|espelho-diario|pendencias-lancamento|ata-conselho|ata-entrega-resultados)')
        ->name('advanced-reports.pedagogical.placeholder');

    // Pendências de lançamento (notas/frequência) — primeira entrega do bloco pedagógico
    Route::get('/relatorios-avancados/pendencias-lancamento', [PendingEntriesController::class, 'index'])
        ->name('advanced-reports.pending-entries.index');
    Route::get('/relatorios-avancados/pendencias-lancamento/pdf', [PendingEntriesController::class, 'pdf'])
        ->name('advanced-reports.pending-entries.pdf');
    Route::get('/relatorios-avancados/pendencias-lancamento/excel', [PendingEntriesController::class, 'excel'])
        ->name('advanced-reports.pending-entries.excel');

    Route::get('/relatorios-avancados/alunos-por-situacao', [StudentsBySituationController::class, 'index'])
        ->name('advanced-reports.students-by-situation.index');
    Route::get('/relatorios-avancados/alunos-por-situacao/pdf', [StudentsBySituationController::class, 'pdf'])
        ->name('advanced-reports.students-by-situation.pdf');
    Route::get('/relatorios-avancados/alunos-por-situacao/excel', [StudentsBySituationController::class, 'excel'])
        ->name('advanced-reports.students-by-situation.excel');

    Route::get('/relatorios-avancados/auditoria/acessos-acoes', [AuditUsersReportController::class, 'index'])
        ->name('advanced-reports.audit.users.index');
    Route::get('/relatorios-avancados/auditoria/acessos-acoes/pdf', [AuditUsersReportController::class, 'pdf'])
        ->name('advanced-reports.audit.users.pdf');
    Route::get('/relatorios-avancados/auditoria/acessos-acoes/excel', [AuditUsersReportController::class, 'excel'])
        ->name('advanced-reports.audit.users.excel');
});

