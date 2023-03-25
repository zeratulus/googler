<?php

namespace CliCommands;

use Facebook\WebDriver\Exception\Internal\UnexpectedResponseException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use SebastianBergmann\Diff\Exception;

/**
 * Class GetImages
 * php index.php --get-images
 * PS. If Google Search UI changed -> need to update selectors
 * @package CliCommands
 */
class GetImages extends \Cli\CliCommand
{
    const COMMAND_NAME = '--get-images';

    private string $serverUrl = 'http://localhost:4444';
    private array $queries = [];

    private array $preloadingEndValues = [
        "Looks like you've reached the end",
        "Схоже, ви переглянули весь вміст"
    ];

    private array $preloadingButtonValues = [
        "Show more results",
        "Показати інші результати"
    ];

    private function getSearchQueries()
    {
        $data = file_get_contents(FILE_DATA);
        $this->queries = explode(',', $data);
    }

    //TODO: Add log of events
    private function processQuery(string $query)
    {
        $this->logToConsole("processQuery($query)");
        // Chrome
        $driver = RemoteWebDriver::create($this->serverUrl, DesiredCapabilities::chrome());

        //https://www.google.com/search?q=T-34&tbs=isz:l&sa=X&tbm=isch
        //get images search page
        $url = "https://www.google.com/search?q={$query}&tbs=isz:l&sa=X&tbm=isch";
        $driver->get($url);
        $this->logToConsole("process $url");
        $loading = true;
        $is_scroll = true;

        //Preload all images on page by search query
        $i=0;
        while ($loading) {
            $this->logToConsole("Preload all images iteration - $i");

            try {
                $btn_more_results = $driver->findElement(WebDriverBy::cssSelector('div input[type=button]'));
                if (in_array($btn_more_results->getAttribute('value'), $this->preloadingButtonValues)) {
                    $btn_more_results->click(); //Exception  throw here
                    sleep('2');
                }
            } catch (UnexpectedResponseException $e) {
                $this->logToConsole($e->getMessage());
            }

            if ($is_scroll) {
                $this->logToConsole("Scroll");
                $driver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
                sleep('2');
            }

            //<div class="OuJzKb Yu2Dnd"><div>Схоже, ви переглянули весь вміст</div></div>
            $el_the_end = $driver->findElement(WebDriverBy::cssSelector('.OuJzKb.Yu2Dnd'));
            if (in_array($el_the_end->getText(), $this->preloadingEndValues)) {
                $this->logToConsole("Preloading end.");
                $loading = false;
                $is_scroll = false;
            }

            $i++;
        }

        //Parse thumbnails > single element selector img.rg_i
        $elements = $driver->findElements(WebDriverBy::cssSelector('img.rg_i'));
        foreach ($elements as $element) {
            $element->click();
            sleep(3);

            //Big image to save
            $target_image = $driver->findElement(WebDriverBy::cssSelector('img.n3VNCb.pT0Scc.KAlRDb'));
            $image_src = $target_image->getAttribute('src');
            $image = file_get_contents($image_src);
//            $filename = pathinfo($image_src, PATHINFO_FILENAME);
            $ext = pathinfo($image_src, PATHINFO_EXTENSION);
            $path = DIR_UPLOAD . $query . '/';
            mkdir($path, 0777, true);

            //uniqid = target filename + extension
            file_put_contents($path . uniqid($query,  true) . '.' . $ext, $image);
        }

        $driver->quit();
    }

    public function execute(): void
    {
//        exec('xfce4-terminal -e "' . DIR_ROOT . '../pre-start.sh"');

        $this->getSearchQueries();

        foreach ($this->queries as $query) {
            $this->processQuery($query);
        }
    }

}