<?php
declare(strict_types=1);

namespace MazenTouati\NoEmoji\Prettifiers;

use MazenTouati\NoEmoji\Entities\File;

/**
 * @author Mazen Touati <mazen_touati@hotmail.com>
 */
class ArrayPrettifier
{
    /**
     * Prettify the input
     *
     * @param File $file The file instance
     *
     * @return string
     */
    public static function run(File $file)
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
