<?php
require_once __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;

if (class_exists('Kreait\Firebase\Factory')) {
    echo "Firebase Factory class loaded successfully!";
} else {
    echo "Failed to load Firebase Factory class.";
}
