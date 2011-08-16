<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require 'Zend/Application.php';
require 'Zend/Config/Ini.php';
require 'Zend/Config/Xml.php';
require 'Zend/Registry.php';

// Merge Zend Configs application.ini and navigation.xml
$config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV, array("allowModifications" => TRUE));
Zend_Registry::set("config", $config);

$configXml = new Zend_Config_Xml(APPLICATION_PATH . "/configs/navigation.xml");
$config->merge($configXml);
$config->setReadOnly(); 

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    $config
);
$application->bootstrap()
            ->run();