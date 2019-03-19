<?php

namespace Phroses\Testing;

class TestCase extends \PHPUnit\Framework\TestCase {
    protected $db;

    public function assertArrayEquals($expected, $actual) {
        $this->assertEqualsCanonicalizing($expected, $actual, "", $delta = 0.0, $maxDepth = 10);
    }

    public function assertArrayType(array $array, $type) {
        $consistent = true;
    
        foreach($array as $key => $value) {
            if(!($value instanceof $type)) $consistent = false;
        }

        $this->assertTrue($consistent);
    }

    protected function getDatabase() {
        return include \Phroses\INCLUDES["TESTS"]."/database.php";
    }

    protected function insertDataset($table, $dataset) {
        foreach($dataset as $item) {
            $query = "insert into `{$table}` ({columns}) VALUES ({values})";
            $values = [];

            foreach((array)$item as $key => $val) {
                $query = str_replace(["{columns}", "{values}"], ["{$key}, {columns}", ":{$key}, {values}"], $query);
                $values[":{$key}"] = $val;
            }

            $query = str_replace([", {columns}", ", {values}"], "", $query);
            $stmt = $this->db->prepare($query);
            $stmt->execute($values);   
        }
    }
}