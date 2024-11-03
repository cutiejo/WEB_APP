<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';
use Kreait\Firebase\Factory;

echo "Starting Firebase connection...<br>";

class FirebaseAuthService
{
    public $auth;
    public $database;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(__DIR__ . 'firebase-key.json')
            ->withDatabaseUri('https://svmrfid-default-rtdb.firebaseio.com');

        $this->auth = $factory->createAuth();
        $this->database = $factory->createDatabase();
        echo "Firebase connection successful!<br>";
    }
}

$firebaseService = new FirebaseAuthService();
