<?php

namespace Phroses\Database\Queries\Traits;

trait Where {
    protected $where = [];

    public function addWhere($column, $comparison = "=", $value = null) {
        $this->where[] = [ $column, $comparison, $value ];
        return $this;
    }

    public function filterWhere() {
        if(!empty($this->where)) {
            $where = array_map(function($val) {
                
                [ $column, $comparison, $value ] = $val;
                $value = $value ?? "?";
                return "{$column}{$comparison}{$value}";

            }, $this->where);

            $this->tpl->where = " WHERE ".implode(" AND ", $where);
        } else {
            $this->tpl->where = "";
        }
    }

}