<?php
/* -------------------------------------------------------------
 *
 * Encryption KEY1:   16-byte
 * Encryption KEY2:   32-byte
 * Encryption keys must be stored base64_encode()
 * - 16, 32 & 64 byte keys are auto-generated on each instanciation as examples.  $this->dump($config), to view them
 *
 * Author:   Eric L. Edberg   ele@EdbergNet.com 4/24
 *
*/
class clsEncryption {
	public  $config       = Array();
	public  $Version      = ".0.4";
	public  $MyDebug      = false;
	public  $error        = null;
    
	// ----------------------------------------------------------
	// ----------------------------------------------------------
	public function __construct($xconfig) {
        $this->config = $xconfig;

        if(!extension_loaded('openssl')) {
			throw new Exception('clsEncryption requires PHP openssl extension, Get Help.');
		}

        if (!$this->readINI(null)){
            throw new Exception('clsEncryption cannot read key file, Get Help.');
        }

        // See README.MD which references using KEY16 and KEY32 when installing QW application (obtained when setting $this->Mydebug=true)
        // Dynamically generate base64-encoded keys at runtime
        // Application would store and manage keys themselves across multiple program instanciations
        // legacy openssl_random_pseudo_bytes() may not return cryptographically secure key on older servers
        if (!isset($this->config['KEY16'])) $this->config['KEY16'] = base64_encode(bin2hex(random_bytes(16)));
        if (!isset($this->config['KEY32'])) $this->config['KEY32'] = base64_encode(bin2hex(random_bytes(32)));
        if (!isset($this->config['KEY64'])) $this->config['KEY64'] = base64_encode(bin2hex(random_bytes(64)));
        
        return true;
	}

    // ----------------------------------------------------------
	// ----------------------------------------------------------
	public function __destruct() {
        
        if ($this->MyDebug) {
            echo "<h4>clsEncryption()</h4>";
            $this-dump($this->config);
        }
	}

    private function dump($var) {
        echo "<div class=dbg><pre>";
        print_r($var);
        echo "</pre></div>";
    }

    // --------------------------------------------
    // --------------------------------------------
    public function setKeys($xKey1, $xKey2) {
        
        if ( !isset($xKey1) || !isset($xKey2) ) {
            $this->config['error'] = "keys were not provided";
            return false;
        }
        
        // ERROR:  why do we assume that the key length is 32 & 64 bytes?
        if ( (strlen(base64_decode($xKey1))!=32) || (strlen(base64_decode($xKey2))!=64) ) {
            $this->config['error'] = "key length is not correct";
            return false;    
        }

        $this->config['KEY1'] = $xKey1;
        $this->config['KEY2'] = $xKey2;
        
        return true;
    }

    // --------------------------------------------
    // encryption keys requied by clsEncryption are stored in the .env file
    // --------------------------------------------
    private function readINI($aOptions) {
        $this->config['ENV'] = parse_ini_file('.env');
        if (!$this->config['ENV']) {
            $this->config['error'] = "encryption keyfile does not exist or is incorrect";
            return false;
        }
        
        if (!$this->setKeys($this->config['ENV']['KEY1'], $this->config['ENV']['KEY2'])) {
            echo "<h1>ERROR: encryption: " . $this->config['error'] . "</h1>";
            exit;
        }
        
        return true;
    }

