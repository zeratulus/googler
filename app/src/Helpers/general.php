<?php

function nowMySQLTimestamp(): string
{
    return date('Y-m-d H:i:s');
}

function isFrameworkDebug(): bool
{
    return defined('DEV') && constant('DEV');
}

function isCommandLineInterface(): bool
{
    return php_sapi_name() === 'cli';
}

function getImageExtensions(): array
{
    return array('png', 'jpg', 'jpeg', 'gif', 'webp');
}

function getImageMimeTypes(): array
{
    return array(
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
        'image/gif',
        'image/webp'
    );
}
