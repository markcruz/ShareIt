<?php
/**
 * Debug static class.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2004, 2008 David Grudl. 
 */
final class NDebug
{
	const TMP_LIMIT_FILE = "/temp/last_error_report";
	private static $enabled = FALSE;
	public static $html;
	public static $display = TRUE;
	public static $logDir;  // TODO: or $logFileMask ?
	public static $email;
	public static $emailSubject = 'PHP error report';
	public static $keysToHide = array('password', 'passwd', 'pass', 'pwd', 'creditcard', 'credit card', 'cc', 'pin');

	/**
	 * Dumps variable output and formats it according to the internal setting in self::$html
	 *
	 * @param  mixed  variable to dump
	 * @param  bool   display output
	 * @return string
	 */
	public static function varDumpFmt($var, $displayOutput = FALSE)
	{
		ob_start();
		var_dump($var);
		$output = ob_get_clean();

		if (self::$html) {
			$output = htmlspecialchars($output, ENT_NOQUOTES);
			$output = preg_replace('#\]=&gt;\n\ +([a-z]+)#i', '] => <span>$1</span>', $output);
			$output = preg_replace('#^([a-z]+)#i', '<span>$1</span>', $output);
			$output = "<pre class=\"dump\">$output</pre>\n";
		} else
			$output = preg_replace('#\]=>\n\ +#i', '] => ', $output) . "\n";

		if ($displayOutput)
			echo $output;

		return $output;
	}

	/**
	 * Returns the number of seconds since the last call of timer(), or 0 on the first call
	 *
	 * @return int seconds
	 */
	public static function timer()
	{
		static $time = 0;

		$now = microtime(TRUE);
		$delta = $now - $time;
		$time = $now;
		return $delta;
	}

	/**
	 * Sets both error handler and exception handler to self::errorHandler and self::exceptionHandler
	 *
	 * @param  int   error_reporting level
	 * @return void
	 */
	public static function enable($level = NULL)
	{
		if ($level !== NULL) 
			error_reporting($level);
		
		set_error_handler(array(__CLASS__, 'errorHandler'));
		set_exception_handler(array(__CLASS__, 'exceptionHandler'));
		self::$enabled = TRUE;
	}

	/**
	 * Tries to restore the previous error and exception handlers
	 *
	 * @return void
	 */
	public static function disable()
	{
		if (self::$enabled) {
			restore_error_handler();
			restore_exception_handler();
			self::$enabled = FALSE;
		}
	}

	/**
	 * Returns whether or not the error and exception handlers are set
	 *
	 * @return void
	 */
	public static function isEnabled()
	{
		return self::$enabled;
	}

	/**
	 * Internal exception handler
	 *
	 * @param  Exception
	 * @return void
	 */
	public static function exceptionHandler(Exception $exception)
	{
		self::disable();
		if (self::$html)
			self::handleMessage(self::displayErrorTemplate($exception), true, true);
		else
			self::handleMessage($exception->__toString(), true, true);

		die();
	}

	/**
	 * Error handler
	 *
	 * @param  int    level of the error raised
	 * @param  string error message
	 * @param  string filename that the error was raised in
	 * @param  int    line number the error was raised at
	 * @param  array  an array of variables that existed in the scope the error was triggered in
	 * @return void
	 */
	public static function errorHandler($code, $message, $file, $line, $context)
	{
		$custom_error_reporting = E_WARNING | E_CORE_WARNING | E_COMPILE_WARNING | E_USER_WARNING;
		$fatals = array(
		E_ERROR => 'Fatal error', // unfortunately not catchable
		E_CORE_ERROR => 'Fatal core error', // not catchable
		E_COMPILE_ERROR => 'Fatal compile error', // unfortunately not catchable
		E_USER_ERROR => 'Fatal error',
		E_PARSE => 'Parse error', // unfortunately not catchable
		E_RECOVERABLE_ERROR => 'Catchable fatal error', // since PHP 5.2
		);

		$msg = "'$message' in $file on line $line";
		
		if (isset($fatals[$code])) {
			self::disable();

			$trace = debug_backtrace();
			array_shift($trace);
			$type = $fatals[$code];
			
			if (self::$html)
				self::handleMessage(self::displayErrorTemplate(NULL, $type, $code, $message, $file, $line, $trace, $context));
			else
				self::handleMessage($msg);

			die();
		}

		if (($code & error_reporting()) === $code) {
			$types = array(
				E_WARNING => 'Warning',
				E_CORE_WARNING => 'Core warning', // not catchable
				E_COMPILE_WARNING => 'Compile warning', // not catchable
				E_USER_WARNING => 'Warning',
				E_NOTICE => 'Notice',
				E_USER_NOTICE => 'Notice',
				E_STRICT => 'Strict standards',
			);
			$type = isset($types[$code]) ? $types[$code] : 'Unknown error';

			if (!self::$email && self::$html && ($code & error_reporting()) === $code) {
				$message = "<b>$type:</b> $message in <b>$file</b> on line <b>$line</b>\n<br>";
				echo $message;
			} else {
				$message = $msg;
			}

			if (!self::$email && self::$display && ($code & error_reporting()) === $code) {
				echo $message;

				if (self::$logDir)
					error_log($message);
			} else if (($code & $custom_error_reporting) === $code) {
				$trace = debug_backtrace();
				array_shift($trace);
				self::handleMessage(self::displayErrorTemplate(NULL, $type, $code, $message, $file, $line, $trace, $context), $code != E_WARNING && $code != E_USER_WARNING);
			}
		}
	}

