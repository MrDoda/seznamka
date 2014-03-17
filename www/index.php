<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require '.maintenance.php';

$container = require dirname(__FILE__) . '/../app/bootstrap.php';

$container->getService('application')->run();
