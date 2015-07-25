<?php
namespace killerCart;

/**
 * Error class
 */
class Error
{
    /**
     * Database connection
     * Database query sql
     * Database query parameters
     *
     * @var PDO $DB
     * @var string $sql
     * @var array $sqlData
     */
    protected $DB, $sql, $sqlData;

    /**
     * Main magic methods
     */
    public function __construct()
    {
        $this->DB = new \Database();
    }

    public function __destruct()
    {
        unset($this->DB, $this->sql, $this->sqlData);
    }

    /**
     * debugLogger
     *
     * Logs message to debug log on server for live tracing of issues
     * 
     * @param string $string Message to log
     * @param boolean $debugLevelOverride If set to true will log regardless of master debug level settings
     */
    public static function debugLogger($string = '', $reportLevel = NULL, $override = NULL)
    {
        // Default to info level if not specified otherwise
        // To hide unwanted messages, increase the reportLevel on the call to debugLogger for that method
        if (empty($reportLevel)) {
            $reportLevel = 1;
        }

        // Check global debug level settings compared to this alert to log or not
        // also allows for overriding for debugging specific methods
        if (cartDebugLevel < $reportLevel && empty($override)) {
            return;
        }

        // Make log dir if doesnt exist
        if (!is_dir(cartLogDir)) {
            Util::createNestedDirectory(cartLogDir);
        }
        $log        = cartLogDir . 'cartDebug.log';
        $size_limit = (4*1048576); // 1 MB = 1048576 Bytes
        // Rotate logs when reach size limit
        if (is_file($log) && (filesize($log) >= $size_limit)) {
            rename($log, $log . time() . '.log');
        }
        // If args = array, serialize for writing to log
        if (is_array($string)) {
            $string = '(Serialized) ' . serialize($string);
        }
        file_put_contents($log, PHP_EOL . time() . ") " . $string, FILE_APPEND | LOCK_EX);
    }

