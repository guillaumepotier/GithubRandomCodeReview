<?php

namespace JsonDB;

class JsonDB {

    private $db_file = false;
    private $repository = false;
    private $db_content = false;

    public function __construct()
    {
        $this->repository = __DIR__ . '/../db/';
    }

    public function setdb($db_file)
    {
        $this->db_file = $this->repository . $db_file . '.json';
    }

    public function get($field)
    {
        if (false === $this->db_content) {
            $this->fetch();
        }

        if (isset($this->db_content[$field])) {
            return $this->db_content[$field];
        }

        return false;
    }

    public function set($field, $value)
    {
        if (false === $this->db_content) {
            $this->fetch();
        }

        $this->db_content[$field] = $value;
    }

    /**
    * save all array
    **/
    public function save()
    {
        $fh = fopen($this->db_file,"w");
        fwrite($fh, json_encode($this->db_content));
        fclose($fh);
        return true;
    }

    /**
    * fetch all array
    **/
    private function fetch()
    {
        $fh = fopen($this->db_file, 'r');
        $jsonData = fread($fh, filesize($this->db_file));
        fclose($fh);
        $this->db_content = json_decode($jsonData, true);
    }
}