<?php

abstract class Form_Base extends depage\htmlform\htmlform implements Form_Interface {

  public function __construct() {
    //parent::__construct('form_report_add');
    $this->_addElements();
  }

  public function addElements() {}

  public function render() {}

  public function renderTable() {}

  public function getConfig() {}

}
