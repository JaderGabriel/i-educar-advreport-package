<?php

namespace iEducar\Packages\AdvancedReports\Http\Controllers;

use App\Http\Controllers\Controller;
use iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument;
use iEducar\Packages\AdvancedReports\Services\DocumentSigningService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentValidationController extends Controller
{
    public function show(Request $request, string $code): View
    {
        $doc = AdvancedReportsDocument::query()->where('code', $code)->first();
        $isValid = false;
        $summary = [];

        if ($doc) {
            $payload = is_array($doc->payload) ? $doc->payload : [];
            $issuedAtIso = optional($doc->issued_at)->toISOString() ?? '';

            if (!empty($doc->mac) && $issuedAtIso !== '') {
                $isValid = app(DocumentSigningService::class)->verify(
                    (string) $doc->mac,
                    (string) $doc->code,
                    (string) $doc->type,
                    $issuedAtIso,
                    $payload
                );
            }

            $summary = $doc->publicSummary();
        }

        return view('advanced-reports::documents.validate', [
            'code' => $code,
            'doc' => $doc,
            'isValid' => $isValid,
            'summary' => $summary,
        ]);
    }
}

