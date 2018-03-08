<?php

namespace Phroses;

class DataClass {
    protected $data = [];
    protected $tableName;
    protected $db;

    use \Phroses\Traits\UnpackOptions;
    
    public function __construct(array $data, $db = "Phroses\DB") {
        $data = array_change_key_case($data);
        $this->unpackOptions($data, $this->data);
        $this->db = $db;
    }

    public function __get(string $key) {
        return $this->data[$key] ?? 
            ($this->data[$key] = $this->db::query("SELECT `{$key}` FROM `{$this->tableName}` WHERE `id`=:id", [ ":id" => $this->data["id"] ])[0]->{$key} ?? null);
    }

    public function __set(string $key, $val): void {
        if(method_exists($this, "set{$key}")) {
            $val = $this->{"set{$key}"}($val);
        }
        
        $this->db::query("UPDATE `{$this->tableName}` SET `{$key}`=:val", [ ":val" => $val ]);
        $this->data[$key] = $val;
    }

    public function getData(): array {
        return $this->data;
    }

    public function delete(): bool {
        if(!$this->id) return false;
        return $this->db::affected("DELETE FROM `{$this->tableName}` WHERE `id`=:id", [ ":id" => $this->id ]) > 0;
    }
}