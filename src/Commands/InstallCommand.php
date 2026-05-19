<?php

namespace RobertBoes\FilamentPasskeys\Commands;

use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;

class InstallCommand extends Command
{
    protected $signature = 'filament-passkeys:install
        {--no-migrate : Skip running migrations after publishing}';

    protected $description = 'Install the Filament passkeys plugin: publish migrations and config, run migrations, and print the User model snippet.';

    public function handle(): int
    {
        $this->components->info('Installing filament-passkeys.');

        $this->components->task('Publishing laravel/passkeys migrations', fn () => $this->callSilent('vendor:publish', [
            '--tag' => 'passkeys-migrations',
        ]) === self::SUCCESS);

        $this->components->task('Publishing laravel/passkeys config', fn () => $this->callSilent('vendor:publish', [
            '--tag' => 'passkeys-config',
        ]) === self::SUCCESS);

        if (confirm(label: 'Publish the filament-passkeys config too?', default: false)) {
            $this->components->task('Publishing filament-passkeys config', fn () => $this->callSilent('vendor:publish', [
                '--tag' => 'filament-passkeys-config',
            ]) === self::SUCCESS);
        }

        if (! $this->option('no-migrate')) {
            $this->components->task('Running migrations', fn () => $this->call('migrate', ['--force' => $this->option('no-interaction')]) === self::SUCCESS);
        }

        $this->newLine();
        $this->components->info('Add the trait and contract to your User model:');
        $this->line(<<<'PHP'
            use Laravel\Passkeys\Contracts\PasskeyUser;
            use Laravel\Passkeys\PasskeyAuthenticatable;

            class User extends Authenticatable implements PasskeyUser
            {
                use PasskeyAuthenticatable;

                // ...
            }
            PHP);
        $this->newLine();

        $this->components->info('Register the plugin on your panel with FilamentPasskeysPlugin::make().');
        $this->components->info('Run `php artisan filament:assets` to publish the JS bundle (or it will run automatically on composer install).');

        return self::SUCCESS;
    }
}
