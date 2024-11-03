<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Factory;

$factory = (new Factory())->withServiceAccount(__DIR__ . '/svmrfid-firebase-adminsdk-x5z2x-e0399e500f.json');
$firebase = $factory->create();
