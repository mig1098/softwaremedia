<?php
/**
 * @package Kount_Log
 * @subpackage Binding
 */

/**
 * Logger interface.
 *
 * @package Kount_Log
 * @subpackage Binding
 * @author Kount <custserv@kount.com>
 * @version $Id: Logger.php 18991 2012-03-27 22:47:11Z bpd $
 * @copyright 2012 Kount, Inc. All Rights Reserved.
 */
interface Kount_Log_Binding_Logger {

	/**
	 * Log a debug level message.
	 * @param string $message Message to log
	 * @param Exception $exception Exception to log
	 * @return void
	 */
	public function debug ($message, $exception=null);

	/**
	 * Log an info level message.
	 * @param string $message Message to log
	 * @param Exception $exception Exception to log
	 * @return void
	 */
	public function info ($message, $exception=null);

	/**
	 * Log a warn level message.
	 * @param string $message Message to log
	 * @param Exception $exception Exception to log
	 * @return void
	 */
	public function warn ($message, $exception=null);

	/**
	 * Log an error level message.
	 * @param string $message Message to log
	 * @param Exception $exception Exception to log
	 * @return void
	 */
	public function error ($message, $exception=null);

	/**
	 * Log a fatal level message.
	 * @param string $message Message to log
	 * @param Exception $exception Exception to log
	 * @return void
	 */
	public function fatal ($message, $exception=null);

} // end Kount_Log_Binding_Logger
