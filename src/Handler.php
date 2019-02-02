<?php
declare(strict_types=1);

namespace MazenTouati\NoEmoji;

use MazenTouati\Simple2wayConfig\S2WConfig;
use MazenTouati\NoEmoji\Entities\File;
use MazenTouati\NoEmoji\Entities\GroupsFactory;
use MazenTouati\NoEmoji\Entities\Group;

/**
 * Uses the scrapped unicodes to generate a RegEx pattern
 *
 * @author Mazen Touati <mazen_touati@hotmail.com>
 */
class Handler
{
    /**
     * The class instance
     *
     * @var [Handler]
     */
    private static $_instance = null;

    /**
     * Emoji unicodes
     *
     * @var [array]
     */
    private $_unicodes = [];

    /**
     * Unicodes as intervals
     *
     * @var [array]
     */
    private $_ranges = [];

    /**
     * The produced pattern
     *
     * @var [type]
     */
    private $_pattern = '';

    /**
     * Configuration object
     *
     * @var S2WConfig
     */
    public $config;

    /**
     * Retrieves the singleton instance
     *
     * @return Handler
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Handler();
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

        $file = new File($c, 'storage.output.json');
        $instance->unicodes = $file->fromJSON(true);

        return $instance;
    }

    /**
     * Starts the Handling
     *
     * @return Handler
     */
    public function run()
    {
        $this->_splitXbitUnicodes(Group::SINGLE_UNIT_20_BIT);
        $this->_splitXbitUnicodes(Group::MULTI_UNIT_20_BIT);
        $this->_splitXbitUnicodes(Group::SINGLE_UNIT_16_BIT);
        $this->_fusionXbitUnicodes(Group::MULTI_UNIT_16_BIT);
        $this->_transformRangesToPattern();
        return $this;
    }

    /**
     * Divides the unicodes into intervals
     *
     * @param  string $bitsKey The holder array's key for the bits
     *
     * @return void
     */
    private function _splitXbitUnicodes($bitsKey)
    {
        if (!isset($this->unicodes[$bitsKey])) {
            return false;
        }

        $data = $this->unicodes[$bitsKey];
        sort($data);

        $ranges = [
            [ $data[0] ]
        ];
        $rangesIndex = 0;
        $lastRangeIndex = 0;

        foreach ($data as $k => $unicode) {
            if ($k === 0) {
                continue;
            }

            $lastItem = $ranges[$rangesIndex][$lastRangeIndex];

            if (hexdec($unicode) - hexdec($lastItem) === 1) {
                $ranges[$rangesIndex][] = $unicode;
                $lastRangeIndex++;
            } else {
                $ranges[] = [$unicode];
                $lastRangeIndex = 0;
                $rangesIndex++;
            }
        }

        $this->_ranges = array_merge($this->_ranges, $ranges);
    }

    /**
     * Glues the multi unit values into RegEx pattern
     *
     * @param  string $bitsKey The holder array's key for the bits
     *
     * @return void
     */
    private function _fusionXbitUnicodes(string $bitsKey)
    {
        $data = $this->unicodes[$bitsKey];

        $patternString = '';
        foreach ($data as $unicode) {
            $bytes = explode(' ', $unicode);
            $bytesPattern = '';

            foreach ($bytes as $byte) {
                $bytesPattern .= '\x{' . $byte .'}';
            }

            $patternString .= '|' . $bytesPattern;
        }

        if (empty($this->_pattern)) {
            $patternString = ltrim($patternString, '|');
        }
        $this->_pattern .= $patternString;
    }

    /**
     * Transforms the ranges to a RegEx pattern
     *
     * @return void
     */
    private function _transformRangesToPattern()
    {
        $reducedRanges = [];
        foreach ($this->_ranges as $range) {
            if (isset($range[1])) {
                $first = reset($range);
                $last = end($range);
                $reducedRanges[] = '[\x{' . $first . '}-\x{' . $last . '}]';
            } else {
                $reducedRanges[] = '\x{' . $range[0] . '}';
            }
        }

        if (!empty($this->_pattern)) {
            $this->_pattern .= '|';
        }

        $this->_pattern .= implode('|', $reducedRanges);
    }

    /**
     * Exports the unicodes data into JSON file
     *
     * @return bool|array Returns false on error or an Array holds the created file details
     */
    public function export()
    {
        $base = $this->config->get('storage.base');
        $output = $this->config->get('storage.output.ranges');
        $fileTitle = $this->config->get('storage.output.rangesFileTitle');

        $content = '\''. $this->_pattern .'\'';

        return File::toText($base.$output, $fileTitle, $content);
    }

    /**
     * Tests the produced pattern efficiency catching the Emojis
     *
     * @param  string $replacement The replacement for the Emojis
     * @return bool|array Returns false on error or an array holds the created file details
     */
    public function testPattern($replacement = '@GOT_YOU')
    {
        $file = new File($this->config);

        $pattern = '('. $this->_pattern .').*\n';
        $count = 0;

        $file->replaceContent(function ($content) use ($pattern, $replacement, &$count) {
            $newContent = preg_replace('/' . $pattern . '/mu', $replacement."\n", $content, -1, $count);
            return iconv(mb_detect_encoding($newContent), 'UTF-16', $newContent);
        });

        $base = $this->config->get('storage.base');
        $fileTitle = $this->config->get('storage.output.testFileTitle');
        $output = $this->config->get('storage.output.test');

        $export = File::toText($base.$output, $fileTitle, $file->content);

        if ($export === false) {
            return false;
        }

        return array_merge($export, ['count' => $count]);
    }
}
