<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Company;

use App\Domain\Documents\Services\FileStorage;
use App\Http\Controllers\Controller;
use App\Models\CompanyUser;
use App\Models\Document;
use App\Models\FacilityChecklistPhoto;
use App\Models\Premises;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Company downloads of its own files (03 §11.3, D-056): ownership is
 * checked first, then the file is served as an attachment.
 */
class FileController extends Controller
{
    public function document(Document $document, FileStorage $files): Response
    {
        Gate::authorize('view', $document);

        return $files->download($document->file);
    }

    public function photo(Request $request, FacilityChecklistPhoto $photo, FileStorage $files): Response
    {
        /** @var CompanyUser $account */
        $account = $request->user();
        $premises = Premises::query()->withTrashed()->find($photo->item->premises_id);

        abort_unless($premises instanceof Premises && $premises->company_id === $account->company_id, 404);

        return $files->download($photo->file);
    }
}
