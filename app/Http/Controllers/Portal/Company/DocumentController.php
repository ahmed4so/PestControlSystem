<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Company;

use App\Domain\CompanyRegistry\Actions\UploadDocument;
use App\Domain\CompanyRegistry\Enums\DocumentType;
use App\Domain\Documents\Exceptions\UnsafeUpload;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\Company\DocumentRequest;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Document;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Company documents with history (02 §4.4, 05 §4.4, D-067). Renewed
 * commerce and tax certificates can be uploaded even when the profile is
 * locked (D-073).
 */
class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $this->company($request);
        $documents = $company->documents()->with('person:id,full_name')->latest('id')->get();

        return Inertia::render('portal/documents/index', [
            'types' => collect(DocumentType::cases())
                ->reject(fn (DocumentType $type) => $type->belongsToPerson())
                ->map(fn (DocumentType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                    'isCertificate' => $type->isCertificate(),
                    'canUpload' => Gate::allows('create', [Document::class, $company, $type]),
                    'current' => $this->present($documents->first(fn (Document $d) => $d->document_type === $type && $d->isCurrent())),
                    'history' => $documents->filter(fn (Document $d) => $d->document_type === $type && ! $d->isCurrent())
                        ->map(fn (Document $d) => $this->present($d))->values(),
                ])->values(),
        ]);
    }

    public function store(DocumentRequest $request, UploadDocument $upload): RedirectResponse
    {
        $company = $this->company($request);
        $type = $request->documentType();
        Gate::authorize('create', [Document::class, $company, $type]);

        /** @var CompanyUser $account */
        $account = $request->user();
        $person = $request->filled('person_id') ? Person::query()->findOrFail($request->integer('person_id')) : null;

        try {
            $upload($company, $type, $request->file('file'), $request->safe()->only(['document_number', 'issued_on', 'expires_on']), $account, $person);
        } catch (UnsafeUpload $exception) {
            throw ValidationException::withMessages(['file' => __($exception->getMessage())]);
        }

        return back()->with('success', __('Document uploaded.'));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function present(?Document $document): ?array
    {
        if ($document === null) {
            return null;
        }

        return [
            'id' => $document->id,
            'document_number' => $document->document_number,
            'issued_on' => $document->issued_on?->toDateString(),
            'expires_on' => $document->expires_on?->toDateString(),
            'uploaded_at' => $document->created_at?->toIso8601String(),
            'url' => route('portal.files.document', $document),
        ];
    }

    private function company(Request $request): Company
    {
        /** @var CompanyUser $account */
        $account = $request->user();

        return Company::query()->findOrFail($account->company_id);
    }
}
