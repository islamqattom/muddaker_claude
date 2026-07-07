<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class VerifyPushSetup extends Command
{
    protected $signature = 'push:verify';
    protected $description = 'Verify WebPush is initialised and VAPID keys are loaded';

    public function handle(NotificationService $service): int
    {
        $ref = new \ReflectionClass($service);
        $prop = $ref->getProperty('webPush');
        $prop->setAccessible(true);
        $wp = $prop->getValue($service);

        if (!$wp) {
            $this->error('WebPush is NULL — VAPID keys are missing or invalid.');
            $this->line('Check VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY in .env');
            return self::FAILURE;
        }

        $this->info('WebPush initialised: ' . get_class($wp));

        $ref2 = new \ReflectionClass($wp);
        $auth = $ref2->getProperty('auth');
        $auth->setAccessible(true);
        $authVal = $auth->getValue($wp);

        $this->line('  Auth class:        ' . (is_object($authVal) ? get_class($authVal) : gettype($authVal)));
        $this->line('  Default TTL:       ' . ($wp->getDefaultOptions()['TTL'] ?? '(unset)'));

        return self::SUCCESS;
    }
}
