<?php

//TODO: finish this
class Form_Report_Add extends Form_Base {

  //Adds all the required elements to the form
  protected function _addElements() {
    $this->addText('username', array('label' => 'User name', 'required' => true));
    $this->addEmail('email', array('label' => 'Email address'));
    $this->addBoolean('accept', array('label' => 'Accept terms and conditions.', 'required' => true));
  }

  public function render() {}

  public function getConfig() {}

}
