<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Lists Somali entries whose text is still identical to the English
 * placeholder (D-154). Zero untranslated entries is a go-live requirement;
 * the Phase 5 go-live check runs this with --fail.
 */
class TranslationsReportCommand extends Command
{
    protected $signature = 'translations:report {--fail : Exit with an error when any Somali entry is untranslated}';

    protected $description = 'List Somali translations that still equal the English text (D-154)';

    public function handle(): int
    {
        $english = $this->lines('en');
        $somali = $this->lines('so');

        $untranslated = array_keys(array_filter(
            $english,
            fn (string $text, string $key): bool => ($somali[$key] ?? $text) === $text,
            ARRAY_FILTER_USE_BOTH,
        ));

        foreach ($untranslated as $key) {
            $this->line("  {$key}");
        }

        $count = count($untranslated);
        $total = count($english);
        $this->info("{$count} of {$total} Somali entries still equal the English text.");

        return $count > 0 && $this->option('fail') ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    private function lines(string $locale): array
    {
        /** @var array<string, string> $lines */
        $lines = json_decode(File::get(lang_path("{$locale}.json")), true, flags: JSON_THROW_ON_ERROR);

        return $lines;
    }
}