	/**
	 * Handles error message.
	 *
	 * @param  string
	 * @param  bool unrecoverable (optional) defaults to TRUE
	 * @param  bool is exception (optional) defaults to FALSE
	 * @return void
	 */
	private static function handleMessage($message, $unrecoverable = TRUE, $exception = FALSE)
	{
		if (!self::limitsCanReportIssue($message, $unrecoverable, $exception))
			return;
		
		if (isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME']))
			$error_origin = $_SERVER['SERVER_NAME'];
		else
			$error_origin = sprintf("CRON script: %s ", trim(ltrim($_SERVER['SCRIPT_FILENAME'], "./"))); 
		 
		if (!$unrecoverable)
			self::$emailSubject = sprintf("[%s] PHP warning report", $error_origin);
		else if ($exception)
			self::$emailSubject = sprintf("[%s] PHP exception report", $error_origin);
		else
			self::$emailSubject = sprintf("[%s] PHP error report", $error_origin);
		
		if (!headers_sent())
			header('HTTP/1.1 500 Internal Server Error');
		
		if (self::$logDir) {
			$file = self::$logDir . '/report ' . date('Y-m-d H-i-s ') . substr(microtime(FALSE), 2, 6) . (self::$html ? '.html' : '.txt');
			file_put_contents($file, $message);
		}

		if (self::$display && !self::$email) {
			while (ob_get_level() && ob_end_clean());

			echo $message;

			// fix for IE 6
			if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) {
				$s = " \t\r\n";
				for ($i = 2e3; $i; $i--)
					echo $s[rand(0, 3)];
			}
		} else if (self::$email) {
			// pro mailer v unixu je treba zamenit \r\n na \n, protoze on si to pak opet zameni za \r\n
			$message = str_replace("\r\n", "\n", $message);
			if (PHP_OS != 'Linux')
				$message = str_replace("\n", "\r\n", $message);

			if (self::limitsCanSendMail())
				mail(self::$email, self::$emailSubject, $message, "Content-Type: text/html; charset=utf-8");
			
			if (!$unrecoverable)
				return;
			
			if (PHP_SAPI !== 'cli') {
				// XXX html user-friendly error message should be displayed
				echo _("An error occurred. We apologize for the inconvenience.");
			} else
				echo _("An error occurred. We apologize for the inconvenience.");
			
			die();
		}
	}

	/**
	 * Returns the output of Debug.template.phtml file
	 *
	 * @return string
	 */
	public static function displayErrorTemplate($exception, $type = NULL, $code = NULL, $message = NULL, $file = NULL, $line = NULL, $trace = NULL, $context = NULL)
	{
		if ($exception) {
			$type = get_class($exception);
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$file = $exception->getFile();
			$line = $exception->getLine();
			$trace = $exception->getTrace();
		}

		ob_start();
		require dirname(__FILE__) . '/Debug.template.phtml';
		return ob_get_clean();
	}

	/**
	 * Filters all possibly sensitive information out of the output of self::varDumpFmt()
	 *
	 * @param  string  content
	 * @param  string  additional key
	 * @return void
	 */
	private static function filter($content, $key = NULL)
	{
		if ($key !== NULL && array_search(strtolower($key), self::$keysToHide, TRUE))
			return '<i>*** hidden ***</i>';

		return preg_replace(
            '#^(\s*\["(' . implode('|', self::$keysToHide) . ')"\] => <span>string</span>).+#mi',
            '$1 (?) <i>*** hidden ***</i>',	$content
		);
	}
	
	/**
	 * Limits how often an email can be sent. The original interval was 5 minutes, yet now it's been adjusted to 30 seconds.
	 * 
	 * @return bool email can be sent
	 */
	protected static function limitsCanSendMail()
	{
		$filename = ROOT_PATH . self::TMP_LIMIT_FILE;
		if (file_exists($filename)) {
			$limit = file_get_contents($filename);
			if ($limit > time())
				return FALSE;
		}

		file_put_contents($filename, strtotime("+30 seconds"));
		return TRUE;
	}
	
	/**
	 * Limits error reporting based on its content and importance. Basically we never want to know 
	 * file_get_contents() or fopen() failed to work with a URL. This function searches for pre-defined 
	 * needles in the $message haystack. 
	 * 
	 * @param  string message
	 * @param  bool   unrecoverable
	 * @param  bool   exception
	 * @return bool   TRUE = can be reported
	 */
	protected static function limitsCanReportIssue($message, $unrecoverable, $exception)
	{
		static $needles = array(
			"fopen(" => "http",
			"file_get_contents(" => "http"
		);
		foreach ($needles as $needle1 => $needle2)
			if (FALSE !== strpos($message, $needle1) && FALSE !== strpos($message, $needle2) && !$unrecoverable && !$exception)
				return FALSE;
		
		return TRUE;
	}
}

NDebug::$html = TRUE; // we want HTML error messages even if an error happens in the CLI mode. PHP_SAPI !== 'cli';
if (!defined('E_RECOVERABLE_ERROR'))
	define('E_RECOVERABLE_ERROR', 4096);
?>