<?php
require_once '../config/firebase_config.php';

function login($email, $password)
{
    global $auth;
    try {
        $signInResult = $auth->signInWithEmailAndPassword($email, $password);
        return $signInResult;
    } catch (Exception $e) {
        return null; // Handle error appropriately
    }
}
?>