<?php
declare(strict_types=1);

namespace MazenTouati\NoEmoji\Entities;

use MazenTouati\Simple2wayConfig\S2WConfig;

/**
 * A file is a self-contained object that serves as layer between the package and the file system with an extra functionalities relative to the package
 *
 * @author Mazen Touati <mazen_touati@hotmail.com>
 */
class File
{

    /**
     * The file content
     *
     * @var string
     */
    public $content;

    /**
     * Configuration object
     *
     * @var S2WConfig
     */
    public $config;

    /**
     * The configuration dot-path that lead to the file's source path
     *
     * @var string
     */
    public $src;

    public function __construct(S2WConfig $c, $src = 'storage.input.src')
    {
        $this->config = $c;
        $base = $this->config->get('storage.base');
        $src = $this->config->get($src);
        $this->content = file_get_contents($base.$src);
    }

    /**
     * Puts delimiters between group to make separating them easier
     *
     * @return void
     */
    public function prepareFileForScrapping(): void
    {
        // Note: the EOG 's (End Of Group) Marker should start with @

        // Replace the EOF marker with the EOG marker
        $content = str_replace('#EOF', '@EOG', $this->content);

        // Add the Marker between groups
        $replacement = "@EOG\n\n$1";
        $pattern = "/(# group: .+)/m";
        $this->content = preg_replace($pattern, $replacement, $content);
    }

    /**
     * Executes a Closure that alter the file content
     *
     * @param  \Closure $callback
     *
     * @return void
     */
    public function replaceContent(\Closure $callback): void
    {
        $this->content = $callback($this->content);
    }

    /**
     * Converts the data into a JSON file
     *
     * @param  string $path Output path
     * @param  string $fileTitle The file's title
     * @param  array $data The file data
     *
     * @return boolean|array Return false on error or an array the hold the created file details
     */
    public static function toJSON(string $path, string $fileTitle, array $data)
    {
        $size = file_put_contents($path, json_encode($data));
        if ($size === false) {
            return false;
        }

        return [
            'path' => $path,
            'fileTitle' => $fileTitle,
            'size' => $size
        ];
    }
    /**
     * Converts  the data into a text file
     *
     * @param  string $path Output path
     * @param  string $fileTitle The file's title
     * @param  string $data The file content
     *
     * @return boolean|array Return false on error or an array the hold the created file details
     */
    public static function toText(string $path, string $fileTitle, string $data)
    {
        $size = file_put_contents($path, $data);
        if ($size === false) {
            return false;
        }

        return [
            'path' => $path,
            'fileTitle' => $fileTitle,
            'size' => $size
        ];
    }

    /**
     * Converts a JSON file to an array
     *
     * @param  boolean $assocArray Indicates if returned data should be an associative arrays or not.
     *
     * @return array
     */
    public function fromJSON($assocArray = false)
    {
        return json_decode($this->content, $assocArray);
    }
}
