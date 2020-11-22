<?php
/*** *** *** *** *** ***
* @package Quadodo Login Script
* @file    Test.class.php
* @start   August 1st, 2007
* @author  Douglas Rennehan
* @license http://www.opensource.org/licenses/gpl-license.php
* @version 1.0.6
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
 * This class will setup a test connection and run queries to
 * setup the necessary tables.
 */
class Test {

/**
 * @var string $go_back - Contains the go back link when an error is output
 */
var $go_back = '<br /><br />This may mean your permissions are not correct. Please try to fix the problem and <a href="install.php">try again</a>. If you don\'t understand the problem please post the problem in the <a href="http://www.quadodo.net/s.php">Support Area</a>.';

	/**
	 * Constructs the class and loads the SQL file
	 * @param string $database_server_name
	 * @param string  $database_username
	 * @param string  $database_password
	 * @param string  $database_name
	 * @param integer $database_port
	 * @param string  $database_type
	 */
	function Test($database_server_name, $database_username, $database_password, $database_name, $database_port, $database_type) {
    	// Grab the file
    	require_once('MySQLie.class.php');

		// Find the database type and assign to $current_layer
		switch ($database_type) {
			default:
			case 'MySQLi':
                $this->current_layer = new MySQLie($database_server_name,
                    $database_username,
                    $database_password,
                    $database_port,
                    $database_name,
                    $this
                );
                break;
		}
	}

	/**
	 * Connection failed
	 * @return void
	 */
	function connection_failed() {
	    die('Could not connect to the database.' . $this->go_back);
	}

	/**
	 * Could not create the table
	 * @return void
	 */
	function create_failed() {
	    die('Could not create a table in the database.' . $this->go_back);
	}

	/**
	 * Could not insert into the table
	 * @return void
	 */
	function insert_failed() {
        $this->drop_table();
        die('Could not insert into the created table.' . $this->go_back);
	}

	/**
	 * Could not select from table
	 * @return void
	 */
	function select_failed() {
        $this->drop_table();
        die('Could not select from the created table.' . $this->go_back);
	}

	/**
	 * Could not update the table
	 * @return void
	 */
	function update_failed() {
        $this->drop_table();
        die('Could not run an update query on the table.' . $this->go_back);
	}

	/**
	 * Could not alter the table
	 * @return void
	 */
	function alter_failed() {
        $this->drop_table();
        die('Could not run an alter query on the table.' . $this->go_back);
	}

	/**
	 * Could not delete from the table
	 * @return void
	 */
	function delete_failed() {
        $this->drop_table();
        die('Could not delete the row in the created table.' . $this->go_back);
	}

	/**
	 * Could not drop the table
	 * @return void
	 */
	function drop_failed() {
	    die('Could not drop the table from the database.' . $this->go_back);
	}

	/**
	 * Reading the SQL schemas failed
	 * @return void
	 */
	function open_sql_file_failed() {
	    die('The SQL file could not be opened! Please CHMOD the files in the <b>install/schemas</b> directory to 755.' . $this->go_back);
	}

	/**
	 * These functions will run the functions found in the SQL
	 * files. See those files for more information
	 */
	function test_create_table() {
	    $this->current_layer->test_create_table();
	}

	function test_insert() {
	    $this->current_layer->test_insert();
	}

	function test_select() {
	    $this->current_layer->test_select();
	}

	function test_update() {
	    $this->current_layer->test_update();
	}

	function test_alter() {
	    $this->current_layer->test_alter();
	}

	function test_delete() {
	    $this->current_layer->test_delete();
	}

	function test_drop_table() {
	    $this->current_layer->test_drop_table();
	}

	function test_connection() {
	    $this->current_layer->test_connection();
	}

	function parse_sql_file($file_name) {
        $this->current_layer->parse_sql_file($file_name);
	}

	function read_sql_file($file_name) {
	    return $this->current_layer->read_sql_file($file_name);
	}

	function create_system_tables($database_prefix) {
	    $this->current_layer->create_system_tables($database_prefix);
	}

	function output_error() {
	    $this->current_layer->output_error();
	}

    function error() {
	    $this->current_layer->error();
	}

	function fetch_array($result) {
	    return $this->current_layer->fetch_array($result);
	}

	function query($query) {
	    return $this->current_layer->query($query);
	}
}