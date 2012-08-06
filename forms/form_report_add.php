<?php

//TODO: finish this
class Form_Report_Add extends Form_Base {

  public function __construct() {
    parent::__construct('form_report_add');
    $this->_addElements();
  }

  //Adds all the required elements to the form
  protected function _addElements() {
    $this->addText('username', array('label' => 'User name', 'required' => true));
    $this->addEmail('email', array('label' => 'Email address'));
    $this->addBoolean('accept', array('label' => 'Accept terms and conditions.', 'required' => true));
  }

}