    // ----------------------------------------------------------
	// see:  https://www.php.net/manual/en/function.openssl-encrypt.php
    // ----------------------------------------------------------
	function encryptData($data) {
        if (!isset($data) || trim($data) == '') return false;             // null or empty
        $first_key            = base64_decode($this->config['KEY1']);
        $second_key           = base64_decode($this->config['KEY2']);                 
        $method               = "aes-256-cbc";    
        $iv_length            = openssl_cipher_iv_length($method);
        $iv                   = random_bytes($iv_length);
        $first_encrypted      = openssl_encrypt($data,$method,$first_key, OPENSSL_RAW_DATA ,$iv);    
        $second_encrypted     = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);
        $output               = base64_encode($iv.$second_encrypted.$first_encrypted);
        return $output;        
    }

    // ------------------------------------------------------------------
	// ------------------------------------------------------------------
	function decryptData($input) {
        if (!isset($input) || trim($input) == '') return false;             // null or empty
        $first_key            = base64_decode($this->config['KEY1']);
        $second_key           = base64_decode($this->config['KEY2']);       
        $mix                  = base64_decode($input);        
        $method               = "aes-256-cbc";
        $iv_length            = openssl_cipher_iv_length($method);            
        $iv                   = substr($mix,0,$iv_length);       
        $second_encrypted     = substr($mix,$iv_length,64);
        $first_encrypted      = substr($mix,$iv_length+64);
        $data                 = openssl_decrypt($first_encrypted,$method,$first_key,OPENSSL_RAW_DATA,$iv);      
        $second_encrypted_new = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);
        if (hash_equals($second_encrypted,$second_encrypted_new)) return $data;
        return false;
    }

    // ------------------------------------------------------------------
	// ------------------------------------------------------------------
	public function EncryptDataUrl($xString) {
        if (is_null($xString) || (strcmp($xString,"")==0) ) return false;
		return urlencode($this->encryptData($xString));
	}

    // ---------------------------------------------------------------------
    // for each character in a string, convert it to it's hex value
    // ---------------------------------------------------------------------
    public function strToHex($string){
        $hex = '';
        for ($i=0; $i<strlen($string); $i++){
            $ord     = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex    .= substr('0'.$hexCode, -2);
        }
        return strToUpper($hex);
    }
    
    // ---------------------------------------------------------------------
    // for each set of hex characters, convert it back to a character
    // ---------------------------------------------------------------------
    public function hexToStr($hex){
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
    
    // ---------------------------------------------------------------------
    // Parse a string in comma-seperated name/value pairs into an array
    // ISSUE:  string cannot have split1 or split2 characters in them or the decoding will fail (this function)
    // TODO:   need to test how this reacts for arrays and possibly encode split characters when originally building string
	// ---------------------------------------------------------------------
	function strToNameValueArray($inputString, $split1, $split2) {
        $result = array();

        if (is_null($inputString) || (strcmp($inputString, "")  ==0) ) return array();
		if (is_null($split1)      || (strcmp($split1,      "")  ==0) ) $split1 = ",";
        if (is_null($split2)      || (strcmp($split2,      "")  ==0) ) $split2 = "=";
        
        // Split the input string into name/value pairs
        $pairs = explode($split1, $inputString);

        foreach ($pairs as $pair) {
            // Split each pair into name and value
            list($name, $value) = explode($split2, $pair, 2);

            // Trim any leading or trailing whitespaces
            $name  = trim($name);
            $value = trim($value);

            // Check if the name already exists in the result array
            if (isset($result[$name])) {
                // If it does, convert the existing value to an array if it's not already
                if (!is_array($result[$name])) {
                    $result[$name] = array($result[$name]);
                }
                // Add the new value to the array
                $result[$name][] = $value;
            } else {
                // If the name doesn't exist, simply set the value
                $result[$name] = $value;
            }
        }

        return $result;
    }

    // ---------------------------------------------------------------------
	// Test RSA public/private key generation
    // ERROR:  does not work unless PHP is configured with additional openssl cnf
    // See:   https://medium.com/@viniciusamparo/a-simple-guide-to-client-side-encryption-and-decryption-using-javascript-jsencrypt-and-php-20c2f179b6e5
    // ---------------------------------------------------------------------
	public function generateRSAKeys($aOptions) {
        
        // Define an array with the configuration settings for the keys to be generated.
        $config = array(
            "digest_alg" => "sha512",                     // hash function to use
            "private_key_bits" => 4096,                   // size of private key
            "private_key_type" => OPENSSL_KEYTYPE_RSA,    // type of private key (OPENSSL_KEYTYPE_RSA == RSA key).
        );

        // Generate private and public key pair
        // openssl_pkey_new() returns resource that holds the key pair
        $res = openssl_pkey_new($config);
        if (!$res) echo "<li>ERROR, openssl_pkey_new() failed</li>";
        // Extract private key
        // openssl_pkey_export() extracts private key as a string
        $xRet = openssl_pkey_export($res, $privKey);

        // Extract public key
        // openssl_pkey_get_details() returns an array with key details, including the public key.
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        // Save the private key to a file named 'private_key.pem' for later use.
        // The file_put_contents() function writes data to a file. If the file does not exist, it will be created.
        file_put_contents('.QWprivatekey.pem', $privKey);

        // Similarly, save the public key to a file named 'public_key.pem' for later use.
        file_put_contents('.QWpublickey.pem', $pubKey);

        echo "<h1>Completed Generating QW RSA Keys</h1>";
    }

    // ---------------------------------------------------------------------
	// ---------------------------------------------------------------------
	function ExecuteTests() {

        $dataToEncrypt = "12345";
        echo "<br>Data: " . $dataToEncrypt;

        $xData = $this->encryptData($dataToEncrypt);
        echo "<br>Encrypted Data: " . $xData;
        $xData = $this->decryptData($xData);
        echo "<br>Data: " . $xData;

        $xData = $this->EncryptDataUrl($dataToEncrypt);
        echo "<br><br>URL Encrypted Data: " . $xData;

        $xData = urldecode($xData);
        echo "<br>URLDECODE() Data: " . $xData;

        $xData = $this->decryptData($xData);
        echo "<br>Data: " . $xData;

        $inputString = "name1=value1,name2=value2,name1=value3,name3=value4";
        $resultArray = $this->strToNameValueArray($inputString, ",", "=");
        $this->dump($resultArray);


        // example how to set the encryption key in a cookie (make sure to set secure and httpOnly flags)
        // definately do not want to do this as it's extremely insecure
        // setcookie("key", $key, 0, '/', '', true, true);

    }

}

?>



