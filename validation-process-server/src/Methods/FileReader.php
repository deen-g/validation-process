<?php

namespace Src\Methods;

class FileReader
{
    /**
     * @var FileReader
     */
    private static $instance = null;
    public $handlefile;
    private $filename = "data.txt";

    public function __construct()
    {
        $this->handlefile = fopen($this->filename, "r");
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new FileReader();
        }

        return self::$instance;
    }

    public function process()
    {
        if (filesize($this->filename) == 0) {
            return [];
        }
        $read = fread($this->handlefile, filesize($this->filename));
        return explode('\n', $read);
    }

    public function finish()
    {
        fclose($this->handlefile);
    }
}