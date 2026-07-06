<?php

namespace App\Console\Commands;

use Minishlink\WebPush\VAPID;
use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid
        {--show : Show existing keys from config instead of generating new ones}
        {--set-env : Output keys as .env variable lines}';

    protected $description = 'Generate VAPID keys for Web Push';

    public function handle(): int
    {
        if ($this->option('show')) {
            $publicKey = config('web-push.vapid.public_key');
            $privateKey = config('web-push.vapid.private_key');

            if (empty($publicKey) || empty($privateKey)) {
                $this->warn('No VAPID keys are configured yet. Run without --show to generate.');
                return Command::FAILURE;
            }

            $this->info('VAPID Public Key:  ' . $publicKey);
            $this->info('VAPID Private Key: ' . $privateKey);

            return Command::SUCCESS;
        }

        $keys = VAPID::createVapidKeys();

        if ($this->option('set-env')) {
            $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
            $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        } else {
            $this->info('VAPID Public Key:  ' . $keys['publicKey']);
            $this->info('VAPID Private Key: ' . $keys['privateKey']);
            $this->newLine();
            $this->warn('Add these keys to your .env file:');
            $this->line('VAPID_PUBLIC_KEY=' . $keys['publicKey']);
            $this->line('VAPID_PRIVATE_KEY=' . $keys['privateKey']);
        }

        return Command::SUCCESS;
    }
}
