<?php
// Returns the IP address of the client (Used to prevent session cookie hijacking.)
function myIP()
{
	$ip = $_SERVER["REMOTE_ADDR"];
	// Then we use more HTTP headers to prevent session hijacking from users behind the same proxy.
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { $ip=$ip.'_'.$_SERVER['HTTP_X_FORWARDED_FOR']; }
	if (isset($_SERVER['HTTP_CLIENT_IP'])) { $ip=$ip.'_'.$_SERVER['HTTP_CLIENT_IP']; }
	return $ip;
}

// Check that user/password is correct.
function check_auth($login,$password)
{
	$hash = sha1($password.$login.$GLOBALS['salt']);
	
	// looking for user/password in DB
	$result = mysql_query("	SELECT * 
							FROM  `b2_members` 
							WHERE login = '".mysql_real_escape_string($login)."' AND password = '".$hash."'
	");
	
    if ($result && mysql_num_rows($result)!=0)
    {   // Login/password is correct.
        logm('Login successful by form for user '.$login);
        return True;
    }
    logm('Login failed for user '.$login);
    return False;
}

// Create a session server-side when authentication is done.
function connect($user)
{
    $_SESSION['username']=$user;
    $_SESSION['new_system']='newSystem';    // only temporarily, to make sure the old session system doesn't get in the way of the new one ...
    $_SESSION['LINKS_PER_PAGE']=20;
}

// Returns true if the user is logged in.
function isLoggedIn()
{   
    // If session exists on server side, user is logged in
    if (isset($_SESSION['new_system']))
    {
        return true;
    }
    // If long lasting session cookie is sent by client, try to match to an existing user.
    elseif(isset($_COOKIE['b2_longlastingsession']))
    {
        $pwd = $_COOKIE['b2_longlastingsession'];
        // looking for password in DB
        $result = mysql_query(" SELECT * 
                                FROM  `b2_members` 
                                WHERE password = '".$pwd."'
        ");
        // If user is found, we reconnect him/her
        if ($result && mysql_num_rows($result)!=0)
        {
            $r = mysql_fetch_array($result);
            logm('Login sucessful by cookie for user '.$r['login']);
            connect($r['login']);
            return true;
        }
    }
    // Otherwise, logout properly and return that user is not logged in.
    logout();
    return false;
}

// Force logout.
function logout() { 
    if (isset($_SESSION)) { session_unset(); }
    if (isset($_COOKIE['b2_longlastingsession'])) { setcookie('b2_longlastingsession', "", 0, '/'); }
    if (isset($_COOKIE['b2'])) { setcookie('b2', "", 0, '/'); }
    if (isset($_COOKIE['mailplan'])) { setcookie('mailplan', "", 0, '/'); }
    if (isset($_COOKIE['shaarli'])) { setcookie('shaarli', "", 0, '/'); }
}

// Who is the current user ?
function who() { return $_SESSION['username']; }

// Signal a failed login. Will ban the IP if too many failures:
function ban_loginFailed()
{
    $ip=$_SERVER["REMOTE_ADDR"]; $gb=$GLOBALS['IPBANS'];
    if (!isset($gb['FAILURES'][$ip])) $gb['FAILURES'][$ip]=0;
    $gb['FAILURES'][$ip]++;
    if ($gb['FAILURES'][$ip]>($GLOBALS['config']['BAN_AFTER']-1))
    {
        $gb['BANS'][$ip]=time()+$GLOBALS['config']['BAN_DURATION'];
        logm('IP address banned from login');
    }
    $GLOBALS['IPBANS'] = $gb;
    file_put_contents($GLOBALS['config']['IPBANS_FILENAME'], "<?php\n\$GLOBALS['IPBANS']=".var_export($gb,true).";\n?>");
}

// Signals a successful login. Resets failed login counter.
function ban_loginOk()
{
    $ip=$_SERVER["REMOTE_ADDR"]; $gb=$GLOBALS['IPBANS'];
    unset($gb['FAILURES'][$ip]); unset($gb['BANS'][$ip]);
    $GLOBALS['IPBANS'] = $gb;
    file_put_contents($GLOBALS['config']['IPBANS_FILENAME'], "<?php\n\$GLOBALS['IPBANS']=".var_export($gb,true).";\n?>");
}

// Checks if the user CAN login. If 'true', the user can try to login.
function ban_canLogin()
{
    $ip=$_SERVER["REMOTE_ADDR"]; $gb=$GLOBALS['IPBANS'];
    if (isset($gb['BANS'][$ip]))
    {
        // User is banned. Check if the ban has expired:
        if ($gb['BANS'][$ip]<=time())
        {   // Ban expired, user can try to login again.
            logm('Ban lifted.');
            unset($gb['FAILURES'][$ip]); unset($gb['BANS'][$ip]);
            file_put_contents($GLOBALS['config']['IPBANS_FILENAME'], "<?php\n\$GLOBALS['IPBANS']=".var_export($gb,true).";\n?>");
            return true; // Ban has expired, user can login.
        }
        return false; // User is banned.
    }
    return true; // User is not banned.
}