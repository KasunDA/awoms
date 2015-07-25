<?php
namespace killerCart;

/**
 * Sanitize class
 */
class Sanitize
{
    /**
     * Return data if any input fails filter
     */
    public $invalid;

    /**
     * filterArray
     * 
     * Filters provided array through filter_input_array with provided arguments list of FILTERS per value
     * 
     * @version v0.0.1
     * 
     * @uses Util\convertNLToBR
     *
     * @param string $inp = Where to run filter_input_array against (e.g. INPUT_POST)
     * @param array $args = Array of sanitization options
     * 
     * @return bool = True if valid, False if any failed filter. Retrieve the invalid params through $this->invalid
     *
     * @example usage:
     *      $args = array(
     *          'inpName'=>FILTER_SANITIZE_SPECIAL_CHARS,
     *          'email'=>FILTER_VALIDATE_EMAIL,
     *          'price'=>array('filter' => FILTER_SANITIZE_NUMBER_FLOAT,
     *              'flags' => FILTER_FLAG_ALLOW_FRACTION));
     *      $s = new Sanitize();
     *      if (!$san = $s->verify_data(INPUT_POST, $args)) {
     *          var_dump($san->invalid); // Failed, failed values in $san->invalid marked as null
     *      } else {
     *          var_dump($san); // Passed, clean values in $san array
     *      }
     */
    public function filterArray($inp, $args)
    {
        \Errors::debugLogger(__METHOD__, 10);
        // Pass input and rules through filter
        $san = filter_input_array($inp, $args);
        // Search for any inputs that failed validation
        if (array_search(false, $san, true) !== false) {
            $this->invalid = array_map(function($a) {
                        return $a;
                    }, $san);
            // Point the finger at the one(s) who failed validation
            $msg = '';
            foreach ($this->invalid as $k => $v) {
                if ($v === false) {
                    $msg .= $k . ' ';
                }
            }
            \Errors::debugLogger(__METHOD__ . ': Validation failed: ' . $msg, 1, true);
            trigger_error('Form input @ ' . time() . ' did not pass validation! Msg: ' . $msg, E_USER_ERROR);
            return false;
        } else {
            // Convert new lines to <br />
            foreach ($san as $k => $v) {
                $san[$k] = Util::convertNLToBR($v);
            }
            return $san;
        }
    }

    /**
     * filterSingle
     * 
     * Filters provided data through filter_input with provided FILTER
     * 
     * @since v0.0.1
     * 
     * @uses Util\convertNLToBR
     * 
     * @param mixed $data Data
     * @param filter $filter Filter
     * 
     * @return boolean|string
     */
    public function filterSingle($data, $filter)
    {
        \Errors::debugLogger(__METHOD__, 10);
        $san = filter_var($data, $filter);
        if ($san === FALSE || $san === NULL) {
            \Errors::debugLogger(__METHOD__ . ': Validation failed', 1, true);
            trigger_error('Form input @ ' . time() . ' did not pass validation!', E_USER_ERROR);
            return false;
        } else {
            return Util::convertNLtoBR($san);
        }
    }

}