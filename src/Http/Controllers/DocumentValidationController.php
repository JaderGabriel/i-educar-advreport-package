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

            if (!empty($doc->mac) && $doc->issued_at !== null) {
                $signing = app(DocumentSigningService::class);
                $isValid = $signing->verifyStoredDocument(
                    (string) $doc->mac,
                    (string) $doc->code,
                    (string) $doc->type,
                    $doc->issued_at,
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

