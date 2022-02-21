<?php

namespace Src\Gateways;

use Src\Methods\FileReader;
use Src\Methods\FileWriter;

class ContactGateway
{
    private static $instance = null;
    private $filewriter;
    private $filereader;

    private function __construct()
    {
        $this->filewriter = FileWriter::getInstance();
        $this->filereader = FileReader::getInstance();
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new contactGateway();
        }

        return self::$instance;
    }

    public function insert($data)
    {
        try {
            if ($this->filewriter->process($data)) {
                $this->filewriter->finish();
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }

    public function findAll()
    {
        $list = $this->filereader->process();
        $this->filereader->finish();
        return $list;
    }

    public function find($data)
    {
        return $data;
    }

    public function update($data)
    {
    }

    public function delete($data)
    {
        try {
            if ($this->filewriter->update($data)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            exit($e->getMessage());
        }
    }
}