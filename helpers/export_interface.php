<?php

interface Export_Interface {
  
  public static function render(array $data, $filename = NULL, $configuration = array());
  //TODO: public static function setHeaders();
}