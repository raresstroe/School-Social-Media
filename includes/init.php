<?php
session_start();
require "config.php";
include "includes/functions.php";
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
function isLoggedIn()
{
    return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("location:login.php");
    }
}

requireLogin();

// error_reporting(E_ALL);
// ini_set('log_errors', 1);
// ini_set('error_log', 'error.log');
