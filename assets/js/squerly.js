/**
  *
  * Squerly JavaScript
  * 
  * @author Eric Perez <ericperez@squerly.net>
  * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
  * @license GNU General Public License, version 3 or later
  * @license http://opensource.org/licenses/gpl-3.0.html
  * @link http://www.squerly.net
  * 
  * @todo Implement all CRUD methods in JavaScript using AJAX
  * @todo Break all this JavaScript out into individual 'class' files
  *
  */

var squerly = {};
squerly.report = {};
squerly.report_configuration = {};


/**
  *
  * Report configuration method to retrieve report saved input parameter values using AJAX
  *
  * @return void
  *
  * @todo Automate this for new report types; finish this
  *
  */
/*
squerly.report.configureCodeMirror = function() {
  try {
    var report_type = $('#type').val(); 
  } catch(e) { return; }
  var report_modes = {
    'sql': 'mysql',
    'mysqlssh': 'mysql',
    'hivessh': 'mysql',
    'csv': 'mysql',
    'pigssh': 'pig',
  };
  var query_mode = report_modes[report_type] || null;
  if(query_mode) {
    CodeMirror.fromTextArea($('#query')[0], { mode: query_mode, theme: 'lesser-dark', lineNumbers: true });
  }
};
*/


/**
  *
  * Report configuration method to retrieve report saved input parameter values using AJAX
  *
  * @param int report_configuration_id Primary key of a report configuration object
  * @return void
  *
  */
squerly.report_configuration.getValues = function(report_configuration_id) {
  $.get('/report_configuration/getvalues/' + report_configuration_id,
    function(input_values) {
      //TODO: memoize the report configuration values to reduce network traffic
      var report_form = $('#report_form');
      report_form.clearForm();
      report_form.unserializeForm(input_values);
    }
  );
};


/**
  *
  * Report configuration method to retrieve an optionlist of saved report configurations by report ID
  *
  * @param int report_configuration_id Primary key of a report object
  * @return void
  *
  * @todo Make this more generic for all models
  * 
  */
squerly.report_configuration.loadOptionlist = function(report_id) {
  url = '/report_configuration/optionlist/?report_id=' + report_id;
  var optionlist = $('[name="sqrl[config]"]');
  optionlist.load(url, '', function() { optionlist[0].selectedIndex = 1; })
};


/**
  *
  * Report configuration method to save report input parameter values using AJAX
  *
  * @param int report_configuration_id Primary key of a report configuration object TODO: implement
  * @return void
  *
  *
  * @todo Abstract this out so it encompases all CRUD models/actions
  */
squerly.report_configuration.save = function(report_configuration_id) {
  var report_configuration_name = window.prompt("Saved Report Name:", ""); //TODO: this does not work in IE
  if(!report_configuration_name) { return; }
  var report_form = $('#report_form');
  var report_config_obj = { 
    report_id: $('[name="sqrl[report_id]"]', report_form).val(),
    name: report_configuration_name,
    input_values: report_form.serialize()
  };
  $.post(
      '/report_configuration/add/token/QOFJq34igj3/redirect/false', //TODO: generate real CSRF token!
      report_config_obj,
      function() { 
        alert('Saved Report Configuration Added Successfully'); //TODO: make this more friendly.
        squerly.report_configuration.loadOptionlist(report_config_obj.report_id);
      }
  );
};


/**
  *
  * JQuery function to clear the values out of a form or form input(s)
  *
  * @param boolean only_text_inputs Limits clearing to only type=text form inputs
  * @return void
  *
  */
$.fn.clearForm = function(only_text_inputs) {
  return this.each(function() {
    var type = this.type;
    var tag = this.tagName.toLowerCase();
    if(only_text_inputs === true) { 
      var input_type = 'input:text';
    } else { 
      var input_type = ':input'; 
    }
    if(tag === 'form') { return $(input_type, this).clearForm(); }
    if(type === 'text' || type === 'password' || tag === 'textarea') { this.value = ''; }
    if(type === 'checkbox' || type === 'radio') { this.checked = false; }
    else if(tag === 'select') { this.selectedIndex = -1; }
  });
};


