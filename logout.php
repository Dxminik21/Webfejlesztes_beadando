<?php
require_once 'includes/functions.php';

session_start();
session_destroy();

setFlashMessage('success', 'You have been logged out successfully');
redirect('login.php');
?> 