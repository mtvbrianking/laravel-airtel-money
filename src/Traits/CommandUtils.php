<?php

namespace Bmatovu\AirtelMoney\Traits;

trait CommandUtils
{
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result += $this->flattenArray($value, $fullKey);
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }

    protected function persistConfig(string $configKey, ?string $value): void
    {
        $this->laravel->make('config')->set([$configKey => $value]);

        if ($this->option('no-write')) {
            return;
        }

        $envPath = app()->basePath('.env');

        $envKey = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $configKey));

        $pattern = '/^'.preg_quote($envKey, '/').'=["\']?.*/m';

        $contents = file_get_contents($envPath);

        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, "{$envKey}=\"{$value}\"", $contents);
        } else {
            $contents .= PHP_EOL."{$envKey}=\"{$value}\"";
        }

        file_put_contents($envPath, $contents);
    }

    protected function writeConfig(
        string $key,
        ?string $value = null,
        string $config = 'airtel-money'
    ): ?string {
        $configKey = "{$config}.{$key}";

        $oldValue = $value ?? $this->laravel->make('config')->get($configKey);

        $val = $this->ask($key, $oldValue);

        $this->persistConfig($configKey, $val);

        return $val;
    }
}
