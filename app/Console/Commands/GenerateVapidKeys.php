<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'ayah:vapid';

    protected $description = 'Generate the VAPID key pair used to sign daily-ayah push notifications';

    public function handle(): int
    {
        $keys = VAPID::createVapidKeys();

        $this->line('Add these to your .env (keep the private key secret, and set them in Laravel Cloud too):');
        $this->newLine();
        $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);
        $this->line('VAPID_SUBJECT=mailto:you@example.com');

        return self::SUCCESS;
    }
}
