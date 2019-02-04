<?php
declare(strict_types=1);

namespace MazenTouati\NoEmoji\Entities;

/**
 * A utility class that holds and interacts with groups
 *
 * @author Mazen Touati <mazen_touati@hotmail.com>
 */
class GroupsFactory
{
    /**
     * The RegEx pattern used to extract the groups from the file
     *
     * @var string
     */
    public $pattern = '/# group: (.*)\n+([^@]+)/m';

    /**
     * Holds the Group's instances
     *
     * @var array
     */
    public $groups = [];

    /**
     * Holds the Unicodes extracted from the groups
     *
     * @var array
     */
    public $emojiUnicodes = [];

    /**
     * Unicodes getter
     *
     * @return array
     */
    public function getUnicodes()
    {
        return $this->emojiUnicodes;
    }

    /**
     * Divides the whole file into smaller groups.
     *
     * @param  string $source The textual source to perform the scrapping on
     * @return GroupsFactory
     */
    public function extractGroups($source)
    {
        preg_match_all($this->pattern, $source, $matches);
        foreach ($matches[1] as $i => $groupName) {
            $this->groups[] = new Group($groupName, $matches[2][$i]);
        }

        return $this;
    }

    /**
     * Extracts the Unicodes from the plain text of each group
     * @return GroupsFactory
     */
    public function extractUnicodesFromGroups()
    {
        foreach ($this->groups as $group) {
            $this->emojiUnicodes[$group->name] = $group->extractUnicodes()->getUnicodes();
        }

        return $this;
    }

    /**
     * Flattens the Unicode array from being group based to bit based
     *
     * @return GroupsFactory
     */
    public function flattenUnicodes()
    {
        $flattenedUnicodes = [
            Group::SINGLE_UNIT_20_BIT => [],
            Group::MULTI_UNIT_20_BIT => [],
            Group::SINGLE_UNIT_16_BIT => [],
            Group::MULTI_UNIT_16_BIT => [],
        ];

        $bitKeys = array_keys($flattenedUnicodes);

        foreach ($bitKeys as $bits) {
            foreach ($this->groups as $group) {
                $unicodes = $group->getUnicodes($bits);
                $flattenedUnicodes[$bits] = array_merge($flattenedUnicodes[$bits], $unicodes);
            }
        }

        $this->emojiUnicodes = $flattenedUnicodes;
        $this->mergeMultiWithSingle(Group::MULTI_UNIT_20_BIT, Group::SINGLE_UNIT_20_BIT);

        return $this;
    }

    /**
     * Transforms single unit values from the multi unit array to the single unit array
     *
     * @param string $multi Multi unit values array's key
     * @param string $single Single unit values array's key
     */
    public function mergeMultiWithSingle(string $multi, string $single): void
    {
        $digits = (int)$single / 4;

        foreach ($this->emojiUnicodes[$multi] as $k => $v) {
            // If the following value is single unit then transform it
            if (!isset($v[$digits])) {
                unset($this->emojiUnicodes[$multi][$k]);
                $this->emojiUnicodes[$single][] = $v;
            }
        }

        // Remove the values holder if all values are transformed to the single unit holder
        if (empty($this->emojiUnicodes[$multi])) {
            unset($this->emojiUnicodes[$multi]);
        }
        // Otherwise re-base the holder
        else {
            $this->emojiUnicodes[$multi] = array_values($this->emojiUnicodes[$multi]);
        }
    }
}
