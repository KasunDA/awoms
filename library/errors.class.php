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
     * @param int $errorLevel
     *  10 = Full info
     * @param mixed $data
     */
    public static function debugLogger($errorLevel, $data) {
      if (self::$devEnv === TRUE) {
        if (self::$errorLevel == 10) {
          var_dump($data);
        }
        return;
      }
      
      // Txt file or database for non-devenv?
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
        // Determine Type
        switch ($number) {
            case E_USER_ERROR:
                $subject = 'User Error';
                break;
            case E_USER_WARNING:
                $subject = 'User Warning';
                break;
            case E_USER_NOTICE:
                $subject = 'User Notice';
                break;
            case E_ERROR:
                $subject = 'Error';
                break;
            case E_WARNING:
                $subject = 'Warning';
                break;
            case E_NOTICE:
                $subject = 'User Notice';
                break;
            default:
                $subject = 'Unknown Error Type';
                break;
        }
        // TXT Body
        $txtBody  = "
            [" . $subject . "]
            [Message:]" . $message . "
            [File:]" . $file . "
            [Line:]" . $line . "
            [Visitor:] " . $_SERVER['REMOTE_ADDR'];
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
        <td>Visitor:</td><td>" . $_SERVER['REMOTE_ADDR'] . "</td>
      </tr>
		</table>";
        // Message handling
        if (self::$devEnv === TRUE) {
          if (self::$errorLevel > 0) {
              echo $htmlBody;
              if (self::$errorLevel == 10) {
                echo "<h4>debug_backtrace()</h4>";
                var_dump(debug_backtrace());
              }
              return false; // false = stops script, true = continues... use with caution
          }
        } else {
            Email::sendEmail(self::$email, self::$email, self::$email, NULL, NULL, $subject, $htmlBody);
            echo "
                <div class='alert alert-block alert-error span6 offset3'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <h4>Sorry!</h4>
                    We're very sorry but there seems to be an issue with your request. Details have been logged and emailed to the administrator.
                </div>
            ";
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
            [Visitor:] " . $_SERVER['REMOTE_ADDR'] . "
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
        <td>Visitor:</td><td>" . $_SERVER['REMOTE_ADDR'] . "</td>
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
        // Message handling
        if (self::$devEnv === TRUE) {
          if (self::$errorLevel > 0) {
              echo $htmlBody;
              if (self::$errorLevel == 10) {
                echo '<h4>debug_backtrace()</h4>';
                var_dump(debug_backtrace());
              }
              return false; // false = stops script, true = continues... use with caution
          }
        } else {
            Email::sendEmail(self::$email, self::$email, self::$email, NULL, NULL, $subject, $htmlBody);
            die("
                <div class='alert alert-block alert-error span6 offset3'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    <h4>Oops!</h4>
                    We're very sorry but there seems to be an issue with your request. Details have been logged and emailed to the administrator.
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
            $subject  = 'Shutdown';
            $txtBody  = "
                [Shutdown]
                [Message:]" . $error['message'] . "
                [File:]" . $error['file'] . "
                [Line:]" . $error['line'] . "
                [Visitor:] " . $_SERVER['REMOTE_ADDR'] . "
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
                        <td>Visitor:</td><td>" . $_SERVER['REMOTE_ADDR'] . "</td>
                </tr>
                <tr>
                        <td>Type:</td><td>" . $error['type'] . "</td>
                </tr>
                </table>";
            // Message handling
            if (self::$devEnv === TRUE) {
              if (self::$errorLevel > 0) {
                  echo $htmlBody;
                  if (self::$errorLevel == 10) {
                    echo '<h4>debug_backtrace()</h4>';
                    var_dump(debug_backtrace());
                  }
                  return false; // false = stops script, true = continues... use with caution
              }
            } else {
                Email::sendEmail(self::$email, self::$email, self::$email, NULL, NULL, $subject, $htmlBody);
                die("
                    <div class='alert alert-block alert-error span6 offset3'>
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                        <h4>Error!</h4>
                        We're very sorry but there seems to be an issue with your request. Details have been logged and emailed to the administrator.
                    </div>
                ");
            }
            // Don't execute PHP internal error handler
            return true;
        }
    }

}
?>