<?php

namespace Cli;

interface CliCommandInterface
{
    public function execute(): void;
}