<?php
require_once '../config/firebase_config.php';

class User
{
    private $auth;

    public function __construct()
    {
        global $auth;
        $this->auth = $auth;
    }

    public function login($email, $password)
    {
        return $this->auth->signInWithEmailAndPassword($email, $password);
    }

    public function getUser($uid)
    {
        return $this->auth->getUser($uid);
    }
}
?>