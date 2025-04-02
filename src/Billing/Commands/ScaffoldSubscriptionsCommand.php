<?php

namespace Leaf\Billing\Commands;

use Aloe\Command;
use Symfony\Component\Yaml\Yaml;

class ScaffoldSubscriptionsCommand extends Command
{
    protected static $defaultName = 'scaffold:subscriptions';
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

        $this->info("Scaffolding subscriptions using $scaffold + $type");

        \Aloe\Installer::magicCopy(__DIR__ . "/themes/subscriptions/$type");
        \Aloe\Installer::magicCopy(__DIR__ . "/themes/$scaffold");

        $this->info('Scaffold generated successfully, running subscription schemas...');

        \Aloe\Core::run('php leaf db:migrate subscriptions', $this->output());

        if (\Leaf\FS\File::exists($usersSchema = "$directory/app/database/users.yml")) {
            $data = Yaml::parseFile($usersSchema);
            $columns = $data['columns'] ?? [];

            if (!isset($columns['billing_id'])) {
                $data['columns']['billing_id'] = [
                    'type' => 'string',
                    'nullable' => true,
                ];

                \Leaf\FS\File::write($usersSchema, Yaml::dump($data, 4, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
            }
        }

        \Aloe\Core::run('php leaf db:migrate users', $this->output());

        return 0;
    }
}
