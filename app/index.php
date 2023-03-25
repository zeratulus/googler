<?php

require_once 'constans.php';
require_once 'src/Helpers/general.php';
require_once 'vendor/autoload.php';

if (isCommandLineInterface()) {
    (new Cli\CliCommandBus)->init();
} else {
    echo 'Only CLI commands available.';
}