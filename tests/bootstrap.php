<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

if (!class_exists('Tester\Assert')) {
	echo "Install Nette Tester using `composer update --dev`\n";
	exit(1);
}

TesterEnvironment::setup();

function id($val) {
	return $val;
}

$configurator = new NConfigurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(dirname(__FILE__) . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(dirname(__FILE__) . '/../app')
	->register();

$configurator->addConfig(dirname(__FILE__) . '/../app/config/config.neon');
$configurator->addConfig(dirname(__FILE__) . '/../app/config/config.local.neon', NConfigurator::NONE); // none section
return $configurator->createContainer();
