<?php
/*
 * This file is part of the Foil package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Foil\Template;

use InvalidArgumentException;

/**
 * Find templates in registered folder by their names.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package foil\foil
 * @license http://opensource.org/licenses/MIT MIT
 */
class Finder
{
    /**
     * @var array
     */
    private $dirs = [];

    /**
     * @var string
     */
    private $ext = 'php';

    /**
     * @param string $ext
     */
    public function __construct($ext = 'php')
    {
        $this->ext = $ext;
    }

    /**
     * Set the folders where to search for templates
     *
     * @param  array                    $dirs
     * @param  bool                     $reset
     * @throws InvalidArgumentException
     */
    public function in(array $dirs, $reset = false)
    {
        $reset and $this->dirs = [];
        array_walk($dirs, function ($dir, $name) {
            if (! is_dir($dir)) {
                throw new InvalidArgumentException('Template folders must be readable paths.');
            }
            if (! is_string($name)) {
                $norm = explode(DIRECTORY_SEPARATOR, $this->normalize($dir));
                $name = implode('.', array_slice($norm, -2));
            }
            $this->dirs[$name] = $dir;
        });
    }

    /**
     * Find a template
     *
     * @param  string|array             $template
     * @return string|boolean           Template path if found or false if not
     * @throws InvalidArgumentException
     */
    public function find($template)
    {
        is_array($template) and $template = array_filter($template, 'is_string');
        if ((! is_string($template) && ! is_array($template)) || empty($template)) {
            throw new InvalidArgumentException(
                'Template name must be a string or an array of strings.'
            );
        }
        if (is_array($template)) {
            return $this->findMany($template);
        }
        $parse = $this->parseName($template);
        if ($parse['dir']) {
            return $this->findInDir($parse['dir'], $parse['file']);
        }

        return $parse['file'] ? $this->scanFor($parse['file']) : false;
    }

    /**
     * Normalize paths to be consistent with current server OS.
     * Also strips dots from path edges to avoid relative parent folders files inclusion
     *
     * @param  string $path
     * @return string
     */
    public function normalize($path)
    {
        return trim(preg_replace('|[\\/]+|', DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR.'.:');
    }

    /**
     * Getter for registered directories
     *
     * @return array
     */
    public function dirs()
    {
        return $this->dirs;
    }

    /**
     * Return first found of an array of templates
     *
     * @param  array       $templates
     * @return bool|string
     */
    private function findMany(array $templates)
    {
        $found = false;
        while (! $found && ! empty($templates)) {
            $found = $this->find(array_shift($templates));
        }

        return $found;
    }

    /**
     * Takes a template name and looks for directory name passed in Foil convention, that is
     * folder_name::file_name.
     * If file name as no extension, default extension is appended if available.
     *
     * @param  string $templateName
     * @return array
     * @access private
     */
    private function parseName($templateName)
    {
        $dir = false;
        $array = explode('::', $templateName);
        if (count($array) > 1) {
            $dir = $this->normalize($array[0]);
            $templateName = $this->normalize($array[1]);
        }
        $ext = (string) pathinfo($templateName, PATHINFO_EXTENSION);
        if (empty($ext) && is_string($this->ext) && ! empty($this->ext)) {
            $templateName .= ".{$this->ext}";
        }

        return ['dir' => $dir, 'file' => $this->normalize($templateName)];
    }

    /**
     * Find a template when a specific directory name is required
     *
     * @param  string  $dir
     * @param  string  $templateName
     * @return boolean
     */
    private function findInDir($dir, $templateName)
    {
        if (! array_key_exists($dir, $this->dirs)) {
            return false;
        }

        return $this->exists($this->dirs[$dir], $templateName);
    }

    /**
     * Scans directories to find a template that matches given template name.
     *
     * @param  string         $template
     * @return string|boolean Template full path if found, false otherwise
     * @access private
     */
    private function scanFor($template)
    {
        $in = $this->dirs;
        $found = false;
        while (! $found && ! empty($in)) {
            $dir = array_shift($in);
            $found = $this->exists($dir, $template);
        }

        return $found;
    }

    /**
     * Check if a given template file exists in a given directory
     *
     * @param  string         $dir
     * @param  string         $template
     * @return string|boolean Template full path if found, false otherwise
     */
    private function exists($dir, $template)
    {
        $path = $dir.DIRECTORY_SEPARATOR.$template;

        return file_exists($path) ? $path : false;
    }
}
