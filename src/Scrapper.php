<?php

declare(strict_types=1);

namespace MazenTouati\NoEmoji;

use MazenTouati\Simple2wayConfig\S2WConfig;
use MazenTouati\NoEmoji\Entities\File;
use MazenTouati\NoEmoji\Entities\Group;
use MazenTouati\NoEmoji\Entities\GroupsFactory;

/**
 * Scrapes the Unicode references file to extract and organize Unicodes
 *
 * @author Mazen Touati <mazen_touati@hotmail.com>
 */
class Scrapper
{
    /**
     * The class instance
     *
     * @var Scrapper
     */
    private static $_instance = null;

    /**
     * Configuration object
     *
     * @var S2WConfig
     */
    public $config;

    /**
     * Input file
     *
     * @var File
     */
    private $_file;

    /**
     * Emoji unicodes
     *
     * @var array
     */
    private $_unicodes = [];


    /**
     * Retrieves the singleton instance
     *
     * @return Scrapper
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Scrapper();
        }

        return self::$_instance;
    }

    /**
     * Initializes the Scrapper
     *
     * @param  S2WConfig $c Configuration object
     *
     * @return Scrapper
     */
    public static function factory(S2WConfig $c)
    {
        $instance = self::getInstance();
        $instance->config = $c;

        $instance->_file = new File($c);
        $instance->_file->prepareFileForScrapping();

        return $instance;
    }

    /**
     * Starts the scrapping
     *
     * @return Scrapper
     */
    public function run(): Scrapper
    {
        $this->_unicodes = (new GroupsFactory())
            ->extractGroups($this->_file->content)
            ->extractUnicodesFromGroups()
            ->flattenUnicodes()
            ->getUnicodes();

        return $this;
    }

    /**
     * Exports the unicodes data into JSON file
     *
     * @return boolean|array Returns false on error or the result array
     */
    public function export()
    {
        $base = $this->config->get('storage.base');
        $output = $this->config->get('storage.output.json');
        $fileTitle = $this->config->get('storage.output.jsonFileTitle', 'Undefined Title');
        return File::toJSON($base . $output, $fileTitle, $this->_unicodes);
    }
}
