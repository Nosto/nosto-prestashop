<?php
// This is global bootstrap for autoloading

date_default_timezone_set('Europe/Helsinki');

// Setup the aspect mock.
require_once(dirname(__FILE__) . '/../vendor/autoload.php');
$kernel = \AspectMock\Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'includePaths' => [dirname(__FILE__).'/../src', dirname(__FILE__).'/_support/Zend']
]);
// Load SDK.
$kernel->loadFile(dirname(__FILE__) . '/../autoload.php');
// Load Zend.
$kernel->loadFile(dirname(__FILE__).'/_support/Zend/Exception.php');
$kernel->loadFile(dirname(__FILE__).'/_support/Zend/Currency.php');
$kernel->loadFile(dirname(__FILE__).'/_support/Zend/Locale.php');

// Configure API, Web Hooks, and OAuth client to use Mock server when testing.
NostoApiRequest::$baseUrl = 'http://localhost:3000';
NostoOAuthClient::$baseUrl = 'http://localhost:3000';
NostoHttpRequest::$baseUrl = 'http://localhost:3000';
