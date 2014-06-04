<?php
class Errors
{
    /**
     * 0 - Friendly error messages
     * 1 - Error details
     * 10 - Full backtrace
     * 
     * @var int $errorLevel
     */
    public static $errorLevel = ERROR_LEVEL;
    
    /**
     * @var string $email
     */
    public static $email = ERROR_EMAIL;
    
    /**
     * @var string $devEnv
     */
    public static $devEnv = DEVELOPMENT_ENVIRONMENT;

    /**
     * debugLogger
     *
     * Logs message to debug log on server for live tracing of issues
     * 
     * @param string $string Message to log
     * @param int $reportLevel Will only log this msg if this report level is lower than or equal to the current error level
     * @param boolean $debugLevelOverride If set to true will log regardless of master debug level settings
     */
    public static function debugLogger($string = '', $reportLevel = NULL, $override = NULL)
    {
        // Default to info level if not specified otherwise
        // To hide unwanted messages, increase the reportLevel on the call to debugLogger for that method
        if (empty($reportLevel)) {
            $reportLevel = 1;
        }

        // Uncomment to show on screen instead of just in log
        if (self::$devEnv === TRUE && self::$errorLevel >= 10) {
            //var_dump($string);
        }
      
        // Check global debug level settings compared to this alert to log or not
        // also allows for overriding for debugging specific methods
        if (self::$errorLevel < $reportLevel && empty($override)) {
            return;
        }

        // Rotate logs when reach size limit
        $log        = ROOT . DS . 'tmp' . DS . 'logs' . DS . 'Debug.log';
        $size_limit = (4*1048576); // 1 MB = 1048576 Bytes
        if (is_file($log) && (filesize($log) >= $size_limit)) {
            rename($log, $log . time() . '.log');
        }
        // If args = array, serialize for writing to log
        if (is_array($string)) {
            $string = '(Serialized:) ' . serialize($string);
        }
        file_put_contents($log, PHP_EOL . time() . ") " . $string, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * dbLogger
     * 
     * Record message into database log table
     * 
     * @category  Error handling
     * @version   Release: v1.0.1
     * 
     * @param string $msg Message to record
     * @param int $storeID Optional Brand ID to associate message with, 1 if null
     * @param string $type Optional Type of message to classify, info if null
     * @param string $file Optional File source
     * @param string $line Optional Line source
     * 
     * @return boolean
     */
    public function dbLogger($msg, $brandID = NULL, $type = NULL, $file = NULL, $line = NULL)
    {
        try
        {
            if (empty($this->DB))
            {
                Errors::debugLogger('Unable to write to messageLog (No DB): '.$msg, 0, TRUE);
                return;
            }
            $this->sql     = "
                INSERT INTO messageLog
                (messageDateTime, messageBrandID, messageType, messageBody, messageFile, messageLine)
                VALUES
                (:messageDateTime, :messageBrandID, :messageType, :messageBody, :messageFile, :messageLine)";
            $this->sqlData = array(':messageDateTime' => Utility::getDateTimeUTC(),
                ':messageBrandID'  => $brandID,
                ':messageType'     => $type,
                ':messageBody'     => $msg,
                ':messageFile'     => $file,
                ':messageLine'     => $line);
            return $this->DB->query($this->sql, $this->sqlData);
        }
        catch (PDOException $pe)
        {
            Errors::debugLogger('Unable to write to messageLog (PDO: '.$pe->getMessage().'): '.$msg, 0, TRUE);
        }
        catch (Exception $e)
        {
            Errors::debugLogger('Unable to write to messageLog ('.$e->getMessage().'): '.$msg, 0, TRUE);
        }
    }
    
    /**
     * captureNormal
     *
     * Error handler for normal errors
     *
     * @param int $number Error code
     * @param string $message Error message
     * @param string $file Filename where error was raised
     * @param type $line Line number where error was raised
     * 
     * @return boolean
     */
    public static function captureNormal($number, $message, $file, $line)
    {
        self::debugLogger(__METHOD__, 1, true);
        $visitorIP = Utility::getVisitorIP();
        
        // Determine Type
        switch ($number) {
            case E_USER_ERROR:
                $subject = 'Error (E_USER)';
                break;
            case E_USER_WARNING:
                $subject = 'Warning (E_USER)';
                break;
            case E_USER_NOTICE:
                $subject = 'Notice (E_USER)';
                break;
            case E_ERROR:
                $subject = 'Error (PHP)';
                break;
            case E_WARNING:
                $subject = 'Warning (PHP)';
                break;
            case E_NOTICE:
                $subject = 'Notice (PHP)';
                break;
            default:
                $subject = 'Unknown (' . $number . ')';
                break;
        }
        // TXT Body
        $txtBody  = "
            [" . $subject . "]
            [Message:]" . $message . "
            [File:]" . $file . "
            [Line:]" . $line . "
            [Visitor:] " . $visitorIP;
        // HTML Body
        $htmlBody = "
		<table border='1' cellpadding='5' cellspacing='0'>
			<caption><h4>" . $subject . "</h4></caption>
			<tr>
				<td>Message:</td><td>" . $message . "</td>
			</tr>
			<tr>
				<td>File:</td><td>" . $file . "</td>
			</tr>
			<tr>
				<td>Line:</td><td>" . $line . "</td>
			</tr>
      <tr>
        <td>Visitor:</td><td>" . $visitorIP . "</td>
      </tr>
		</table>";
        
        return self::handleErrorMessage($txtBody, $htmlBody, $subject, $message, $file, $line);
    }

    /**
     * captureException
     *
     * Error handler for exceptions thrown
     *
     * @param exception $exception
     * 
     * @return boolean
     */
    public static function captureException($exception)
    {
        self::debugLogger(__METHOD__, 1, true);
        $visitorIP = Utility::getVisitorIP();
        $message  = $exception->getMessage();
        $file     = $exception->getFile();
        $line     = $exception->getLine();
        $trace    = $exception->getTraceAsString();
        $xdebug   = $exception->xdebug_message;
        $subject  = 'Exception';
        $txtBody  = "
            [Exception]
            [Message:]" . $message . "
            [File:]" . $file . "
            [Line:]" . $line . "
            [Visitor:] " . $visitorIP . "
            [Trace:]" . $trace . "
            [XDebug:]" . $xdebug . "
            ";
        $htmlBody = "
		<table border='1' cellpadding='5' cellspacing='0'>
			<caption><h4>".$subject."<h4></caption>
			<tr>
				<td>Message:</td><td>" . $message . "</td>
			</tr>
			<tr>
				<td>File:</td><td>" . $file . "</td>
			</tr>
			<tr>
				<td>Line:</td><td>" . $line . "</td>
			</tr>
      <tr>
        <td>Visitor:</td><td>" . $visitorIP . "</td>
      </tr>
			<tr>
				<td>Trace:</td><td><pre>" . $trace . "</pre></td>
			</tr>
			<tr>
				<td>XDebug:</td><td>" . $xdebug . "</td>
			</tr>
      <tr>
      <td colspan='2'>/end</td></tr>
		</table>";

        return self::handleErrorMessage($txtBody, $htmlBody, $subject, $message, $file, $line);
    }

    /**
     * captureShutdown
     *
     * Error handler for shutdown errors
     *
     * @return boolean
     */
    public static function captureShutdown()
    {
        if ($error = error_get_last()) {
            self::debugLogger(__METHOD__, 1, true);
            $visitorIP = Utility::getVisitorIP();
            $subject  = 'Shutdown';
            $txtBody  = "
                [Shutdown]
                [Message:]" . $error['message'] . "
                [File:]" . $error['file'] . "
                [Line:]" . $error['line'] . "
                [Visitor:] " . $visitorIP . "
                [Type:]" . $error['type'];
            $htmlBody = "
                <table border='1' cellpadding='5' cellspacing='0'>
                <caption><h4>".$subject."</h4></caption>
                <tr>
                        <td>Message:</td><td>" . $error['message'] . "</td>
                </tr>
                <tr>
                        <td>File:</td><td>" . $error['file'] . "</td>
                </tr>
                <tr>
                        <td>Line:</td><td>" . $error['line'] . "</td>
                </tr>
                <tr>
                        <td>Visitor:</td><td>" . $visitorIP . "</td>
                </tr>
                <tr>
                        <td>Type:</td><td>" . $error['type'] . "</td>
                </tr>
                </table>";

            return self::handleErrorMessage($txtBody, $htmlBody, $subject, $error['message'], $error['file'], $error['line']);
        }
    }
    
    /**
     * 
     * @param type $txtBody
     * @param type $htmlBody
     * @param type $subject
     * @param type $message
     * @param type $file
     * @param type $line
     * @return boolean True: prevent PHP handler (doesnt show xdebug msg) | False: continue to PHP handler (shows xdebug msg)
     */
    public static function handleErrorMessage($txtBody, $htmlBody, $subject, $message, $file, $line)
    {
        /* Message Handling */
        $brandID = NULL;
        if (defined("BRAND_ID")) $brandID = BRAND_ID; # Needed in case error is db related
        
        // Special ignore case for PDO construct Notices: (moved to warning/notice section below changing halt to true)
#        if (preg_match('/MySQL server has gone away/', $message)
#                || preg_match('/An established connection was aborted by the software in your host machine/', $message))
#        {
#            self::debugLogger("Skipping DB notice '".$message."'");
#            // return false; // false - shows xdebug (continues to php handler)
#            return true; // true - doesnt show xdebug (prevents continuing to php handler)
#        }
        
        // Record in cartDebug.log
        self::debugLogger($txtBody);
        
        // Return halt status:
        $halt = false;
        if (preg_match('/Warning/', $subject)
                || preg_match('/Notice/', $subject))
        {
            $halt = true;
        }
        
        // If in debug mode: show detailed message (unless using XDebug)
        if (self::$devEnv === TRUE) {
            
            // Custom output
            if (!USE_XDEBUG_OUTPUT)
            {
                if (self::$errorLevel > 0) {
                    echo $htmlBody;
                }
                if (self::$errorLevel == 10) {
                    echo '<h4>Custom debug_backtrace():</h4>';
                    var_dump(debug_backtrace());
                    echo '<hr />';
                }
            }
            
        // If in live mode: email alert and display friendly error message
        } else {
        
            // Email error
            $emailError = ERROR_EMAIL;
            Email::sendEmail($emailError, $emailError, $emailError, NULL, NULL, $subject, $htmlBody);
            
            // Record error in database (last because if db issue then rest of things can complete before this fails)
            $e = new self();
            $e->dbLogger($message, $brandID, $subject, $file, $line);
            
            // Halt
            if ($halt === TRUE) {
                die("
                    <div class='failure'>
                        <h4>Sorry!</h4>
                        We're very sorry but there seems to be an issue with your request. Details have been logged and emailed to the administrator. Click <a href='javascript:history.go(-1)'>here</a> to go back.
                    </div>
                ");
            }
        }
        
        // Returning true or false changes how script ends (whether php handlers take over or not... look up in manual)
        return $halt;
    }
}
?>