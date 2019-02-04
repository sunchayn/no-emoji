<?php
declare(strict_types=1);

namespace MazenTouati\NoEmoji;

use MazenTouati\Simple2wayConfig\S2WConfig;
use MazenTouati\NoEmoji\Entities\File;

/**
 * Uses the scrapped unicodes to generate a RegEx pattern
 *
 * @author Mazen Touati <mazen_touati@hotmail.com>
 */
class Prettifier
{
    /**
     * The class instance
     *
     * @var Prettifier
     */
    private static $_instance = null;

    /**
     * File instance
     * @var File
     */
    private $_file;

    /**
     * The pretty content
     * @var string
     */
    private $_content;

    /**
     * Retrieves the singleton instance
     *
     * @return Prettifier
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Prettifier();
        }

        return self::$_instance;
    }

    /**
     * Initializes the Scrapper
     *
     * @param S2WConfig $c Configuration object
     *
     * @return Handler
     */
    public static function factory(S2WConfig $c)
    {
        $instance = self::getInstance();
        $instance->config = $c;

        $instance->_file = new File($c, 'storage.output.ranges');

        return $instance;
    }

    /**
     * Starts prettifying
     *
     * @param  string $type Prettifier class name
     *
     * @return Prettifier
     */
    public function run($type = 'ArrayPrettifier')
    {
        $type = 'MazenTouati\NoEmoji\Prettifiers\\'.$type;
        $this->_content = $type::run($this->_file);
        return $this;
    }

    /**
     * Exports the pretty output
     *
     * @return boolean|array Returns false on error or the result array
     */
    public function export()
    {
        $base = $this->config->get('storage.base');
        $fileTitle = $this->config->get('storage.output.prettyRangesFileTitle', 'Undefined Title');
        $output = $this->config->get('storage.output.prettyRanges');

        $export = File::toText($base . $output, $fileTitle, $this->_content);

        if (!is_array($export)) {
            return false;
        }

        return $export;
    }
}
