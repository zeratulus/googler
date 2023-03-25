<?php

namespace Cli;

class CliCommandBus extends CliBase
{
    private bool $isCli = false;
    private bool $isInited = false;
    private array $registered = [];
    private string $command = '';

    protected array $requiredArgs = [];

    private function registerCommands(): void
    {
        $path = DIR_SYSTEM . 'CliCommands/*.php';
        $this->logToConsole("Search Commands in: {$path}");
        $files = glob($path);
        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $className = 'CliCommands\\' . $filename;
            $reflection = new \ReflectionClass($className);
            if (!empty($commandName = $reflection->getConstant('COMMAND_NAME')) &&
                $reflection->implementsInterface('\Cli\CliCommandInterface')
            ) {
                $this->registered[$commandName] = $className;
            }
        }
        $this->logToConsole('Registered commands:', $this->registered);
    }

    public function init(): void
    {
        $this->logToConsole('CliCommandBus->init();');
        $this->registerCommands();
        $this->isCli = isCommandLineInterface();
        $this->getArgs();
        foreach ($this->arguments as $command) {
            $command = trim($command);
            $this->logToConsole("Try find command --> {$command}");
            $commands = array_keys($this->registered);
            $idx = array_search($command, $commands);
            if ($idx !== false) {
                $this->command = $command;
                $this->logToConsole("Command found, execute --> {$command}");
                $this->isInited = true;
                $this->execute();
                break;
            }
        }
        $this->logToConsole('End of CliCommandBus->init();');
    }

    private function execute()
    {
        if ($this->isCli && $this->isInited) {
            (new $this->registered[$this->command]())->init();
        }
    }

}