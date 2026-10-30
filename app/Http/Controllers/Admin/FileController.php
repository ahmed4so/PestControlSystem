<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Audit\Support\AuditLog;
use App\Domain\Documents\Services\FileStorage;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\FacilityChecklistPhoto;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;

/**
 * Staff views and downloads of company files, each written to the audit
 * log (03 §13 "Data access", D-091).
 */
class FileController extends Controller implements HasMiddleware
{
    /**
     * @return list<Middleware>
     */
    public static function middleware(): array
    {
        return [new Middleware('permission:view applications')];
    }

    public function document(Request $request, Document $document, FileStorage $files): Response
    {
        AuditLog::record('company_file_viewed', 'Staff opened a company document', $document, $request->user(), [
            'company_id' => $document->company_id,
            'document_type' => $document->document_type->value,
        ]);

        return $files->download($document->file);
    }

    public function photo(Request $request, FacilityChecklistPhoto $photo, FileStorage $files): Response
    {
        AuditLog::record('company_file_viewed', 'Staff opened a facility photo', $photo, $request->user());

        return $files->download($photo->file);
    }
}
