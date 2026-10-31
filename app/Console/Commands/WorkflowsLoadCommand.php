<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Workflow\Definitions\DefinitionLoader;
use App\Domain\Workflow\Exceptions\InvalidWorkflowDefinition;
use Illuminate\Console\Command;

/**
 * Loads every workflow definition in config/workflows into the workflow
 * tables (D-071, D-097). A loaded version is never changed: identical
 * content is skipped and different content for the same version is
 * refused (D-189). Safe to run on every deploy.
 */
final class WorkflowsLoadCommand extends Command
{
    protected $signature = 'workflows:load {--path= : Folder with the definition files (default: config/workflows)}';

    protected $description = 'Validate and load the workflow definitions';

    public function handle(DefinitionLoader $loader): int
    {
        $path = (string) ($this->option('path') ?: config_path('workflows'));
        $files = glob(rtrim($path, '/\\').DIRECTORY_SEPARATOR.'*.php') ?: [];
        sort($files);

        if ($files === []) {
            $this->error("No workflow definitions found in {$path}.");

            return self::FAILURE;
        }

        $failed = false;

        foreach ($files as $file) {
            $definition = require $file;
            $name = basename($file);

            if (! is_array($definition)) {
                $this->error("{$name}: the file must return an array.");
                $failed = true;

                continue;
            }

            try {
                $result = $loader->load($definition);
                $this->line(sprintf('%s: %s v%s %s.', $name, $definition['code'], $definition['version'], $result));
            } catch (InvalidWorkflowDefinition $exception) {
                $this->error("{$name}: ".$exception->getMessage());
                $failed = true;
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
