<?php

namespace CliCommands;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
/**
 * Class GetImages
 * php index.php --get-images
 * -count - images to parse
 * -page - page to start from
 * @package CliCommands
 */
class GetImages extends \Cli\CliCommand
{
    const COMMAND_NAME = '--get-images';

    private string $serverUrl = 'http://localhost:4444';
    private array $queries = [];

    private function getSearchQueries()
    {
        $data = file_get_contents(FILE_DATA);
        $this->queries = explode(',', $data);
    }

    public function execute(): void
    {
        $count = $this->getArgumentValue('-count');
        if (!$count) {
            $count = 500;
        }

        $page = $this->getArgumentValue('-page');
        if (!$page) {
            $page = 1;
        }


        $this->getSearchQueries();

        // Chrome
        $driver = RemoteWebDriver::create($this->serverUrl, DesiredCapabilities::chrome());

        foreach ($this->queries as $query) {
            $url = "https://www.google.com/search?q={$query}&tbs=isz:l&sa=X&tbm=isch";


            //UserAgent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Safari/537.36
            $image = file_get_contents($url);
            $filename = pathinfo($url, PATHINFO_FILENAME);
            $fileext = pathinfo($url, PATHINFO_EXTENSION);

            file_put_contents(DIR_UPLOAD . "$query/" . uniqid($query,  true));
        }
    }

}