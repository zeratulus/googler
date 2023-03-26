<?php

namespace Cli;

abstract class CliCommand extends CliBase implements CliCommandInterface
{
    const COMMAND_NAME = '';

    protected array $requiredArgs = [];
    protected array $checkedArgs = [];

    public function init(): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '4G');

        $this->getArgs();

        if ($this->isValidRequiredArgs()) {
            $this->execute();
        } else {
            if (isFrameworkDebug()) {
                $this->logToConsole("Missing of required arguments!", $this->requiredArgs);
                $this->logToConsole("Pass check: ", $this->checkedArgs);
            }
        }
    }

    private function isValidRequiredArgs(): bool
    {
        $args = $this->arguments;

        foreach ($this->requiredArgs as $required) {
            foreach ($args as $key => $argument) {
                if (str_starts_with($argument, $required)) {
                    $this->checkedArgs[$required] = $argument;
                    unset($args[$key]);
                    break;
                }
            }
        }

        return count($this->checkedArgs) == count($this->requiredArgs);
    }

}