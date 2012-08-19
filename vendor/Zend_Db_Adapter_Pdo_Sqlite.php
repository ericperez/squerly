<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Sqlite.php 23775 2011-03-01 17:25:24Z ralph $
 */


/**
 * Class for connecting to SQLite2 and SQLite3 databases and performing common operations.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Adapter_Pdo_Sqlite
{
    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public static function listTables($DBC = 'DB')
    {
        $sql = "SELECT name FROM sqlite_master WHERE type='table' "
             . "UNION ALL SELECT name FROM sqlite_temp_master "
             . "WHERE type='table' ORDER BY name";
        DB::sql($sql, NULL, 60, $DBC);
        return F3::get("{$DBC}->result");
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public static function describeTable($tableName, $schemaName = null)
    {
        $sql = 'PRAGMA ';

        if ($schemaName) {
            $sql .= $this->quoteIdentifier($schemaName) . '.';
        }

        $sql .= 'table_info('.$this->quoteIdentifier($tableName).')';

        $stmt = $this->query($sql);

        /**
         * Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
         */
        $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        $cid        = 0;
        $name       = 1;
        $type       = 2;
        $notnull    = 3;
        $dflt_value = 4;
        $pk         = 5;

        $desc = array();

        $p = 1;
        foreach ($result as $key => $row) {
            list($length, $scale, $precision, $primary, $primaryPosition, $identity) =
                array(null, null, null, false, null, false);
            if (preg_match('/^((?:var)?char)\((\d+)\)/i', $row[$type], $matches)) {
                $row[$type] = $matches[1];
                $length = $matches[2];
            } else if (preg_match('/^decimal\((\d+),(\d+)\)/i', $row[$type], $matches)) {
                $row[$type] = 'DECIMAL';
                $precision = $matches[1];
                $scale = $matches[2];
            }
            if ((bool) $row[$pk]) {
                $primary = true;
                $primaryPosition = $p;
                /**
                 * SQLite INTEGER primary key is always auto-increment.
                 */
                $identity = (bool) ($row[$type] == 'INTEGER');
                ++$p;
            }
            $desc[$this->foldCase($row[$name])] = array(
                'SCHEMA_NAME'      => $this->foldCase($schemaName),
                'TABLE_NAME'       => $this->foldCase($tableName),
                'COLUMN_NAME'      => $this->foldCase($row[$name]),
                'COLUMN_POSITION'  => $row[$cid]+1,
                'DATA_TYPE'        => $row[$type],
                'DEFAULT'          => $row[$dflt_value],
                'NULLABLE'         => ! (bool) $row[$notnull],
                'LENGTH'           => $length,
                'SCALE'            => $scale,
                'PRECISION'        => $precision,
                'UNSIGNED'         => null, // Sqlite3 does not support unsigned data
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity
            );
        }
        return $desc;
    }

}
