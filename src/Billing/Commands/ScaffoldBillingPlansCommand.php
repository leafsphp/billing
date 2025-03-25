<?php

namespace Leaf\Billing\Commands;

use Aloe\Command;

class ScaffoldBillingPlansCommand extends Command
{
    protected static $defaultName = 'scaffold:billing-plans';
    public $description = 'Scaffold billing plans for subscriptions';

    public $help = "This command will scaffold billing plans for your application. You can choose between different scaffolds like default, react, vue, svelte etc.";

    protected function config()
    {
        $this
            ->setOption('scaffold', 's', 'optional', 'Which scaffold to use for authentication (default/react/vue/svelte)', 'default');
    }

    protected function handle()
    {
        $directory = getcwd();
        $scaffold = $this->option('scaffold');
        $type = billing()->providerName();

        $this->info("Creating BillingPlansController for $type");

        \Aloe\Installer::magicCopy(__DIR__ . "/themes/subscriptions/$type");

        if (\Leaf\FS\File::exists("$directory/app/views/_inertia.blade.php")) {
            $content = \Leaf\FS\File::read("$directory/app/views/_inertia.blade.php");

            if (strpos($content, '.jsx') !== false) {
                $scaffold = 'react';
            } else if (strpos($content, '.svelte') !== false) {
                $scaffold = 'svelte';
            } else if (strpos($content, '.vue') !== false) {
                $scaffold = 'vue';
            }
        }

        \Aloe\Installer::magicCopy(__DIR__ . "/themes/$scaffold");

        $this->info('Billing webhooks created successfully');

        return 0;
    }
}
