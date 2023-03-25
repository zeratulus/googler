<?php

require_once 'constants.php';
require_once './../vendor/autoload.php';
require_once 'src/Helpers/general.php';

if (isCommandLineInterface()) {
    (new Cli\CliCommandBus)->init();
} else {
    echo 'Only CLI commands available.';
}