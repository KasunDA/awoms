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
    public static $errorEmail = ERROR_EMAIL;
    
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
     * @param int $cartID Optional Brand ID to associate message with, 1 if null
     * @param string $type Optional Type of message to classify, info if null
     * @param string $file Optional File source
     * @param string $line Optional Line source
     * 
     * @return boolean
     */
    private function dbLogger($msg, $brandID = NULL, $cartID = NULL, $type = NULL, $file = NULL, $line = NULL)
    {
        try
        {
            if (self::$devEnv) return;
            if ($brandID == NULL) $brandID = 1;
            if (empty($this->DB))
            {
                $this->DB = new \Database();
                #Errors::debugLogger('Unable to write to messageLog (No DB): '.$msg, 0, TRUE);
                #return false;
            }
            $this->sql     = "
                INSERT INTO messageLog
                (messageDateTime, brandID, messageCartID, messageType, messageBody, messageFile, messageLine)
                VALUES
                (:messageDateTime, :brandID, :messageCartID, :messageType, :messageBody, :messageFile, :messageLine)";
            $this->sqlData = array(':messageDateTime' => Utility::getDateTimeUTC(),
                ':brandID'  => $brandID,
                ':messageCartID' => $cartID,
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
        $error = error_get_last();
        if ($error) {
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

        // Record in cartDebug.log
        self::debugLogger($txtBody);

        $supressError = FALSE;
        // Notices shhh
        if (preg_match('/Notice/', $subject))
        {
            $supressError = TRUE;
        }
        
        // Show all errors/notices during dev
        if (self::$devEnv)
        {
            $supressError = FALSE;
            // Custom output w/o xdebug
            if (!USE_XDEBUG_OUTPUT)
            {
                if (self::$errorLevel > 0) {
                    $_SESSION['ErrorMessage'] = "
                    <div class='alert error'>
                        ".$htmlBody."
                    </div>
                    ";

                    if (self::$errorLevel == 10) {
                        $backtrace = var_export(debug_backtrace(), true);
                        $_SESSION['ErrorMessage'] .= "
                            <div class='alert error'>
                                <h3>Custom debug_backtrace():</h3>
                                <pre>".$backtrace."</pre>
                            </div>";
                    }
                }
                return true;
            }
            // Return False here lets PHP/XDebug take over and show us all warnings in dev mode
            // Commenting this out will go to email/db error and show error as in live mode
            return false; 
        }

        // Email error
        if (!preg_match('/Failed to connect to mailserver/', $message))
        {
            $emailError = self::$errorEmail;
            Email::sendEmail($emailError, $emailError, $emailError, NULL, NULL, $subject, $htmlBody);
        }

        // Record error in database (last because if db issue then rest of things can complete before this fails)
        $e = new self();
        $cartID = NULL;
        $e->dbLogger($message, $brandID, $cartID, $subject, $file, $line);

        // Display error or not?
        if ($supressError === FALSE)
        {
            $_SESSION['ErrorMessage'] = "
                <div class='alert error'>
                    <h2>Sorry!</h2>
                    <p>We're very sorry but there seems to be an issue with your request. Details have been logged and emailed to the administrator.</p>
                    <p>Click <a href='javascript:history.go(-1)'>here</a> to go back.</p>
                </div>
            ";
        }
        
        // Always returning true to skip PHP error handler and allow template to finish
        return true;
    }
}
?>