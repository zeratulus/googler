<?php

namespace CliCommands;

use Exception;
use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Exception\ElementNotInteractableException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

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
                    sleep(2);
                    $this->logToConsole("Button Load More -> clicked");
                }
            } catch (ElementNotInteractableException $e) {
                $this->logToConsole('Error: ElementNotInteractableException -> ' . $e->getMessage());
            }

            //Scroll to bottom
            if ($is_scroll) {
                $this->logToConsole("Scroll");
                $driver->executeScript('window.scrollTo(0,document.body.scrollHeight);');
                sleep(2);
            }

            //The end of loading
            $is_the_end = false;
            try {
                $el_the_end = $driver->findElement(WebDriverBy::cssSelector('.OuJzKb.Yu2Dnd'));
                if (in_array($el_the_end->getText(), $this->preloadingEndValues)) {
                    $this->logToConsole("Preloading end.");
                    $loading = false;
                    $is_scroll = false;
                    $is_the_end = true;
                }
            } catch (ElementNotInteractableException $e) { //8 div span
                $this->logToConsole("The end not found.");
                $is_the_end = false;
            }

            if (!$is_the_end) {
                try {
                    $el_the_end = $driver->findElement(WebDriverBy::cssSelector('div.WYR1I span'));
                    if (str_contains($el_the_end->getText(), "See more anyway")) {
                        $this->logToConsole("Preloading end.");
                        $loading = false;
                        $is_scroll = false;
                        $is_the_end = true;
                    }
                } catch (ElementNotInteractableException $e) {
                    $this->logToConsole("The end not found.");
                    $is_the_end = false;
                } catch (NoSuchElementException $e) {
                    $this->logToConsole("The end not found.");
                    $is_the_end = false;
                }
            }

            $i++;
        }

        //Parse thumbnails > single element selector img.rg_i
        $elements = $driver->findElements(WebDriverBy::cssSelector('img.rg_i'));
        foreach ($elements as $element) {
            $this->processElement($query, $element, $driver);
        }
        $driver->quit();
    }

    private function processElement($query, $element, $driver)
    {
        $parent = $element->findElement(WebDriverBy::xpath("./../.."));
        $parent->click();
        sleep(2);

        //Here is on Google Search 3  images: prev thumb, current (big image), next thumb
        $target_images = $driver->findElements(WebDriverBy::cssSelector('c-wiz div div div div div a[role="link"] img'));
        $this->logToConsole('Target Images');
        $path = DIR_UPLOAD . $query . '/';
        mkdir($path, 0777, true);
        foreach ($target_images as $target_image)  {

            $src = $target_image->getAttribute('src');

            if (str_contains($src, 'http')) { //Try to download high quality image
                $this->logToConsole('Process big image src -> ' . $src);
                $image_src = $target_image->getAttribute('src');
                $this->logToConsole('Try to download image -> ' . $image_src);
                $image = file_get_contents($image_src);
//                    $filename = pathinfo($image_src, PATHINFO_FILENAME);
                $ext = pathinfo($image_src, PATHINFO_EXTENSION);

                $this->tryToSave($query, $path, $ext, $image, $image_src, 'xl-');
            } else { //Downloading of small thumbnails
                $this->logToConsole('Process small image');
                $image = $target_image->getAttribute('src');
                if (($pos = strpos($image, ';')) !==  false) {
                    $type = substr($image, 0, $pos);
                    $res = explode('/', $type);
                    $ext = end($res);
                    $this->tryToSave($query, $path, $ext, $image, $type, 'sm-');
                }
            }

        }
    }

    private function tryToSave(string $query, string $path, string $ext, $image, string $log_msg, string $size)
    {
        $filename = uniqid(str_replace(' ', '', $size . $query), true);
        if (file_put_contents($path . $filename . '.' . $ext, $image)) {
            $this->logToConsole('Downloaded -> ' . $log_msg);
        } else {
            $this->logToConsole('Download Failed -> ' . $log_msg);
        }
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