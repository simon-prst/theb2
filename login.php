<?php

ini_set('display_errors', 0);

if(isset($_POST['login']) && isset($_POST['pwd'])){

	if (is_file('b2book/data/config.php')){         require 'b2book/data/config.php'; } 
                                            	else{ echo "Can't load the page, Config file is missing ... find it quick !";exit; }     // Config file
	if (is_file('b2book/inc/misc_functions.php')){  require 'b2book/inc/misc_functions.php'; } 
	                                            else{ echo "Can't load the page, Misc functions are missing ... find it quick !";exit; }     // several useful functions
	if (is_file('b2book/inc/authentication.php')){  require 'b2book/inc/authentication.php'; } 
	                                            else{ echo "Can't load the page, Authentification functions are missing ... find it quick !";exit; }        // authentication functions

	define('INACTIVITY_TIMEOUT',3600); // (in seconds). If the user does not access any page within this time, his/her session is considered expired.
	ini_set('session.use_cookies', 1);       // Use cookies to store session.
	ini_set('session.use_only_cookies', 1);  // Force cookies for session (phpsessionID forbidden in URL)
	ini_set('session.use_trans_sid', false); // Prevent php to use sessionID in URL if cookies are disabled.
	session_name('b2');
	session_start();

	//connect to the database
	require 'b2book/data/database.php';

    // Brute force protection system : Several consecutive failed logins will ban the IP address for 30 minutes.
    if (!is_file($GLOBALS['config']['IPBANS_FILENAME'])) file_put_contents($GLOBALS['config']['IPBANS_FILENAME'], "<?php\n\$GLOBALS['IPBANS']=".var_export(array('FAILURES'=>array(),'BANS'=>array()),true).";\n?>");
    include $GLOBALS['config']['IPBANS_FILENAME'];

	// Process login form: Check if login/password is correct.
    if (!ban_canLogin()) die('You are banned for the moment. Go away.');
    if ( check_auth($_POST['login'], $_POST['pwd']) )
    {   // Login/password is ok.
        ban_loginOk();
        connect($_POST['login']);      // Create the session server-side for user.
        setcookie('b2_longlastingsession', sha1($_POST['pwd'].$_POST['login'].$GLOBALS['salt']), time() + 365*24*3600, '/', null, false, true);

        header('Location: /'); 

        exit;
    }
    else
    {
        // ban_loginFailed();

        header('Location: /?ko');  
        exit;
    }

}else{
	echo 'Get out !';
}

?>