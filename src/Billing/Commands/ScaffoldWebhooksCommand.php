<?php

namespace Leaf\Billing\Commands;

use Aloe\Command;

class ScaffoldWebhooksCommand extends Command
{
    protected static $defaultName = 'scaffold:billing-webhooks';
    public $description = 'Scaffold billing webhooks';

    public $help = "This command will scaffold billing webhooks for your application. You can choose between different scaffolds like default, react, vue, svelte etc.";

    protected function handle()
    {
        $type = billing()->providerName() ?? 'stripe';

        $this->info("Creating BillingWebhooksController for $type");

        \Aloe\Installer::magicCopy(__DIR__ . "/themes/webhooks/$type");

        $this->info('Billing webhooks created successfully');

        return 0;
    }
}
