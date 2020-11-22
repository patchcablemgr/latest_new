<?php
/*** *** *** *** *** ***
* @package Quadodo Login Script
* @file    MySQLie.class.php
* @start   August 2nd, 2007
* @author  Douglas Rennehan
* @license http://www.opensource.org/licenses/gpl-license.php
* @version 1.0.5
* @link    http://www.quadodo.net
*** *** *** *** *** ***
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*** *** *** *** *** ***
* Comments are always before the code they are commenting.
*** *** *** *** *** ***/
if (!defined('IN_INSTALL')) {
    exit;
}

/**
 * Contains all functions needed to test the MySQLi information
 * the user entered in the installation process.
 */
class MySQLie {
/**
 * @var object $sql_class - Contains SQL class
 */
var $sql_class;

	/**
	 * Constructs the class
	 * @param string  $database_server_name
	 * @param string  $database_username
	 * @param string  $database_password
	 * @param integer $database_port
	 * @param string  $database_name
	 * @param object  $sql_class
	 */
	function MySQLie($database_server_name, $database_username, $database_password, $database_port, $database_name, &$sql_class) {
        $this->database_server_name = $database_server_name;
        $this->database_username = $database_username;
        $this->database_password = $database_password;
        $this->database_port = $database_port;
        $this->database_name = $database_name;
        $this->sql_class = &$sql_class;
	}

	/**
	 * Attempts to create a table
	 *	@return void but will output error if found
	 */
	function test_create_table() {
        $sql = "CREATE TABLE `qls3_test`(
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `test1` VARCHAR(255) NOT NULL,
            `test2` INT(11) NOT NULL,
            `test3` TINYINT(1) NOT NULL,
            PRIMARY KEY(`id`),
            INDEX(`test1`)
        )";

		if (!$this->connection->query($sql)) {
		    $this->sql_class->create_failed();
		}
	}

	/**
	 * Attempts to insert into the test table
	 * @return void
	 */
	function test_insert() {
	    $sql = "INSERT INTO `qls3_test` (`test1`,`test2`,`test3`) VALUES('test',12345,9)";

		if (!$this->connection->query($sql)) {
		    $this->sql_class->insert_failed();
		}
	}

	/**
	 * Tries to select from the table
	 * @return void
	 */
	function test_select() {
	    $sql = "SELECT `test2` FROM `qls3_test` WHERE `test3`=9";

		if (!$result = $this->connection->query($sql)) {
		    $this->sql_class->select_failed();
		}
		else {
		    $row = $result->fetch_row();

			// Did it return 12345?
			if ($row[0] != '12345') {
			    $this->sql_class->select_failed();
			}
			else {
			    $result->free_result();
			}
		}
	}

	/**
	 * Attempts to update the row in the test table
	 * @return void
	 */
	function test_update() {
	    $sql = "UPDATE `qls3_test` SET `test1`='testagain',`test2`=123 WHERE `test3`=9";

		if (!$this->connection->query($sql)) {
		    $this->sql_class->update_failed();
		}
	}

	/**
	 * Attempts to alter the test table
	 * @return void
	 */
	function test_alter() {
	    $sql = "ALTER TABLE `qls3_test` ADD `test4` VARCHAR(20)";
	    $sql2 = "ALTER TABLE `qls3_test` DROP COLUMN `test4`";

		if (!$this->connection->query($sql)) {
		    $this->sql_class->alter_failed();
		}
		else {
            if (!$this->connection->query($sql2)) {
			    $this->sql_class->alter_failed();
			}
		}
	}

	/**
	 * Attempts to delete from the table
	 * @return void
	 */
	function test_delete() {
	    $sql = "DELETE FROM `qls3_test` WHERE `test3`=9";

		if (!$this->connection->query($sql)) {
		    $this->sql_class->delete_failed();
		}
	}

	/**
	 * Attempts to drop the test table
	 * @return void
	 */
	function test_drop_table() {
	    $sql = "DROP TABLE `qls3_test`";

		if (!$this->connection->query($sql)) {
		    $this->sql_class->drop_failed();
		}
	}

	/**
	 * Tries to connect to the database
	 * @return void
	 */
	function test_connection() {
        // Test the connection
        $this->connection = ($this->database_port !== false) ? new mysqli($this->database_server_name, $this->database_username, $this->database_password, $this->database_name, $this->database_port) : new mysqli($this->database_server_name, $this->database_username, $this->database_password, $this->database_name);

		if (!mysqli_connect_errno()) {
            // Call the functions to test functionality
            $this->test_create_table();
            $this->test_insert();
            $this->test_select();
            $this->test_update();
            $this->test_alter();
            $this->test_delete();
            $this->test_drop_table();
		}
		else {
		    $this->sql_class->connection_failed();
		}
	}

	/**
	 * Parses a SQL file
	 * @param string $file_name - The file name
	 * @return void
	 */
	function parse_sql_file($file_name) {
        $sql = $this->read_sql_file($file_name);
        $sql = explode(';', $sql);
        $query_count = count($sql);

		for ($x = 0; $x < $query_count; $x++) {
		    $sql[$x] = str_replace('{database_prefix}', $this->database_prefix, $sql[$x]);

			if (!empty($sql[$x])) {
			    $this->query($sql[$x]);
			}
		}
	}

	/**
	 * Reads a SQL file
	 * @param string $file_name - The file name
	 * @return string
	 */
	function read_sql_file($file_name) {
	    $file_name = dirname(__FILE__) . '/schemas/' . $file_name;

		if (file_exists($file_name) && is_readable($file_name)) {
			if ($file_handle = fopen($file_name, 'r')) {
                $file_data = fread($file_handle, filesize($file_name));
                fclose($file_handle);
                return $file_data;
			}
			else {
			    return false;
			}
		}
		else {
		    return false;
		}
	}

	/**
	 * Creates the necessary tables for the system
	 * @param string $database_prefix - The prefix entered by the user
	 * @return void
	 */
	function create_system_tables($database_prefix) {
        // Fetch SQL information
        $sql = $this->read_sql_file('mysql.sql');

		if ($sql === false) {
		    $this->sql_class->open_sql_file_failed();
		}

        $sql = explode(';', $sql);
        $query_count = count($sql);

		// Loop through all the SQL
		for ($x = 0; $x < $query_count; $x++) {
			$sql[$x] = str_replace('{database_prefix}', $database_prefix, $sql[$x]);

			if (!empty($sql[$x])) {
			    $this->connection->query($sql[$x]) or die($this->connection->error);
			}
		}
	}

	/**
	 * Kills the script and outputs the error
	 * @return void
	 */
	function output_error() {
	    die($this->last_error_number . ': ' . $this->last_error);
	}

	/**
	 * Sets the error variables to output an error
	 * @return void
	 */
	function error() {
        $this->last_error = $this->connection->error;
        $this->last_error_number = $this->connection->errno;
	}

	/**
	 * Fetches an array from a result resource
	 * @param resource $result - The result
	 * @return array|bool
	 */
	function fetch_array($result) {
	    return mysqli_fetch_array($result);
	}

	/**
	 * Runs a SQL query on the database (same as in includes/MySQLie.class.php)
	 * @param string $query - A SQL query
 	 * @return resource|bool
	 */
	function query($query) {
		if ($query != '') {
			// Run and check if true or false
			if ($result = $this->connection->query($query)) {
			    return $result;
			} else {
				error_log($query);
                $this->error();
                return false;
			}
		} else {
			error_log($query);
            // Find the error for no query
            $result = $this->connection->query('');
            $this->error();
            return false;
		}
	}
}