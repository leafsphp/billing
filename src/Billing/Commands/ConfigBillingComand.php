<?php

namespace Leaf\Billing\Commands;

use Aloe\Command;
use Symfony\Component\Yaml\Yaml;

class ConfigBillingComand extends Command
{
    protected static $defaultName = 'config:billing';
    public $description = 'Scaffold billing plans on payment provider';

    public $help = 'This command will scaffold billing plans on the payment provider. It will also cache your plans in storage/billing';

    protected function handle()
    {
        $this->writeln('Publishing billing tiers on provider ...');

        try {
            billing()->initialize();
            $this->comment('Billing plans published on provider');
        } catch (\Throwable $th) {
            $this->error('Error publishing billing plans on provider');
            $this->error($th->getMessage());
            return 1;
        }

        return 0;
    }
}
