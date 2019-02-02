<?php
declare(strict_types=1);

namespace MazenTouati\NoEmoji\Entities;

/**
 * A group is a self-contained object that stores and manipulates the data of a single unicodes group
 *
 * @author Mazen Touati <mazen_touati@hotmail.com>
 */
class Group
{
    /**
     * The array key for unicodes with single unit of 16 bit
     *
     * @var string
     */
    const SINGLE_UNIT_16_BIT = '16bit';

    /**
     * The array key for unicodes with single unit of 20 bit
     *
     * @var string
     */
    const SINGLE_UNIT_20_BIT = '20bit';

    /**
     * The array key for unicodes with multi unit of 16 bit
     *
     * @var string
     */
    const MULTI_UNIT_16_BIT = '16bit-MultiUnitValues';

    /**
     * The array key for unicodes with multi unit of 16 bit
     *
     * @var string
     */
    const MULTI_UNIT_20_BIT = '20bit-MultiUnitValues';

    /**
     * Group's name or title
     *
     * @var string
     */
    public $name = '';

    /**
     * The content of the group as a plain text
     *
     * @var string
     */
    private $_content = '';

    /**
     * The unicodes that are extracted from the group
     *
     * @var array
     */
    private $_unicodes = [];

    /**
     * Set of pattern to extract the different unicodes from the group
     *
     * @var array
     */
    private $_patterns = [
        self::SINGLE_UNIT_20_BIT => '/^([0-9A-F]{5}(?: [0-9A-F]+)*)\s*;/m',
        self::SINGLE_UNIT_16_BIT  => '/^([0-9A-F]{4}(?: [0-9A-F]+)*)\s*;/m',
    ];

    public function __construct(string $name, string $content)
    {
        $this->name = $name;
        $this->_content = $content;
    }

    /**
     * Unicodes getter
     *
     * @param  string $bits When not null it the method will return the data of the selected bits
     *
     * @return array The group's unicodes
     */
    public function getUnicodes($bits = null)
    {
        if ($bits === null) {
            return $this->_unicodes;
        }
        return isset($this->_unicodes[$bits]) ? $this->_unicodes[$bits] : [];
    }

    /**
     * Extracts the unicodes from the group's plain text content
     *
     * @return Group
     */
    public function extractUnicodes()
    {
        $unicodesHandled = [];

        foreach ($this->_patterns as $bits => $pattern) {
            preg_match_all($pattern, $this->_content, $matches);

            $extractedUnicodes = $matches[1];

            $multiUnitUnicodes = $this->popMultiUnitValues($extractedUnicodes, $bits);

            if (!empty($extractedUnicodes)) {
                $unicodes[$bits] = $extractedUnicodes;
            }

            if (!empty($multiUnitUnicodes)) {
                // For the 20 bit multi unit values we gonna transform the multi unit to single unit
                if ($bits == self::SINGLE_UNIT_20_BIT) {
                    $unicodes[self::MULTI_UNIT_20_BIT] = $this->flattenMultiUnitValues($multiUnitUnicodes);
                }
                // The 16 bit multi unit values use an initial unit that is used by ASCII characters so we have to avoid flattening them
                else {
                    $unicodes[self::MULTI_UNIT_16_BIT] = $multiUnitUnicodes;
                }
            }
        }

        $this->_unicodes = $unicodes;
        return $this;
    }

    /**
     * Removes the unicodes with multi units from the input array and return an array with the removed values and discard the modifiers
     *
     * @param  array  $data Unicodes' array
     * @param  string $bits The target bit's key
     *
     * @return array The removed multi unit values
     */
    public function popMultiUnitValues(array &$data, string $bits): array
    {
        $cleanData = [];
        $multiUnitValues = [];
        $digits = (int)$bits / 4;

        // Extract the multi unit values
        // --
        foreach ($data as $k => $v) {
            // it's like if count($v) >= $digits ?
            if (isset($v[$digits])) {
                $multiUnitValues[] = $v;
                unset($data[$k]);
            }
        }
        // Re-base source array
        $data = array_values($data);

        // Discard the modifiers
        // --
        $joinedData = implode(',', $data);
        foreach ($multiUnitValues as $k => $v) {
            $firstUnit = substr($v, 0, $digits);
            // If the first unit is already extracted then this multi unit value represents a modifier => discard
            if (strpos($joinedData, $firstUnit) !== false) {
                unset($multiUnitValues[$k]);
            }
        }

        return array_values($multiUnitValues);
    }

    /**
     * Returns only the first unit of the given multi unit values
     *
     * @param array $data Input multi unit values
     *
     * @return array Output single unit values
     */
    public function flattenMultiUnitValues($data)
    {
        $new = [];
        foreach ($data as $v) {
            $units = explode(' ', $v);
            // Ignore already existent units
            if (in_array($units[0], $new)) {
                continue;
            }

            $new[] = $units[0];
        }

        return $new;
    }
}
