# PHP-Encryption - A PHP Class for Encryption and Decryption
PHP-Encryption is a class that provides SYMETRIC encryption and decryption functionality using the OpenSSL library. It uses the Advanced Encryption Standard (AES) algorithm in Cipher-Block Chaining (CBC) mode for secure data encryption.  

Remember, keeping encryption keys secure and private are essential to maintaining data security.  This excescise is left to the user application.

#### Features
- Ability to import encryption keys from a secured ".env" file or set by the application at run-time
- Strong encryption of the data using AES-256-CBC cipher
- Optionally calculate HMAC_HASH on originally-encrypted data
- Optionally combines the encrypted data and it's HMAC_HASH and encrypts it a second time sing KEY2
- Optionally will compare the originally encrypted data to it's HMAC_HASH to verify it was not tampered with

#### Installation
Install PHP-Encryption via Composer using the following command:

`composer require ericledberg/PHP-Encryption`

Alternatively, you can manually download and include the cksEncryption.php file in your PHP project.

Optionally, initialize .ENV keyfile:
- Locate Example.env in:   Composer/vendor/php-encryption
- Copy example to:    cp  Example.env .env
- For production, generate new keys and update .env
- Update .env permissions to limit access
    - sudo chown root .env
    - sudo chgrp apache2 .env
    - sudo chmod 440 .env

# Example:  Composer Using .ENV To Store Keys
Here's an example of how to use PHP-Encryption in your PHP code using composer.

ericledberg/php-encryption

```php

// Alter path to where test.php script resides
$autoloader = require __DIR__ . '/vendor/autoload.php';

$aOptions = array();
$aOptions['ENVKEYFILE'] = __DIR__ . "/.env";

$objENC = new \clsEncryption\clsEncryption($aOptions);
echo "<h2>objENC() created</h2>";

// Encrypt data
$data = 'Hello, world!';
$encryptedData = $objENC->encrypt($data);

// Decrypt data
$decryptedData = $objENC->decrypt($encryptedData);

// Display results
echo "Original data: $data\n";
echo "Encrypted data: $encryptedData\n";
echo "Decrypted data: $decryptedData\n";

```
# Example:  Simple Require Class

```php

// --------------------------------------------
// encryption
// APP does not have knowledge about about internal operations of clsEncryption, including keys
// --------------------------------------------
$aOptions = array();
$aOptions['ENVKEYFILE'] = __DIR__ . $config['folderSep'] . ".envENCRYPTION";
require_once('./clsEncryption.php');
$objENC = new \clsEncryption\clsEncryption($aOptions);

// Optionally execute built-in tests...
//$objENC->ExecuteTests();
//exit;

// Encrypt data
$data = 'Hello, world!';
$encryptedData = $objENC->encrypt($data);

// Decrypt data
$decryptedData = $objENC->decrypt($encryptedData);

// Display results
echo "Original data: $data\n";
echo "Encrypted data: $encryptedData\n";
echo "Decrypted data: $decryptedData\n";

```

## Security Considerations
- Properly manage encryption keys and keep them confidential and protected from unauthorized access.
- Store encrypted data and encryption keys securely with appropriate access controls.
- Validate and sanitize input data to prevent potential security vulnerabilities.
- Regularly audit and review the implementation for security risks.
- Ensure compliance with applicable data protection laws and industry regulations, especially when handling sensitive data.

## Contribution
Contributions to PHP-Encryption are welcome! Please submit issues and pull requests to the GitHub repository at https://github.com/ericledberg/PHP-Encryption.

## License
PHP-Encryption is released under the MIT License, which allows for both personal and commercial use. Please see the LICENSE file for more details.

## Credits
PHP-Encryption was created by [ericledberg](https://github.com/ericledberg) and is inspired by various PHP encryption libraries and best practices for encryption and key management.
