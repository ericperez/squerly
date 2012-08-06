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

class XmlToArray {

  public static function render($xml_content)
  {
      $xml = new XMLReader();
      $xml->XML($xml_content);

      //First pass - discover all path and branch points
      $cols = array();
      $root = new xml_path(null, "root");
      $current = $root;
      while($xml->read()) {
          if (in_array($xml->nodeType, array(XMLReader::TEXT, XMLReader::CDATA, XMLReader::WHITESPACE, XMLReader::SIGNIFICANT_WHITESPACE))) {
              if(trim($xml->value) == "") continue;
              $current->hastext = true;
          }
          if ($xml->nodeType == XMLReader::ELEMENT) {
              $child = $current->findChild($xml->name);
              if($child !== null) {
                  $current = $child;
                  $current->counter++;
              } else {
                  //brand new path
                  $current = new xml_path($current, $xml->name);
              }
          }
          if ($xml->nodeType == XMLReader::END_ELEMENT) {
              $current->analyzeBranch();
              $current = $current->parent;
          }
      }

      //Build the Column Header
      $cols = array();
      $root->analyzeColumn($cols);
      $header = array();
      $i = 0;
      foreach($cols as $path) {
          //append parent's path name to be more descriptive
          if($path->parent !== null) {
              $header[$i] = ''; //$path->parent->name . "/"; //TODO: this may be necessar to add back for more complicated XML files
          }
          $header[$i] .= $path->name;
          $i++;
      }

      //Second pass - map values to current branch points
      $xml->XML($xml_content);
      $current = $root;
      $branch = null;
      while($xml->read()) {
          if (in_array($xml->nodeType, array(XMLReader::TEXT, XMLReader::CDATA, XMLReader::WHITESPACE, XMLReader::SIGNIFICANT_WHITESPACE))) {
              $value = trim($xml->value);
              if(trim($xml->value) == "") continue;
              $branch->row[$current->colid] = $value;
          }
          if ($xml->nodeType == XMLReader::ELEMENT) {
              $current = $current->findChild($xml->name);
              $branch = $current->getBranch();
          }
          if ($xml->nodeType == XMLReader::END_ELEMENT) {
              if($current == $branch) {
                  $branch->closeBranch();
              }
              $current = $current->parent;
              $branch_new = $current->getBranch();
              if($branch_new !== null) {
                  $branch = $branch_new;
              }
          }
      }

      //Returns the output array
      return $root->output($header);
  }


}

