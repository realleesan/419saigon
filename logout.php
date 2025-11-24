<?php
require_once 'includes/config.php';

// Destroy session
session_destroy();

// Redirect to home page
redirect('index.php');
?>
