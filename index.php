<?php
define('ROOT', dirname(__FILE__));
define('SYSTEM', ROOT.'/system');
require(ROOT.'/config.php');
require(SYSTEM.'/core/core.core.php');
core::init();