<?php
use App\Core\Auth;

// This file checks whether the user is logged in.

// It uses the Auth class from app/Core/Auth.php.

// The important line is:

// Auth::requireLogin();

// That means:

// If the user is logged in:
//     allow the page to continue

// If the user is not logged in:
//     redirect them to login.php

// Example usage

// At the top of a protected page:

// <?php
// require_once __DIR__ . '/../../../app/Core/bootstrap.php';
// require_once __DIR__ . '/../../../app/Middleware/require_auth.php';


Auth::requireLogin();
