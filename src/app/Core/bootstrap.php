<?php
declare(strict_types=1);

//starts php session
// A PHP session is a mechanism that stores user data on the 
// web server so it can be accessed across multiple pages of a 
// website 

// the important part of a session is allowing login state to work
// For example, after a user logs in, you can store:

// $_SESSION['user'] = [
//     'id' => 1,
//     'name' => 'Demo Admin',
//     'role' => 'Admin'
// ];

session_start();

define('APP_PATH', __DIR__ . '/../');

// This part:

// spl_autoload_register(...)

// means you do not have to manually require every class file.


// MANUAL REQUIRE EXAMPLE
//   require_once '../app/Core/Auth.php';

// You can just write:
//    use App\Core\Auth;
//    use App\Core\Database;

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
