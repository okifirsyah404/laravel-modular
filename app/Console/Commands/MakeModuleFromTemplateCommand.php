<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Str;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

#[Signature('make:module-template')]
#[Description('Create starter CRUD module from stubs/module-template')]
class MakeModuleFromTemplateCommand extends Command
{

    public function handle(): int
    {
        $this->container['name'] = ucwords($this->ask('Please enter the name of the Module'));

        if (strlen($this->container['name']) === 0) {
            $this->error("\nModule name cannot be empty.");

            return self::FAILURE;
        }

        $this->comment('You have provided the following information:');
        $this->comment('Name: ' . $this->container['name']);

        if (! $this->confirm('Do you wish to continue?', true)) {
            return self::FAILURE;
        }

        if (! $this->generate()) {
            return self::FAILURE;
        }

        $this->updateModuleStatus();
        $this->dumpAutoload();

        $this->info('Starter ' . $this->container['name'] . ' module installed successfully.');
        $this->line('Next: run `php artisan module:make-migration create_' . Str::snake($this->container['name']) . '_table ' . $this->container['name'] . '` to add your first migration.');
        $this->warn('Heads up: the controller renders pages under `{ModuleName}/Index`, but the template ships pages flat under `resources/js/pages/`. Move them into a `{ModuleName}/` subdir, or change the Inertia::render() calls, before the routes will resolve in the browser.');

        return self::SUCCESS;
    }

    protected function generate(): bool
    {
        $name = $this->container['name'];
        $targetPath = base_path('Modules/' . $name);
        $templatePath = base_path('stubs/module-template');

        $filesystem = new SymfonyFilesystem;
        if (! $filesystem->exists($templatePath)) {
            $this->error("Template not found at {$templatePath}.");

            return false;
        }

        $filesystem->mirror($templatePath, $targetPath);

        // Walk the freshly-copied tree and rewrite every .stub file: substitute
        // placeholders in both contents and filename, then strip the .stub suffix.
        foreach (Finder::create()->files()->in($targetPath) as $file) {
            if (str_ends_with($file->getFilename(), '.stub')) {
                $this->processStub($file->getPathname(), $file->getRelativePathname());
            }
        }

        return true;
    }

    protected function updateModuleStatus(): void
    {
        $statusesPath = config('modules.activators.file.statuses-file', base_path('modules_statuses.json'));
        $moduleName = $this->container['name'];

        $statuses = [];

        if (is_file($statusesPath)) {
            $decoded = json_decode((string) file_get_contents($statusesPath), true);

            if (is_array($decoded)) {
                $statuses = $decoded;
            }
        }

        $statuses[$moduleName] = true;
        ksort($statuses);

        $encoded = json_encode($statuses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($encoded === false || file_put_contents($statusesPath, $encoded . PHP_EOL) === false) {
            $this->warn('Unable to update module status file at ' . $statusesPath . '. Enable the module manually if needed.');
        }
    }

    /**
     * Replace placeholders inside a .stub file, rename it to drop the .stub suffix,
     * and rewrite filenames that contain `ModuleTemplate` (e.g.
     * `ModuleTemplateServiceProvider.php.stub` -> `{Name}ServiceProvider.php`).
     */
    protected function processStub(string $absPath, string $relativePath): void
    {
        $contents = file_get_contents($absPath);
        $contents = $this->replacePlaceholders($contents);

        $newRelative = $this->replacePlaceholders($relativePath);
        if (str_ends_with($newRelative, '.stub')) {
            $newRelative = substr($newRelative, 0, -5);
        }

        $newAbs = base_path('Modules/' . $this->container['name']) . '/' . $newRelative;

        $filesystem = new SymfonyFilesystem;
        $filesystem->mkdir(dirname($newAbs));
        $filesystem->dumpFile($newAbs, $contents);

        // Safely remove the original .stub file if the target location changed
        if ($absPath !== $newAbs) {
            $filesystem->remove($absPath);
        }
    }
    /**
     * Replace every placeholder token from module-template.md inside the given string.
     */
    protected function replacePlaceholders(string $value): string
    {
        $name = $this->container['name'];

        $map = [
            '{module_template}' => Str::snake($name),
            '{module-template}' => Str::kebab($name),
            '{ModuleTemplate}' => $name,
            '{moduletemplate}' => Str::lower($name),
            '{moduleTemplate}' => lcfirst($name),
        ];

        return str_replace(array_keys($map), array_values($map), $value);
    }

    protected function dumpAutoload(): void
    {
        $base = base_path();
        $command = 'composer dump-autoload --working-dir=' . escapeshellarg($base) . ' 2>&1';

        $this->line('Running `composer dump-autoload`...');

        $output = [];
        $exit = 0;
        exec($command, $output, $exit);

        foreach ($output as $line) {
            $this->line($line);
        }

        if ($exit !== 0) {
            $this->warn('`composer dump-autoload` did not exit cleanly (code ' . $exit . '). The module files are in place; rerun dump-autoload manually if classes cannot be resolved.');
        }
    }
}
