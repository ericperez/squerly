<?php
//BASED ON: https://sites.google.com/site/soichih/q-a/xml-to-csv-converter

/*#################################################################################################

Copyright 2009 The Trustees of Indiana University

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in
compliance with the License. You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License
is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
implied. See the License for the specific language governing permissions and limitations under the
License.

#################################################################################################*/

class xml_path
{
    public $colid;

    public $parent;
    public $name;
    public $children;

    public $branch;
    public $counters;
    public $hastext;

    public $rows;
    public $row; //current row to set values

    function __construct($parent, $name) {
        $this->colid = null;
        $this->branch = false;
        $this->hastext = false;
        $this->counter = 0;
        $this->rows = array();
        $this->row = array();

        $this->parent = $parent;
        $this->name = $name;

        //register myself to parent
        $this->children = array();
        if($parent !== null) {
            $parent->children[] = $this;
        }
    }
    function getFullPath()
    {
        $fullname = "";
        if($this->parent !== null) {
            $fullname .= $this->parent->getFullPath() . "/";
        }
        $fullname .= $this->name;
        return $fullname;
    }
    function findChild($name) {
        foreach($this->children as $child) {
            if($child->name == $name) return $child;
        }
        return null;
    }
    function analyzeColumn(&$cols, $under_branch = false) {
        if($under_branch) { // && $this->hastext) {
            $this->colid = sizeof($cols);
            $cols[] = $this;
        }
        foreach($this->children as $child) {
            if($this->branch) {
                $under_branch = true;
            }
            $child->analyzeColumn($cols, $under_branch);
        }
    }
    function analyzeBranch() {
        foreach($this->children as $child) {
            if($child->counter > 1) {
                $child->branch = true;
            }
            $child->counter = 0;
        }
    }
    function getBranch()
    {
        if($this->branch) return $this;
        if($this->parent !== null) {
            return $this->parent->getBranch();
        }
        return null;
    }

    function getRoot()
    {
        if($this->parent === null) return $this;
        return $this->parent->getRoot();
    }

    function closeBranch()
    {
        //close this branch and send all records to parent branch (or root)
        $parent_branch = $this->parent->getBranch();
        if($parent_branch === null) {
            $parent_branch = $this->getRoot();
        }

        //merge my row and sub-rows to parent rows.
        if(sizeof($this->rows) == 0) {
            $parent_branch->rows[] = self::merge_row($parent_branch->row, $this->row);
        } else {
            foreach($this->rows as $row) {
                $parent_branch->rows[] = self::merge_row($parent_branch->row, self::merge_row($this->row, $row));
            }
        }
        //reset this and child rows
        $this->row = array();
        $this->rows = array();
    }

    public static function merge_row($row1, $row2)
    {
        foreach($row2 as $key=>$col) {
            $row1[$key] = $col;
        }
        return $row1;
    }

    //Build the output array
    function output($cols)
    {
        $output = array();
        foreach($this->rows as $row) {
            $row = array_pad($row, sizeof($cols), ''); //TODO: does this work?
            $output[] = array_combine($cols, $row);
        }
        return $output;
    }
    
}
