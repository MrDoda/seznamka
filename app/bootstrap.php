<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

$configurator = new NConfigurator;

//$configurator->setDebugMode(TRUE);  // debug mode MUST NOT be enabled on production server
$configurator->enableDebugger(dirname(__FILE__) . '/../log');

$configurator->setTempDirectory(dirname(__FILE__) . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(dirname(__FILE__))
	->addDirectory(dirname(__FILE__) . '/../vendor/others')
	->register();

$configurator->addConfig(dirname(__FILE__) . '/config/config.neon');
$configurator->addConfig(dirname(__FILE__) . '/config/config.local.neon', NConfigurator::NONE); // none section

$container = $configurator->createContainer();

return $container;
