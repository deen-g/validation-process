<?php

namespace Src\Methods;

class FileWriter
{
    /**
     * @var FileWriter
     */
    private static $instance = null;
    public $handlefile;
    private $filename = "data.txt";

    public function __construct()
    {
        $this->handlefile = fopen($this->filename, "a");
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new FileWriter();
        }

        return self::$instance;
    }

    public function process($data)
    {
        $string = implode(',', $data);
        fwrite($this->handlefile, "$string\n");
        return true;
    }
    public function update($data)
    {
        $content = file_get_contents($this->filename);
        $content = str_replace($data, null, $content);
        file_put_contents($this->filename, $content);
        return true;
    }

    public function finish()
    {
        fclose($this->handlefile);
    }
}