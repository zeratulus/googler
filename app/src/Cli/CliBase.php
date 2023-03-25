<?php

namespace Cli;

class CliBase
{
    protected array $arguments = [];

    protected function getArgumentValue(string $key): string
    {
        $this->logToConsole("Search for <{$key}> in arguments as string: ", $this->arguments);
        foreach ($this->arguments as $argument) {
            if (str_starts_with($argument, $key)) {
                return explode('=', $argument)[1];
            }
        }
        return '';
    }

    protected function getArgumentValueAsBool(string $key): bool
    {
        $this->logToConsole("Search for <{$key}> in arguments as bool: ", $this->arguments);
        foreach ($this->arguments as $argument) {
            if ($argument === $key) {
                return true;
            }
        }
        return false;
    }

    protected function getArgs(): void
    {
        global $argv;
        foreach ($argv as $arg) {
            if ($arg !== 'index.php')
                $this->arguments[] = trim($arg);
        }
    }

    protected function writeToConsole(string $msg): void
    {
        echo nowMySQLTimestamp() . " -> {$msg}\n";
    }

    protected function logToConsole(string $msg, array $context = []): void
    {
        if (isFrameworkDebug()) {
            $this->writeToConsole($msg);
            if (!empty($context)) {
                var_dump($context);
            }
        }
    }
}