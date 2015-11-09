<?php
use \Defuse\Crypto\Crypto;
use \Defuse\Crypto\Exception as Ex;

require_once 'php-encryption/autoload.php';

try {
    $key = Crypto::createNewRandomKey();
} catch (Ex\CryptoTestFailedException $ex) {
    die('Cannot safely create a key');
} catch (Ex\CannotPerformOperationException $ex) {
    die('Cannot safely create a key');
}
echo base64_encode($key) . "\n";
