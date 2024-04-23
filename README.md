# PHP-Encryption - A PHP Class for Encryption and Decryption
PHP-Encryption is a PHP class that provides encryption and decryption functionality using the OpenSSL library. It uses the Advanced Encryption Standard (AES) algorithm in Cipher-Block Chaining (CBC) mode for secure data encryption.  

It will encrypt a data string using KEY1, compute it's HMAC_HASH, and then combine both the originally encypted data with it's computed hash and encrypt it a second time using KEY2.  This second encryption step allows the decryption process to verify that the originally encrypted data was not altered in transmission.

Remember, keeping all encryption keys secure and private are essential to maintaining data security.  This excescise is left to the user.

#### Features
- Strong encryption using AES-256-CBC cipher.
- Supports custom encryption and decryption keys.
- Encrypts the original string using KEY1 and determines it's HMAC_HASH
- Combines the previously encrypted data and it's computed hash and then encrypts it using KEY2
- During decryption it validates that the originally encrypted sting is value by comparing using the HASH
- Supports obtaining encryption keys from an .env file thereby isolating keys from application

#### TODO / Issues
- Update pathing to .env file to specify during instanciation

#### Installation
Install PHP-Encryption via Composer using the following command:

`composer require ericledberg/PHP-Encryption`

Alternatively, you can manually download and include the cksEncryption.php file in your PHP project.

Initialize default keyfile:
- Locate Example.env in:   Composer/vendor/php-encryption
- To test:    cp  Example.env .env
- For production, generate new keys and update .env
- Update .env permissions to limit access
    - sudo chown root .env
    - sudo chgrp apache2 .env
    - sudo chmod 440 .env

# Usage
Here's an example of how to use PHP-Encryption in your PHP code:

```php
require 'vendor/autoload.php'; // Add this line if you're using core PHP

use ericledberg\PHP-Encryption;

// Instantiate PHP-Encryption with encryption key
$objENC = new PHP-Encryption($encryptionKey);

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
