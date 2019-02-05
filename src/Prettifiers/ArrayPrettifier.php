<?php
declare(strict_types=1);

namespace MazenTouati\NoEmoji\Prettifiers;

/**
 * @author Mazen Touati <mazen_touati@hotmail.com>
 */
class ArrayPrettifier
{
    /**
     * Prettify the input
     *
     * @param  File $file The file instance
     *
     * @return string
     */
    public static function run($file)
    {
        $content = trim($file->content, '\'');
        $ranges = explode('|', $content);

        $pretty = '[' . PHP_EOL;

        foreach ($ranges as $range) {
            $pretty .= "\t'". $range . "'," . PHP_EOL;
        }

        $pretty .= ']' . PHP_EOL;

        return $pretty;
    }
}
