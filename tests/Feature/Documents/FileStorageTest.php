<?php

declare(strict_types=1);

use App\Domain\Documents\Services\FileStorage;
use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('r2');
});

// Phase 0 "Done when": a file can be stored to and read from R2 through the Documents module.
it('stores a file in R2 and reads it back', function () {
    $user = User::factory()->create();
    $upload = UploadedFile::fake()->createWithContent('receipt.pdf', '%PDF-1.4 test receipt');

    $stored = app(FileStorage::class)->store($upload, 'RECEIPT', $user);

    Storage::disk('r2')->assertExists($stored->path);

    expect(app(FileStorage::class)->contents($stored))->toBe('%PDF-1.4 test receipt')
        ->and($stored->original_name)->toBe('receipt.pdf')
        ->and($stored->size_bytes)->toBe(strlen('%PDF-1.4 test receipt'))
        ->and($stored->sha256)->toBe(hash('sha256', '%PDF-1.4 test receipt'))
        ->and($stored->disk)->toBe('r2')
        ->and($stored->purpose)->toBe('RECEIPT')
        ->and($stored->uploadedBy->is($user))->toBeTrue();
});

it('never reuses a path, so a replacement is a new file', function () {
    $storage = app(FileStorage::class);

    $first = $storage->storeContents('v1', 'label.png', 'LABEL');
    $second = $storage->storeContents('v2', 'label.png', 'LABEL');

    expect($first->path)->not->toBe($second->path)
        ->and($storage->contents($first))->toBe('v1')
        ->and($storage->contents($second))->toBe('v2');
});

it('uses a random key that does not contain the original file name', function () {
    $stored = app(FileStorage::class)->storeContents('x', 'MD national ID.pdf', 'COMPANY_DOCUMENT');

    expect($stored->path)->toStartWith('company-document/')
        ->and($stored->path)->not->toContain('national');
});

it('keeps stored file rows immutable', function () {
    $stored = app(FileStorage::class)->storeContents('x', 'a.pdf', 'RECEIPT');

    expect(fn () => $stored->update(['original_name' => 'b.pdf']))->toThrow(LogicException::class)
        ->and(fn () => $stored->delete())->toThrow(LogicException::class)
        ->and(StoredFile::query()->count())->toBe(1);
});

it('refuses to return a file whose contents were changed', function () {
    $stored = app(FileStorage::class)->storeContents('original', 'a.pdf', 'RECEIPT');
    Storage::disk('r2')->put($stored->path, 'tampered');

    expect(fn () => app(FileStorage::class)->contents($stored))->toThrow(LogicException::class);
});

it('issues signed download URLs that expire after 5 minutes', function () {
    $stored = app(FileStorage::class)->storeContents('x', 'a.pdf', 'RECEIPT');
    $expiries = [];
    Storage::disk('r2')->buildTemporaryUrlsUsing(function (string $path, DateTimeInterface $expiration) use (&$expiries) {
        $expiries[] = $expiration;

        return "https://r2.test/{$path}?signed";
    });

    $this->freezeTime();

    expect(app(FileStorage::class)->temporaryUrl($stored))->toBe("https://r2.test/{$stored->path}?signed")
        ->and($expiries[0]->getTimestamp())->toBe(now()->addMinutes(5)->getTimestamp());
});

it('does not serve the local disk over HTTP', function () {
    expect(collect(Route::getRoutes()->getRoutes())->map->uri())->not->toContain('storage/{path}');
});
