<?php
/**
 *
 * Squerly - Excel 2007 file export class (using PHPExcel)
 *
 * @author Eric Perez <ericperez@squerly.net>
 * @copyright (c)2012-2013 Squerly contributors (Eric Perez, et al.)
 * @license GNU General Public License, version 3 or later
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link http://www.squerly.net
 *
 */
class Export_Excel_2007 extends Export_Spreadsheet_Base {

  /**
   *
   * Renders 2D associative array data as an Excel 2007 file
   *
   * @param array $data 2D associative array of data to be exported
   * @param string $filename Name of file that will hold the spreadsheet results
   * @param array $config Array of configuration settings
   *
   * @todo Use $config to set the title, subject, and description properties
   *
   */
  public static function render(array $data, $filename = 'export', $config = array()) {
    $config = array(
      'file_extension' => 'xlsx',
      'output_type' => 'Excel2007',
      'content_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    );
    return parent::render(&$data, $filename, $config);
  }
}
