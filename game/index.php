<?php

  if(!defined('_X_SECURE')){define("_X_SECURE",true);}
  
  
  $game_main_directory = getcwd();
  chdir(dirname ( __FILE__ ));
  require_once("lib/include.php");
  chdir($game_main_directory);
  
  /*****************************************************
  *  DATOS INICIO
  *****************************************************/
  /*
  $menu = new menuview();
  $menu->updateState();
  $menu->initByDefault();*/
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>

<?php

    include_css("css/reset.css");
    include_css("css/jquery-ui/jquery-ui.css");
    include_css("css/jquery.qtip.css");
    include_css("css/index.css");   
 
    include_css("css/start/jquery-ui.css");
    //include_js("lib/jquery/jquery.js");
    //include_js("lib/jquery/jquery.ui.js");
    //include_js("lib/jquery/jquery.qtip2.js");
    include_js("lib/jquery/jquery.countdown.js");
    include_js("lib/js/index.js");    
?>

<title><?php echo "Xhelos - Estrategia Via Web" ?></title>
</head>

<body id="Index">
    <div class="logo-container">
        <img src="img/index/logo_en.png" alt="Logo">
    </div>
    <div class="container">
            <div class="tabs">
                <button id="login-tab" class="tab active">Login</button>
                <button id="register-tab" class="tab">Register</button>
            </div>
            <div class="form-container">
                <form id="login-form" class="form" action="login.php" method="POST">
                    <h2>Login</h2>
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required class="textInput" title="Enter your username">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required  class="textInput" title="Enter your password">
                    <button type="submit" id="xhelosLogin">Login</button>

                </form>
                <form id="register-form" class="form hidden" action="register.php" method="POST">
                    <h2>Register</h2>
                    <label for="new-username">Username</label>
                    <input type="text" id="new-username" name="username" required  class="textInput" title="Enter your username">
                    <label for="new-password">Password</label>
                    <input type="password" id="new-password" name="password" required  class="textInput" title="Enter your password">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" required  class="textInput" title="Confirm your password">
                    <button type="submit" id="xhelosRegister">Register</button>
                </form>
                <div id="loginAjax" class="loader hidden"></div>
                <div id="registerAjax" class="loader hidden"></div>
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="error-message">
                        <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js"></script>
        <script src="https://unpkg.com/tippy.js@6/dist/tippy-bundle.umd.js"></script>
        <?php include_js("lib/js/index.js"); ?>
        

</body>
</html>