    /**
     * dbLogger
     * 
     * Record message into database log table
     * 
     * @category  Error handling
     * @package   killerCart
     * @version   v0.0.1
     * 
     * @param string $msg Message to record
     * @param int $cartID Optional Cart ID to associate message with, 1 if null
     * @param string $type Optional Type of message to classify, info if null
     * @param string $file Optional File source
     * @param string $line Optional Line source
     * 
     * @return boolean
     */
    public function dbLogger($msg, $cartID = NULL, $type = NULL, $file = NULL, $line = NULL)
    {
        if (empty($cartID))
        {
            $cartID = "NULL";
        }
        $this->sql     = "
            INSERT INTO messageLog
            (messageDateTime, messageCartID, messageType, messageBody, messageFile, messageLine)
            VALUES
            (:messageDateTime, :messageCartID, :messageType, :messageBody, :messageFile, :messageLine)";
        $this->sqlData = array(':messageDateTime' => Util::getDateTimeUTC(),
            ':messageCartID'  => $cartID,
            ':messageType'     => $type,
            ':messageBody'     => $msg,
            ':messageFile'     => $file,
            ':messageLine'     => $line);
        return $this->DB->query($this->sql, $this->sqlData);
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
        $visitorIP = Util::getVisitorIP();
        $halt = TRUE;
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
            case E_WARNING:
                $subject = 'Warning (PHP)';
                break;
            case E_NOTICE:
                $subject = 'Notice (PHP)';
                $halt    = FALSE;
                break;
            default:
                $subject = 'Unknown (' . $number . ')';
                break;
        }
        // TXT Body
        $txtBody  = "
            [" . $subject . "]
            [Message:] " . $message . "
            [File:] " . $file . "
            [Line:] " . $line . "
            [VisitorIP:] " . $visitorIP;
        // HTML Body
        $htmlBody = "
		<html><head><title>" . $subject . "</title></head><body>
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
				<td>VisitorIP:</td><td>" . $visitorIP . "</td>
			</tr>
		</table></body></html>";
        // Cart ID (if in admin section)
        if (!empty($_SESSION['cartID'])) {
            $cartID = $_SESSION['cartID'];
        } else {
            $cartID = NULL;
        }
        // Record in database
        $e = new self();
        $e->dbLogger($message, $cartID, $subject, $file, $line);
        // Record in cartDebug.log
        self::debugLogger($txtBody);
        // If in debug mode: show detailed message
        if (cartDebug) {
            echo $htmlBody;
            echo '<h4>debug_backtrace()</h4>';
            var_dump(debug_backtrace());
            return false; // false = stops script, true = continues... use with caution
            // If in live mode: email alert and display friendly error message
        } else {
            // Email notify and die with friendly error message
            if ($halt === TRUE) {
                $s          = new KillerCart();
                $emailError = $s->getCartErrorEmail(CART_ID);
                Email::sendEmail($emailError, $emailError, $emailError, NULL, NULL, $subject, $htmlBody);
                die("
                    <div class='alert alert-block alert-error span6 offset3'>
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                        <h4>Sorry!</h4>
                        We're very sorry but there seems to be an issue with your request. Details have been logged and emailed to the administrator. Click <a href='" . cartPublicUrl . "'>here</a> to return to Cart.
                    </div>
                ");
            }
        }
        // Don't execute PHP internal error handler
        return true;
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
        $visitorIP = Util::getVisitorIP();
        $message  = $exception->getMessage();
        $file     = $exception->getFile();
        $line     = $exception->getLine();
        $trace    = $exception->getTraceAsString();
        $xdebug   = $exception->xdebug_message;
        $subject  = 'Exception';
        $txtBody  = "
            [Exception]
            [Message:] " . $message . "
            [File:] " . $file . "
            [Line:] " . $line . "
            [VisitorIP:] " . $visitorIP . "
            [Trace:] " . $trace . "
            [XDebug:] " . $xdebug . "
            ";
        $htmlBody = "
		<html><head><title>" . $subject . "</title></head><body>
		<table border='1' cellpadding='5' cellspacing='0'>
			<caption><h4>Exception<h4></caption>
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
				<td>VisitorIP:</td><td>" . $visitorIP . "</td>
			</tr>
			<tr>
				<td>Trace:</td><td>" . $trace . "</td>
			</tr>
			<tr>
				<td>XDebug:</td><td>" . $xdebug . "</td>
			</tr>
		</table></body></html>";
        // Cart ID (if in admin section)
        if (!empty($_SESSION['cartID'])) {
            $cartID = $_SESSION['cartID'];
        } else {
            $cartID = NULL;
        }
        // Record in database
        $e = new self();
        $e->dbLogger($message, $cartID, $subject, $file, $line);
        // Record in cartDebug.log
        self::debugLogger($txtBody);
        // If in debug mode: show detailed message
        if (cartDebug) {
            echo $htmlBody;
            echo '<h4>debug_backtrace()</h4>';
            var_dump(debug_backtrace());
            return false; // false = stops script, true = continues... use with caution
            // If in live mode: email alert and display friendly error message
        } else {
            $s          = new KillerCart();
            $emailError = $s->getCartErrorEmail(CART_ID);
            Email::sendEmail($emailError, $emailError, $emailError, NULL, NULL, $subject, $htmlBody);
            die("
                <div class='alert alert-block alert-error span6 offset3'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <h4>Oops!</h4>
                    We're very sorry but there seems to be an issue with your request. Details have been logged and emailed to the administrator. Click <a href='" . cartPublicUrl . "'>here</a> to return to Cart.
                </div>
            ");
        }
        // Don't execute PHP internal error handler
        return true;
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
            $visitorIP = Util::getVisitorIP();
            $subject  = 'Shutdown';
            $txtBody  = "
                [Shutdown]
                [Message:] " . $error['message'] . "
                [File:] " . $error['file'] . "
                [Line:] " . $error['line'] . "
                [Type:] " . $error['type'] . "
                [VisitorIP:] " . $visitorIP;
            $htmlBody = "
                <html><head><title>" . $subject . "</title></head><body>
                <table border='1' cellpadding='5' cellspacing='0'>
                <caption><h4>Shutdown</h4></caption>
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
                        <td>Type:</td><td>" . $error['type'] . "</td>
                </tr>
                <tr>
                        <td>VisitorIP:</td><td>" . $visitorIP . "</td>
                </tr>
                </table></body></html>";
            // Cart ID (if in admin section)
            if (!empty($_SESSION['cartID'])) {
                $cartID = $_SESSION['cartID'];
            } else {
                $cartID = NULL;
            }
            // Record in database
            $e = new self();
            $e->dbLogger($error['message'], $cartID, $subject . ' (' . $error['type'] . ')', $error['file'], $error['line']);
            // Record in cartDebug.log
            self::debugLogger($txtBody);
            // If in debug mode: show detailed message
            if (cartDebug) {
                echo $htmlBody;
                echo '<h4>debug_backtrace()</h4>';
                var_dump(debug_backtrace());
                return false; // false = stops script, true = continues... use with caution
                // If in live mode: email alert and display friendly error message
            } else {
                $s          = new KillerCart();
                $emailError = $s->getCartErrorEmail(CART_ID);
                Email::sendEmail($emailError, $emailError, $emailError, NULL, NULL,
                                 $subject . ' (' . $error['type'] . ')', $htmlBody);
                die("
                        <div class='alert alert-block alert-error span6 offset3'>
                            <button type='button' class='close' data-dismiss='alert'>&times;</button>
                            <h4>Error!</h4>
                            We're very sorry but there seems to be an issue with your request. Details have been logged and emailed to the administrator. Click <a href='" . cartPublicUrl . "'>here</a> to return to Cart.
                        </div>
                    ");
                return false;
            }
            // Don't execute PHP internal error handler
            return true;
        }
    }

    /**
     * getMessageLog
     * 
     * Returns list of messages from database
     * 
     * @version v0.0.1
     * 
     * @param int $cartID Optional cart ID
     * @param int $limit Optional limit results
     * 
     * @return array
     */
    public function getMessageLog($cartID = NULL, $limit = NULL)
    {
        self::debugLogger(__METHOD__, 1, true);
        if ($cartID === NULL) {
            // All Carts
            $whereSql = 'messageID IS NOT NULL';
        } else {
            $whereSql = 'messageCartID = ' . $cartID;
        }
        if ($limit === NULL) {
            $limit = 20;
        }
        $this->sql = "
            SELECT messageID, messageDateTime, messageCartID, messageType, messageBody, messageFile, messageLine
            FROM messageLog
            WHERE " . $whereSql . "
            ORDER BY messageDateTime DESC
            LIMIT " . $limit;
        $res       = $this->DB->query($this->sql);
        return $res;
    }

}
?>