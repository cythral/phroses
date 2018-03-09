<?php

namespace Phroses\Testing;

class TestCase extends \PHPUnit\Framework\TestCase {
    protected $db;

    public function assertArrayEquals($expected, $actual) {
        $this->assertEquals($expected, $actual, "\$canonicalize = true", $delta = 0.0, $maxDepth = 10, $canonicalize = true);
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