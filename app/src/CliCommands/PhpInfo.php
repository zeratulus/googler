<?php

namespace CliCommands;

/**
 * Class PhpInfo
 * php index.php --php-info
 * Shows php_info();
 * @package CliCommands
 */
class PhpInfo extends \Cli\CliCommand
{
    const COMMAND_NAME = '--php-info';

    public function execute(): void
    {
        phpinfo();
    }

}