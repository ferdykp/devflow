<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProductionPreflight extends Command
{
    protected $signature = 'devflow:preflight {--strict : Exit with failure if any required production check fails}';

    protected $description = 'Check the DevFlow release environment without exposing credentials';

    public function handle(): int
    {
        $production = app()->environment('production');
        $failed = 0;
        $warnings = 0;

        $check = function (string $label, bool $ok, bool $required = true) use (&$failed, &$warnings): void {
            $this->line(($ok ? '[PASS] ' : ($required ? '[FAIL] ' : '[WARN] ')).$label);
            if (! $ok) {
                $required ? $failed++ : $warnings++;
            }
        };

        $this->components->info('DevFlow deployment preflight (credentials are never displayed)');
        $check('Environment is production', $production, true);
        $check('Debug mode is disabled', config('app.debug') === false);
        $appKey = (string) config('app.key');
        $keyBytes = str_starts_with($appKey, 'base64:')
            ? base64_decode(substr($appKey, 7), true)
            : $appKey;
        $validKey = is_string($keyBytes) && in_array(strlen($keyBytes), [16, 24, 32], true);
        $check('Application key has a valid encryption length', $validKey);

        $appUrl = (string) config('app.url');
        $urlParts = parse_url($appUrl);
        $validHttps = is_array($urlParts)
            && ($urlParts['scheme'] ?? null) === 'https'
            && filled($urlParts['host'] ?? null)
            && ! in_array(strtolower((string) ($urlParts['host'] ?? '')), ['localhost', '127.0.0.1', '0.0.0.0'], true);
        $check('Application URL is public HTTPS', $validHttps);
        $check('Vite manifest exists', is_file(public_path('build/manifest.json')));
        $check('Session storage is server-side and persistent', in_array(config('session.driver'), ['database', 'redis', 'file'], true));
        $check('Queue is asynchronous', in_array(config('queue.default'), ['database', 'redis', 'sqs', 'beanstalkd'], true));
        $check('Cache uses persistent storage', in_array(config('cache.default'), ['database', 'redis', 'file', 'memcached', 'dynamodb'], true));
        $check('Application logging is not debug level', config('logging.channels.'.config('logging.default').'.level', env('LOG_LEVEL', 'debug')) !== 'debug', false);
        $check('Webhook secret is configured', filled(config('services.github.webhook_secret')), false);

        try {
            DB::connection()->getPdo();
            $check('Database connection succeeds', true);
        } catch (Throwable $e) {
            $check('Database connection succeeds', false);
        }

        $this->line("Checks: {$failed} required failure(s), {$warnings} warning(s).");
        $this->warn('This command cannot verify TLS termination, worker supervision, backups, provider integrations or browser accessibility.');

        return ($failed > 0 && $this->option('strict')) ? self::FAILURE : self::SUCCESS;
    }
}
