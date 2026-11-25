<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Relatórios Avançados
    |--------------------------------------------------------------------------
    */

    'enabled' => true,

    /*
    | Formatos nativos do pacote vs. formatos típicos de edital.
    | PDF/XLSX são gerados aqui; os demais podem ser complementados pela
    | exportação unificada (buriti/i-educar-exportacao-unificada-package).
    */
    'formatos_nativos' => ['PDF', 'XLSX', 'HTML'],

    'formatos_edital_referencia' => [
        'PDF', 'ODT', 'DOCX', 'ODS', 'XLSX', 'HTML', 'XML', 'CSV', 'TXT', 'JPEG',
    ],

    'validacao_publica' => [
        'rota' => '/documentos/validar/{code}',
        'qr' => true,
    ],
];
