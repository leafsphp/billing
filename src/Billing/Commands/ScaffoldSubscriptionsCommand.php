<?php

namespace Leaf\Billing\Commands;

use Leaf\Sprout\Command;
use Symfony\Component\Yaml\Yaml;

class ScaffoldSubscriptionsCommand extends Command
{
    protected $signature = 'scaffold:subscriptions
        {--scaffold=default : Which scaffold to use for subscriptions (default/react/vue/svelte)}';
    protected $description = 'Scaffold billing plans for subscriptions';

    protected $help = "This command will scaffold billing plans for your application. You can choose between different scaffolds like default, react, vue, svelte etc.";

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

        \Leaf\FS\Directory::copy(
            __DIR__ . "/themes/subscriptions/$type",
            getcwd(),
            ['recursive' => true]
        );

        \Leaf\FS\Directory::copy(
            __DIR__ . "/themes/$scaffold",
            getcwd(),
            ['recursive' => true]
        );

        $this->info('Scaffold generated successfully, running subscription schemas...');

        sprout()->process('php leaf db:migrate subscriptions')->run();

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

        sprout()->process('php leaf db:migrate users')->run();

        return 0;
    }
}
