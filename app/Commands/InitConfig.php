<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Spatie\Regex\Regex;

class InitConfig extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->title("Welcome to the .env Wizard!");
        $this->info('You now will be asked for all the .env Variable values.');
        $this->warn('you can enter "null" if you don\'t want to set a value');
        $this->line('');

        $variables = $this->askVariables();
        $this->writeVariables($variables);
    }

    private function writeVariables($variables)
    {
        if (file_exists(base_path('.env')) && !$this->confirm('a .env file does already exist, do you want to overwrite it?')) {
            return;
        }

        $contents = $this->getExampleEnv();
        foreach ($variables as $variable => $value) {
            $value = $this->sanitizeValue($value);
            $contents = Regex::replace("/^{$variable}=.*/m", $variable.'='.$value, $contents)->result();
        }

        file_put_contents(base_path('.env'), $contents);
    }

    private function askVariables()
    {
        $valueGroups = $this->parseEnv();
        $variables = [];
        foreach ($valueGroups as $group) {
            $this->info('Enter values for group');
            $this->info("({$group['name']})");
            foreach ($group['values'] as $variable => $config) {
                $variables[$variable] = $this->askVariable($variable, $config);
            }
        }

        return $variables;
    }

    private function askVariable($variable, $config)
    {
        $result = null;
        $question = "{$variable}";
        do {
            $result = $this->ask($question, $config['default']);
        } while (!$result);

        if ($result === 'null') {
            $result = null;
        }

        return $result;
    }

    private function parseEnv()
    {
        $contents = $this->getExampleEnv();
        $lines = explode("\n", $contents);
        $groups = [];
        foreach ($lines as $line) {
            if (!$line) {
                continue;
            }

            if (starts_with($line, '# ')) {
                $groups[] = [
                    'name' => str_replace('# ', '', $line),
                ];
            } else {
                $lastGroup = array_last($groups);
                $atIndex = array_search($lastGroup, $groups);
                $lastGroup['values'] = !isset($lastGroup['values']) ? [] : $lastGroup['values'];
                [$full, $key, $value] = Regex::match("/([A-Z0-9_]+)=(.*)/", $line)->groups();

                $lastGroup['values'][$key] = ['default' => $value];
                $groups[$atIndex] = $lastGroup;
            }
        }

        return $groups;
    }

    private function getExampleEnv()
    {
        $envPath = base_path('.env.example');
        $contents = file_get_contents($envPath);
        return $contents;
    }

    private function sanitizeValue($value)
    {
        $value = str_replace('$', '\\\\$', $value);
        if (Regex::match('/ /', $value)->hasMatch()) {
            return "\"{$value}\"";
        }

        return $value;
    }
}
