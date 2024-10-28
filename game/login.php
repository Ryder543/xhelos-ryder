<?php
if(!defined('_X_SECURE')){define("_X_SECURE",true);}
 require_once(dirname ( __FILE__ ).'/lib/security.php'); //Advertencia de Seguridad
 require_once(dirname ( __FILE__ )."/lib/include.php"); 
 

 // Get the login data from the POST request
 $username = isset($_POST['username']) ? trim($_POST['username']) : '';
 $password = isset($_POST['password']) ? trim($_POST['password']) : '';
 

if (empty($username) || empty($password)) {
    // Redirect to index if username or password is empty
    $_SESSION['login_error'] = 'Username and password are required.';
    debug::log("Username and password are required","login.php 1");
    header('Location: index.php');
    exit();
}

// Use the db class to find the player by username
$db = db::singleton();
$query = "SELECT * FROM players WHERE username = '" . $db->getConnection()->real_escape_string($username) . "' AND state = 'A'";
$player = $db->fetch($query);

debug::log($player,"login.php player");


if ($player) {
    // Validate password
    if (md5($password) === $player['password']) {
        // Password is correct, set the user to the session
        $_SESSION['xid'] = $player['id'];
        $_SESSION['username'] = $player['username'];
        unset($_SESSION['login_error']);
        
        // Redirect to the region page
        header('Location: region.php');
        debug::log("Username and password ok","login.php 2");
        exit();
    } else {
        // Incorrect password
        $_SESSION['login_error'] = 'Invalid username or password.';
        debug::log("Password NOT ok","login.php 3");
    }
} else {
    // Player not found
    $_SESSION['login_error'] = 'Invalid username or password.';
    debug::log("User NOT ok","login.php 4");
}

// Redirect back to the index page with an error message
header('Location: index.php');
exit();
 ?>

<body id="Login">
    <?php if (isset($_SESSION['login_error'])): ?>
        <div class="error-message">
            <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
        </div>
    <?php endif; ?>
</body>