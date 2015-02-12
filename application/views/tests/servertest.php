<?php

/**
 * Tests the server's ability to use openSSL:
 * 
 * 1) create pub/priv key pair
 * 2) extract pub/priv keys
 * 3) encrypt plaintext using keys
 * 4) decrypt using keys
 * 
 * @return boolean|string False if fails, decrypted string if success
 */
function testOpenSSL($opensslConfigPath = NULL)
{
    if ($opensslConfigPath == NULL) {
        $opensslConfigPath = OPENSSL_CONFIG;
    }
    $config = array(
        "config"           => $opensslConfigPath,
        "digest_alg"       => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );
    
    if ($opensslConfigPath == NULL) {
        unset($config["config"]);
    }

    $res = openssl_pkey_new($config);
    if (empty($res)) {
        return false;
    }

    // Extract the private key from $res to $privKey
    openssl_pkey_export($res, $privKey, NULL, $config);

    // Extract the public key from $res to $pubKey
    $pubKey = openssl_pkey_get_details($res);
    if ($pubKey === FALSE) {
        return false;
    }

    $pubKey = $pubKey["key"];

    $data = 'Pass';

    // Encrypt the data to $encrypted using the public key
    $res = openssl_public_encrypt($data, $encrypted, $pubKey);
    if ($res === FALSE) {
        return false;
    }

    // Decrypt the data using the private key and cart the results in $decrypted
    $res = openssl_private_decrypt($encrypted, $decrypted, $privKey);
    if ($res === FALSE) {
        return false;
    }

    return $decrypted;
}

function testMySqlLowercaseTableNames()
{
    $RewriteMapping = new RewriteMapping();
    $test = $RewriteMapping->getWhere(array('ID' => 1));
    var_dump($test);
    return FALSE;
}

function runServerTests()
{
    $pass = TRUE;
    $results = "";
    // mcrypt
    $results .= "<table class='bordered'><tr><td>Mcrypt</td><td>";
    if (!defined("MCRYPT_MODE_CFB")) {
        $pass = FALSE;
        $results .= "<span style='background-color:red;color:yellow;padding:10px;'>Fail</span> Try installing <strong>php-mcrypt</strong>";
    } else {
        $results .= "<span style='background-color:green;color:white;padding:10px;'>Pass</span>";
    }
    $results .= "</td></tr>";

    // soap
    $results .= "<tr><td>Soap</td><td>";
    if (!defined("SOAP_1_1")) {
        $pass = FALSE;
        $results .= "<span style='background-color:red;color:yellow;padding:10px;'>Fail</span> Try installing <strong>php-soap</strong>";
    } else {
        $results .= "<span style='background-color:green;color:white;padding:10px;'>Pass</span>";
    }
    $results .= "</td></tr>";

    // openSSL
    $results .= "<tr><td>OpenSSL</td><td>";
    $res = testOpenSSL(OPENSSL_CONFIG);
    if (empty($res)) {
        $pass = FALSE;
        $results .= "<span style='background-color:red;color:yellow;padding:10px;'>Fail " . $res . "</span> If using Windows, ensure OpenSSL path is defined correctly and OpenSSL is configured correctly.";
    } else {
        $results .= "<span style='background-color:green;color:white;padding:10px;'>" . $res . "</span>";
    }
    $results .= "</td></tr>";
    
    // GD library (img)
    $results .= "<tr><td>GD Image</td><td>";
    if (extension_loaded('gd') && function_exists('gd_info'))
    {$res = "Pass";} else {$res = FALSE;}
    if (empty($res)) {
        $pass = FALSE;
        $results .= "<span style='background-color:red;color:yellow;padding:10px;'>Fail " . $res . "</span> Ensure php5-gd is installed and enabled";
    } else {
        $results .= "<span style='background-color:green;color:white;padding:10px;'>" . $res . "</span>";
    }
    $results .= "</td></tr>";
    
    // MySQL (lowercase_table_names)
    $results .= "<tr><td>MySQL</td><td>";
    $res = testMySqlLowercaseTableNames();
    if (empty($res)) {
        $pass = FALSE;
        $results .= "<span style='background-color:red;color:yellow;padding:10px;'>Fail " . $res . "</span> If using Linux, ensure MySQL my.ini setting <strong>lower_case_table_names</strong> is set to 1";
    } else {
        $results .= "<span style='background-color:green;color:white;padding:10px;'>" . $res . "</span>";
    }
    $results .= "</td></tr></table>";
    
    // Show form if Pass
    if ($pass === TRUE)
    {
        $results .= "<script>$('.wizard_form').removeClass('hidden');</script>";
    }
    
    return $results;
}

echo runServerTests();