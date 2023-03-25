<?php

use Monolog\Logger;

class Log extends Logger
{
    /**
     * @param string $message
     * @param array $data
     */
    public function write(string $message, array $data = [])
    {
        $this->error($message, $data);
    }

}