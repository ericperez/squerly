<?php
/**
 *
 * Squerly - Base Spreadsheet file export class (using PHPExcel)
 *
 * @author Eric Perez <ericperez@squerly.net>
 * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
 * @license GNU General Public License, version 3 or later
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.squerly.net
 *
 */
class Export_Spreadsheet_Base implements Export_Interface {

  /**
   *
   * Renders 2D associative array data as an Spreadsheet file
   *
   * @param array $data 2D associative array of data to be exported
   * @param string $filename Name of file that will hold the spreadsheet results
   * @param array $config Array of configuration settings
   *
   * @todo Use $config to set the title, subject, and description properties
   *
   */
  public static function render(array $data, $filename = 'export', $config = array()) {
    if(empty($config)) { return ''; }

    $php_excel = new PHPExcel();
    $php_excel->setActiveSheetIndex(0);

    //Spreadsheet Metadata
    $php_excel->getProperties()->setCreator("Squerly Reporting Framework (http://www.squerly.net)");
    $php_excel->getProperties()->setLastModifiedBy("Squerly");
    $php_excel->getProperties()->setTitle(String::humanize($filename));
    //$php_excel->getProperties()->setSubject("");
    //$php_excel->getProperties()->setDescription("");

    header('Content-Type: ' . $config['content_type']);
    $filename .= '.' . $config['file_extension'];
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    $php_excel->getActiveSheet()->fromArray($data, NULL, 'A1'); //Convert data into an in-memory spreadsheet object
    $excel_writer = PHPExcel_IOFactory::createWriter($php_excel, $config['output_type']);
    $excel_writer->save('php://output'); //Send the file to the browser
    $php_excel->disconnectWorksheets();
    $php_excel = null;
    return;
  }
}
