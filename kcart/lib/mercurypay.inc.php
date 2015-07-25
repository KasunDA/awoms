<?php
namespace killerCart;

/**
 * MercuryPay class
 *
 * MercuryPay payment gateway methods
 *
 * PHP version 5
 *
 * @category  Cart
 * @package   Cart
 * @author    Brock Hensley <brock@brockhensley.com>
 * @version   v0.0.1
 * @since     v0.0.1
 */
class MercuryPay extends KillerCart
{
    /**
     * __construct
     */
    public function __construct()
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->DB = new \Database();
    }

    /**
     * getSoapClient
     * 
     * Initializes SOAP client with Merchant ID and Web Services Password
     * 
     * @param string $wsdl WSDL
     * 
     * @return boolean|\SoapClient
     */
    public function getSoapClient($wsdl)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $options = array('trace'        => 1,
            'exceptions'   => true,
            'cache_wsdl'   => WSDL_CACHE_NONE,
            'features'     => SOAP_SINGLE_ELEMENT_ARRAYS,
            'soap_version' => SOAP_1_1);
        $soap    = new \SoapClient($wsdl, $options);
        if (!isset($soap)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return $soap;
    }

    /**
     * logHistory
     * 
     * Log MercuryPay history to database
     * 
     * @param int $orderID          Order ID
     * @param int $customerID       Customer ID
     * @param int $paymentMethodID  Payment method ID
     * @param string $action        Action
     * @param string $amount        Amount
     * @param SimpleXMLElement $returnCode    Return code
     * @param SimpleXMLElement $returnStatus  Return status
     * @param SimpleXMLElement $returnTextResponse Return TextResponse
     * @param string $returnMessage Return message
     * @param string $avsResult     AVS result
     * @param string $cvvResult     CVV result
     * @param string $authCode      Auth code
     * @param string $acqRefData    AcqRefData
     * @param string $refNo         RefNo
     * 
     * @return boolean
     */
    public function logHistory($orderID, $customerID, $paymentMethodID, $action, $amount, $returnCode, $returnStatus,
                               $returnTextResponse, $returnMessage, $avsResult, $cvvResult, $authCode, $acqRefData, $refNo,
                               $processData)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
            INSERT INTO mercuryPayHistory
                (orderID, customerID, paymentMethodID, dateReported, action, amount, returnCode, returnStatus, returnTextResponse,
                    returnMessage, avsResult, cvvResult, authCode, acqRefData, refNo, processData)
            VALUES
                (:orderID, :customerID, :paymentMethodID, :dateReported, :action, :amount, :returnCode, :returnStatus, :returnTextResponse,
                    :returnMessage, :avsResult, :cvvResult, :authCode, :acqRefData, :refNo, :processData)";
        $this->sqlData = array(':orderID'            => $orderID,
            ':customerID'         => $customerID,
            ':paymentMethodID'    => $paymentMethodID,
            ':dateReported'       => Util::getDateTimeUTC(),
            ':action'             => $action,
            ':amount'             => $amount,
            ':returnCode'         => $returnCode,
            ':returnStatus'       => $returnStatus,
            ':returnTextResponse' => $returnTextResponse,
            ':returnMessage'      => $returnMessage,
            ':avsResult'          => $avsResult,
            ':cvvResult'          => $cvvResult,
            ':authCode'           => $authCode,
            ':acqRefData'         => $acqRefData,
            ':refNo'              => $refNo,
            ':processData'        => $processData);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * getHistory
     * 
     * Gets transaction history used primarily for looking up declined transaction reason
     * 
     * @param type $orderID Order ID
     * @param type $customerID Customer ID
     * 
     * @return boolean|array History details
     */
    public function getHistory($orderID, $customerID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT
                paymentMethodID, dateReported, action, amount, returnCode, returnStatus, returnTextResponse,
                    returnMessage, avsResult, cvvResult, authCode, acqRefData, refNo, processData
            FROM mercuryPayHistory
            WHERE orderID = :orderID
            AND customerID = :customerID
            AND acqRefData IS NOT NULL
            ORDER BY mercuryPayHistoryID DESC
            LIMIT 1";
        $this->sqlData = array(':orderID'    => $orderID, ':customerID' => $customerID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (count($res) !== 1) {
            // No history
            return false;
        }
        return $res[0];
    }

    /**
     * getPaymentMethodLastCodes
     * 
     * Gets last codes for payment method for subsequent byRecordNo use
     * 
     * @since v0.0.1
     * 
     * @param int $paymentMethodID
     * 
     * @return boolean|array
     */
    public function getPaymentMethodLastCodes($paymentMethodID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT authCode, acqRefData
            FROM mercuryPayHistory
            WHERE paymentMethodID = :paymentMethodID
                AND authCode IS NOT NULL
                AND acqRefData IS NOT NULL
            ORDER BY mercuryPayHistoryID DESC
            LIMIT 1";
        $this->sqlData = array(':paymentMethodID' => $paymentMethodID);
        $r             = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($r)) {
            return false;
        } else {
            return $r[0];
        }
    }

    /**
     * logToken
     * 
     * Logs MToken for payment method
     * 
     * @param int $customerID Customer ID
     * @param int $paymentMethodID Payment Method ID
     * @param string $frequency Frequency
     * @param SimpleXMLElement $recordNo RecordNo
     * 
     * @return boolean True on success
     */
    public function logToken($customerID, $paymentMethodID, $frequency, $recordNo)
    {
        \Errors::debugLogger(__METHOD__, 5);
        $this->sql     = "
            INSERT INTO mercuryPayCustomerPaymentMethods
                (customerID, paymentMethodID, frequency, recordNo, dateLastUsed)
            VALUES
                (:customerID, :paymentMethodID, :frequency, :recordNo, :dateLastUsed)
            ON DUPLICATE KEY UPDATE
                customerID = :customerID,
                paymentMethodID = :paymentMethodID,
                frequency = :frequency,
                recordNo = :recordNo,
                dateLastUsed = :dateLastUsed";
        $this->sqlData = array(':customerID'      => $customerID,
            ':paymentMethodID' => $paymentMethodID,
            ':frequency'       => $frequency,
            ':recordNo'        => $recordNo,
            ':dateLastUsed'    => Util::getDateTimeUTC());
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * getToken
     * 
     * Gets MToken for payment method
     * 
     * @param int $customerID Customer ID
     * @param int $paymentMethodID Payment Method ID
     * 
     * @return boolean|array False if no history otherwise array of data
     */
    public function getToken($customerID, $paymentMethodID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT frequency, recordNo, dateLastUsed
            FROM mercuryPayCustomerPaymentMethods
            WHERE customerID = :customerID
            AND paymentMethodID = :paymentMethodID
            LIMIT 1";
        $this->sqlData = array(':customerID'      => $customerID,
            ':paymentMethodID' => $paymentMethodID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (count($res) !== 1) {
            //trigger_error('Unexpected results', E_USER_NOTICE);
            return false;
        }
        return $res[0];
    }

    /**
     * preparePreAuthRequest
     * 
     * Prepares XML for PreAuth transaction, handles both new payment methods and existing tokens
     * 
     * @version v0.0.1
     * 
     * @param string $merchantID Merchant ID
     * @param string $operatorID Operator ID
     * @param string $tranType Tran Type
     * @param string $tranCode Tran Code
     * @param string $invoiceNo Invoice No - doubles as RefNo
     * @param string $memo Memo
     * @param string $freq Freq
     * @param string $number1 Number 1
     * @param string $number2 Number 2
     * @param string $ccExpMo Expiration Month
     * @param string $ccExpYr Expiration Year
     * @param string $ccAddress Address
     * @param string $ccZip Zip
     * @param string $amount Amount
     * @param string $paymentMethodToken Optional Token to use instead of new details
     * @param string $authCode Optional if using recordNo
     * @param string $acqRefData Optional if using recordNo
     * 
     * @return string $xml XML formatted PreAuth request
     */
    public function preparePreAuthRequest($merchantID, $operatorID, $tranType, $tranCode, $invoiceNo, $memo, $freq, $number1, $number2,
                                          $ccExpMo, $ccExpYr, $ccAddress, $ccZip, $amount, $paymentMethodToken = NULL,
                                          $authCode = NULL, $acqRefData = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);

        // Existing payment method (Token)
        if (!empty($paymentMethodToken)) {
            $recordNo = '<RecordNo>' . $paymentMethodToken . '</RecordNo>';
            $account  = '
                <TranInfo>
                    <AuthCode>' . $authCode . '</AuthCode>
                    <AcqRefData>' . $acqRefData . '</AcqRefData>
                </TranInfo>';
            // New payment method
        } else {
            $ccExp    = $ccExpMo . $ccExpYr;
            $recordNo = '<RecordNo>RecordNumberRequested</RecordNo>';
            $account  = '
                <Account>
                        <AcctNo>' . $number1 . '</AcctNo>
                        <ExpDate>' . $ccExp . '</ExpDate>
                </Account>
                <CVVData>' . $number2 . '</CVVData>
                <AVS>
                    <Address>' . $ccAddress . '</Address>
                    <Zip>' . $ccZip . '</Zip>
                </AVS>';
        }

        $xml = '<?xml version="1.0" ?>
            <TStream>
                <Transaction>
                    <MerchantID>' . $merchantID . '</MerchantID>
                    <OperatorID>' . $operatorID . '</OperatorID>
                    <TranType>' . $tranType . '</TranType>
                    <TranCode>' . $tranCode . '</TranCode>
                    <InvoiceNo>' . $invoiceNo . '</InvoiceNo>
                    <RefNo>' . $invoiceNo . '</RefNo>
                    <Memo>' . $memo . '</Memo>
                    <PartialAuth>Allow</PartialAuth>
                    ' . $recordNo . '
                    <Frequency>' . $freq . '</Frequency>
                    ' . $account . '
                    <Amount>
                        <Purchase>' . $amount . '</Purchase>
                        <Authorize>' . $amount . '</Authorize>
                    </Amount>
                </Transaction>
            </TStream>';
        return $xml;
    }

    /**
     * preparePreAuthCaptureRequest
     * 
     * Prepares XML for PreAuth transaction
     * 
     * @param string $merchantID Merchant ID
     * @param string $operatorID Operator ID
     * @param string $tranType Tran Type
     * @param string $tranCode Tran Code
     * @param string $invoiceNo Invoice No - doubles as RefNo
     * @param string $memo Memo
     * @param string $recordNo Record No
     * @param string $freq Frequency
     * @param string $totalAmount Total Amount
     * @param string $authAmount Authorized Amount
     * @param string $authCode Auth Code
     * @param string $acqRefData AcqRefData
     * 
     * @return string $xml XML formatted PreAuthCapture request
     */
    public function preparePreAuthCaptureRequest($merchantID, $operatorID, $tranType, $tranCode, $invoiceNo, $memo, $recordNo, $freq,
                                                 $totalAmount, $authAmount, $authCode, $acqRefData)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $xml = '<?xml version="1.0" ?>
            <TStream>
                <Transaction>
                    <MerchantID>' . $merchantID . '</MerchantID>
                    <OperatorID>' . $operatorID . '</OperatorID>
                    <TranType>' . $tranType . '</TranType>
                    <TranCode>' . $tranCode . '</TranCode>
                    <InvoiceNo>' . $invoiceNo . '</InvoiceNo>
                    <RefNo>' . $invoiceNo . '</RefNo>
                    <Memo>' . $memo . '</Memo>
                    <PartialAuth>Allow</PartialAuth>
                    <RecordNo>' . $recordNo . '</RecordNo>
                    <Frequency>' . $freq . '</Frequency>
                    <Amount>
                        <Purchase>' . $totalAmount . '</Purchase>
                        <Authorize>' . $authAmount . '</Authorize>
                    </Amount>
                    <TranInfo>
                        <AuthCode>' . $authCode . '</AuthCode>
                        <AcqRefData>' . $acqRefData . '</AcqRefData>
                    </TranInfo>
                </Transaction>
            </TStream>';
        return $xml;
    }

    /**
     * creditTransaction
     * 
     * Executes prepared XML statement
     * 
     * @param \SoapClient $client SoapClient
     * @param string $xml XML formatted string
     * @param string $pass WebServicesPassphrase
     * 
     * @return boolean|\SoapClient CreditTransactionResult
     */
    public function creditTransaction(\SoapClient $client, $xml, $pass)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $res = $client->CreditTransaction(
                        array('tran' => $xml,
                            'pw'   => $pass)
                )->CreditTransactionResult;
        if (!isset($res)) {
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return $res;
    }

    /**
     * prepareVoidSaleRequest
     * 
     * Prepares XML request for Reversal/VoidSale
     * 
     * @param string $merchantID Merchant ID
     * @param string $operatorID Operator ID
     * @param string $tranType Tran Type
     * @param string $tranCode Tran Code
     * @param string $invoiceNo Invoice No
     * @param string $memo Memo
     * @param string $recordNo Record No
     * @param string $freq Frequency
     * @param string $totalAmount Total Amount
     * @param string $authAmount Authorized Amount
     * @param string $authCode Auth Code
     * @param string $acqRefData Optional - required for Reversal, omit for VoidSale
     * @param string $processData Optional - required for Reversal, omit for VoidSale
     * 
     * @return string $xml XML formatted request
     */
    public function prepareVoidSaleRequest($merchantID, $operatorID, $tranType, $tranCode, $invoiceNo, $memo, $recordNo, $freq,
                                           $totalAmount, $authAmount, $authCode, $acqRefData = false, $processData = false)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $xml = '<?xml version="1.0"?>
            <TStream>
                <Transaction>
                    <MerchantID>' . $merchantID . '</MerchantID>
                    <OperatorID>' . $operatorID . '</OperatorID>
                    <TranType>' . $tranType . '</TranType>
                    <TranCode>' . $tranCode . '</TranCode>
                    <InvoiceNo>' . $invoiceNo . '</InvoiceNo>
                    <RefNo>' . $invoiceNo . '</RefNo>
                    <Memo>' . $memo . '</Memo>
                    <RecordNo>' . $recordNo . '</RecordNo>
                    <Frequency>' . $freq . '</Frequency>
                    <Amount>
                        <Authorize>' . $authAmount . '</Authorize>
                        <Purchase>' . $totalAmount . '</Purchase>
                    </Amount>
                    <TranInfo>
                        <AuthCode>' . $authCode . '</AuthCode>';
        // Reversal request
        if (!empty($acqRefData) && !empty($processData)) {
            $xml .= '<AcqRefData>' . $acqRefData . '</AcqRefData><ProcessData>' . $processData . '</ProcessData>';
        }
        $xml .= '
                    </TranInfo>
                </Transaction>
            </TStream>';
        return $xml;
    }

    /**
     * prepareReturnRequest
     * 
     * Prepares XML request for Return/Refund
     * 
     * @param string $merchantID Merchant ID
     * @param string $operatorID Operator ID
     * @param string $tranType Tran Type
     * @param string $tranCode Tran Code
     * @param string $invoiceNo Invoice No - doubles as RefNo
     * @param string $memo Memo
     * @param string $recordNo Record No
     * @param string $freq Frequency
     * @param string $totalAmount Total Amount
     * 
     * @return string $xml XML formatted request
     */
    public function prepareReturnRequest($merchantID, $operatorID, $tranType, $tranCode, $invoiceNo, $memo, $recordNo, $freq,
                                         $totalAmount)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $xml = '<?xml version="1.0"?>
            <TStream>
                <Transaction>
                    <MerchantID>' . $merchantID . '</MerchantID>
                    <OperatorID>' . $operatorID . '</OperatorID>
                    <TranType>' . $tranType . '</TranType>
                    <TranCode>' . $tranCode . '</TranCode>
                    <InvoiceNo>' . $invoiceNo . '</InvoiceNo>
                    <RefNo>' . $invoiceNo . '</RefNo>
                    <Memo>' . $memo . '</Memo>
                    <RecordNo>' . $recordNo . '</RecordNo>
                    <Frequency>' . $freq . '</Frequency>
                    <Amount>
                        <Purchase>' . $totalAmount . '</Purchase>
                    </Amount>
                </Transaction>
            </TStream>';
        return $xml;
    }

    /**
     * orderHasAttemptedReversal
     * 
     * Checks if order has attempted a reversal
     * 
     * @param int $orderID Order ID
     * @param int $customerID Customer ID
     * 
     * @return boolean True if has attempted reversal previously
     */
    public function orderHasAttemptedReversal($orderID, $customerID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT COUNT(orderID) AS count
            FROM mercuryPayHistory
            WHERE orderID = :orderID
            AND customerID = :customerID
            AND action = :action";
        $this->sqlData = array(':orderID'    => $orderID, ':customerID' => $customerID, ':action'     => 'preAuthReversal');
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (intval($res[0]['count']) != 0) {
            \Errors::debugLogger(__METHOD__ . ' true');
            return true;
        } else {
            \Errors::debugLogger(__METHOD__ . ' false');
            return false;
        }
    }

    /**
     * getMercuryPayChangelog
     * 
     * Gets orders MercuryPay transactions history
     * 
     * @param int $orderID Order ID
     * @param int $customerID Customer ID
     * 
     * @return array Changelog details
     */
    public function getMercuryPayChangelog($orderID, $customerID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT mph.dateReported, mph.action, mph.amount, mph.returnCode, mph.returnStatus, mph.returnTextResponse, mph.returnMessage,
                mph.avsResult, mph.cvvResult, mph.authCode, mph.acqRefData, mph.refNo, mph.processData,
                mpcpm.frequency, mpcpm.recordNo
            FROM mercuryPayHistory AS mph
                LEFT JOIN mercuryPayCustomerPaymentMethods as mpcpm
                    ON mph.customerID = mpcpm.customerID
                        AND mph.paymentMethodID = mpcpm.paymentMethodID
            WHERE mph.orderID = :orderID
            AND mph.customerID = :customerID
            ORDER BY mph.dateReported DESC";
        $this->sqlData = array(':orderID'    => $orderID, ':customerID' => $customerID);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * convertReponseToXML
     * 
     * @param string $res Response from API
     * 
     * @return \SimpleXMLElement Response as XML object for reading
     */
    public function convertReponseToXML($res)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $xml = simplexml_load_string($res);
        return $xml;
    }

    /**
     * getCmdResponseMsgByCode
     * 
     * Gets the description of the provided response code
     * 
     * @param string $code DSI return code
     * 
     * @return string Code description
     */
    public function getCmdResponseMsgByCode($code)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $reasons = array(
            // Server
            '002000' => 'Password Verified',
            '002001' => 'Queue Full',
            '002002' => 'Password Failed – Disconnecting',
            '002003' => 'System Going Offline',
            '002004' => 'Disconnecting Socket',
            '002006' => 'Refused Max Connections',
            '002008' => 'Duplicate Serial Number Detected',
            '002009' => 'Password Failed (Client / Server)',
            '002010' => 'Password failed (Challenge / Response)',
            '002011' => 'Internal Server Error – Call Provider',
            // General
            '001001' => 'General Failure',
            '001003' => 'Invalid Command Format',
            '001004' => 'Insufficient Fields',
            '001006' => 'Global API Not Initialized',
            '001007' => 'Timeout on Response',
            '001011' => 'Empty Command String',
            '100201' => 'Invalid Transaction Type',
            '100202' => 'Invalid Operator ID',
            '100203' => 'Invalid Memo',
            '100204' => 'Invalid Account Number',
            '100205' => 'Invalid Expiration Date',
            '100206' => 'Invalid Authorization Code',
            '100207' => 'Invalid Authorization Code',
            '100208' => 'Invalid Authorization Amount',
            '100209' => 'Invalid Cash Back Amount',
            '100210' => 'Invalid Gratuity Amount',
            '100211' => 'Invalid Purchase Amount',
            '100212' => 'Invalid Magnetic Stripe Data',
            '100213' => 'Invalid PIN Block Data',
            '100214' => 'Invalid Derived Key Data',
            '100215' => 'Invalid State Code',
            '100216' => 'Invalid Date of Birth',
            '100217' => 'Invalid Check Type',
            '100218' => 'Invalid Routing Number',
            '100219' => 'Invalid TranCode',
            '100220' => 'Invalid Merchant ID',
            '100221' => 'Invalid TStream Type',
            '100222' => 'Invalid Batch Number',
            '100223' => 'Invalid Batch Item Count',
            '100224' => 'Invalid MICR Input Type',
            '100225' => 'Invalid Driver’s License',
            '100226' => 'Invalid Sequence Number',
            '100227' => 'Invalid Pass Data',
            '100228' => 'Invalid Card Type',
            '004019' => 'TStream Type Missing',
            '004020' => 'Could Not Encrypt Response- Call Provider',
            '009999' => 'Unknown Error',
            '003002' => 'In Process with server',
            '003003' => 'Socket Error sending request',
            '003004' => 'Socket already open or in use',
            '003005' => 'Socket Creation Failed',
            '003006' => 'Socket Connection Failed',
            '003007' => 'Connection Lost',
            '003008' => 'TCP/IP Failed to Initialize',
            '003009' => 'Control failed to find branded serial (password lookup failed)',
            '003010' => 'Time Out waiting for server response',
            '003011' => 'Connect Cancelled',
            '003012' => '128 bit CryptoAPI failed',
            '003014' => 'Threaded Auth Started Expect Response Event (Note it is possible the event could fire before the function returns.)',
            '003017' => 'Failed to start Event Thread',
            '003050' => 'XML Parse Error',
            '003051' => 'All Connections Failed',
            '003052' => 'Server Login Failed',
            '003053' => 'Initialize Failed',
            '004001' => 'Global Response Length Error (Too Short)',
            '004002' => 'Unable to Parse Response from Global (Indistinguishable)',
            '004003' => 'Global String Error',
            '004004' => 'Weak Encryption Request Not Supported',
            '004005' => 'Clear Text Request Not Supported',
            '004010' => 'Unrecognized Request Format',
            '004011' => 'Error Occurred While Decrypting Request',
            '004017' => 'Invalid Check Digit',
            '004018' => 'Merchant ID Missing'
        );
        if (!array_key_exists($code, $reasons)) {
            $msg = 'Unknown';
        } else {
            $msg = $reasons[$code];
        }
        return $msg;
    }

    /**
     * getCmdResponseMsg
     * 
     * Evaluate CreditTransactionResult according to pg 27 of integration guide
     * 
     * @version v0.0.1
     * 
     * @param SimpleXMLElement $cmdStatus CmdStatus
     * @param string $textResponse TextResponse
     * 
     * @return string $msg Response message
     */
    public function getCmdResponseMsg($cmdStatus, $textResponse)
    {
        \Errors::debugLogger(__METHOD__, 5);
        // Evaluate response code & msg
        switch ($cmdStatus):

            // Approved status
            case 'Approved':

                // Text response
                switch ($textResponse):
                    case 'AP':
                        $msg = 'Credit Approved';
                        break;

                    case 'APPROVED':
                        $msg = 'Debit Approved';
                        break;

                    case 'PARTIAL AP':
                        $msg = 'Authorized';
                        break;

                    case 'AP*';
                        $msg = 'Duplicate';
                        break;

                    case 'APPROVED STANDIN':
                        $msg = 'Approved';
                        break;

                    case 'AP - NOT CAPTURED':
                        $msg = 'Resend for Authorization';
                        break;

                    default:
                        $msg = 'Unknown';
                        break;
                endswitch;
                break;

            // Declined status
            case 'Declined':

                // Text response
                switch ($textResponse):
                    case 'DECLINE':
                        $msg = 'Declined';
                        break;

                    case 'CALL ND Referrals':
                        $msg = 'Call Required';
                        break;

                    case 'CALL AE Referrals':
                        $msg = 'Call Required';
                        break;

                    case 'CALL Discover Referrals':
                        $msg = 'Call Required';
                        break;

                    case 'PIC UP':
                        $msg = 'Declined';
                        break;

                    case 'DECLINED-CV2 FAIL':
                        $msg = 'Invalid Security Code';
                        break;

                    case 'INVALID EXP DATE':
                        $msg = 'Invalid Expiration Date';
                        break;

                    case 'INVALID PIN':
                        $msg = 'Invalid PIN';
                        break;

                    case 'UNAUTH USER':
                        $msg = 'Unauthorized Usage';
                        break;

                    default:
                        $msg = 'Unknown';
                        break;
                endswitch;
                break;

            // Success
            case 'Success':
                $msg = 'Success';
                break;

            // Error
            case 'Error':
                $msg = 'Error';
                break;

            // Not accounted for
            default:
                $msg = 'Unknown';
                break;
        endswitch;

        return $msg;
    }

}
?>