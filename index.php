<?php

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

// if user want to logout
if (isset($_GET['disconnect']))
{
    logout();
    header('Location: ?');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>theb2.fr</title>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="res/style.css" rel="stylesheet">
	<link rel="icon" type="image/png" href="res/favico.ico" />
	<link href='http://fonts.googleapis.com/css?family=Pacifico' rel='stylesheet' type='text/css'>

</head>

<body id="comingsoon">

<header id="header">

<?php if(!isLoggedIn()){ ?>

	<div class="container">
		<h1>Theb2.fr</h1>
		<h2><br></h2>

					<form action="login.php" method="post" name="loginForm" >
						<div id="login-form">
							<input type="text" name="login" class="login-input" placeholder="login">
							<input type="password" name="pwd" class="login-input" placeholder="password">
							<input src="res/go.png" type="image" value="submit" class="login-submit">
						</div>
						<?php if(isset($_GET['ko'])){ ?><div id="tryagain">Ahem ... try again !</div> <?php } ?>
					</form>		
	
<?php }else{ ?>

	<div id="identification">Bienvenue <? echo ucfirst(who()); ?></div>
	<div class="container">
		<h1>Theb2.fr</h1>
		<h2>fun & geeky stuff everyday</h2>

					<a href="b2book" class="btn btn-orange">B2book</a>
					<a href="box" class="btn btn-orange">Cloud</a>
					<a href="proxy" class="btn btn-orange">Proxy</a>
					<a href="chat/all" class="btn btn-orange">Chat</a>

<?php } ?>

	</div>

	<div id="monster-wrap">
		<!-- Generator: Adobe Illustrator 17.0.2, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg version="1.1" id="monster" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="660px" height="415px" viewBox="0 0 660 415" enable-background="new 0 0 660 415" xml:space="preserve">
<g id="body">
	
		<linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="273.7538" y1="663.4063" x2="273.7538" y2="381.1393" gradientTransform="matrix(1.1347 0 0 1.1347 12.2037 -338.6221)">
		<stop  offset="0" style="stop-color:#C34A2E"/>
		<stop  offset="0.0332" style="stop-color:#CA4B2C"/>
		<stop  offset="0.1425" style="stop-color:#DB4F28"/>
		<stop  offset="0.2786" style="stop-color:#E65125"/>
		<stop  offset="0.4696" style="stop-color:#ED5323"/>
		<stop  offset="1" style="stop-color:#EF5323"/>
	</linearGradient>
	<path fill="url(#SVGID_1_)" d="M438.449,156.011c5.839-10.264,2.569-23.392-7.548-29.676c-6.22-3.865-13.611-4.259-19.921-1.72
		c-21.919-18.284-49.868-29.672-80.607-30.685c-19.85-0.654-38.82,3.091-55.987,10.345c-7.29-7.871-19.429-9.459-28.584-3.259
		c-8.759,5.933-11.906,17.047-8.129,26.483c-4.656,4.15-9.017,8.626-13.052,13.392c-7.575-3.44-14.992-3.217-18.265,1.14
		c-3.378,4.494-1.318,12.002,4.495,18.46c-10.502,18.246-16.813,39.257-17.555,61.768l-6.406,191.886h261.409l10.402-183.139
		C459.608,203.5,452.08,177.673,438.449,156.011z"/>
	<circle fill="#C53827" cx="237.026" cy="206.764" r="6.448"/>
	<circle fill="#C53827" cx="411.704" cy="180.737" r="8.463"/>
	<circle fill="#C53827" cx="416.954" cy="217.16" r="5.288"/>
</g>
<g id="tenticle_1">
	<path fill="#EE5323" d="M97.146,279.315c-10.292-18.255-16.381-22.227-25.765-26.53c-7.927-3.634-18.459-1.205-24.127,3.433
		c-0.187-1.607,0.288-3.847,1.847-6.749c4.215-7.86,18.951-14.42,30.518-10.656c11.567,3.761,19.227,7.572,33.683,27.39
		c14.454,19.82,35.685,17.607,35.685,17.607c-2.799,20.172-21.478,22.972-21.478,22.972s-1.865-0.38-5.029-1.813
		c1.262-2.007,2.322-4.402,3.046-7.269C125.525,297.699,107.436,297.572,97.146,279.315z"/>
	<path fill="#C53827" d="M77.709,260.64c-12.981-11.363-22.365-2.869-25.749-1.589c-2.133,0.806-4.389-0.091-4.707-2.833
		c5.668-4.638,16.201-7.066,24.127-3.433c9.385,4.303,15.473,8.275,25.765,26.53c10.29,18.256,28.379,18.385,28.379,18.385
		c-0.724,2.866-1.783,5.262-3.046,7.269c-4.377-1.984-11.245-5.986-19.119-13.791C89.797,277.727,90.69,272.003,77.709,260.64z"/>
</g>
<g id="tenticle_3">
	<path fill="#EE5323" d="M527.478,276.977c7.606-40.968,16.537-52.332,31.48-66.415c12.622-11.898,34.102-14.024,47.823-8.888
		c-0.69-3.148-3.04-7.065-7.867-11.548c-13.066-12.135-45.171-15.125-64.589-0.669c-19.418,14.455-31.421,26.514-45.883,73.08
		c-14.46,46.565-56.017,55.906-56.017,55.906c18.377,36.251,55.495,29.642,55.495,29.642s3.276-1.904,8.326-6.62
		c-3.687-2.981-7.245-6.824-10.473-11.767C485.771,329.7,519.873,317.948,527.478,276.977z"/>
	<path fill="#C53827" d="M552.095,229.397c17.159-29.684,40.403-19.642,47.628-19.385c4.556,0.162,8.233-2.965,7.056-8.337
		c-13.72-5.136-35.201-3.01-47.823,8.888c-14.943,14.082-23.874,25.447-31.48,66.415c-7.605,40.97-41.707,52.723-41.707,52.723
		c3.227,4.943,6.785,8.786,10.473,11.767c6.984-6.527,17.369-18.443,27.184-38.17C540.336,269.305,534.935,259.081,552.095,229.397z
		"/>
</g>
<g id="tenticle_2">
	<path fill="#EE5323" d="M116.593,153.271c17.237,21.05,27.198,37.507,32.077,92.693c4.015,45.411,61.072,93.624,71.197,100.136
		c4.718-3.241,24.364-4.779,24.364-4.779s-80.408-55.41-85.287-110.595c-4.879-55.183-14.84-71.642-32.077-92.691
		s-51.712-26.18-68.171-16.219c-9.697,5.868-12.458,11.804-11.526,16.021c0.405-0.262,0.82-0.523,1.25-0.784
		C64.88,127.091,99.357,132.221,116.593,153.271z"/>
	<path fill="#C53827" d="M53.421,143.546c7.667,1.612,38.808-1.018,50.28,36.246c11.473,37.262,3.899,45.534,12.922,87.765
		c10.317,48.285,45.295,90.053,45.295,90.053s33.817,5.477,58.555-11.509c-10.125-6.512-67.788-54.725-71.802-100.136
		c-4.879-55.185-14.841-71.643-32.077-92.693c-17.236-21.051-51.713-26.181-68.172-16.22c-0.431,0.261-0.846,0.523-1.25,0.784
		C47.823,140.778,50.271,142.884,53.421,143.546z"/>
	
		<ellipse transform="matrix(-0.9206 0.3905 -0.3905 -0.9206 299.554 298.1275)" opacity="0.8" fill="#A21316" enable-background="new    " cx="119.469" cy="179.517" rx="3.905" ry="5.213"/>
	
		<ellipse transform="matrix(-0.9206 0.3905 -0.3905 -0.9206 275.0566 282.9246)" opacity="0.8" fill="#A21316" enable-background="new    " cx="108.766" cy="169.425" rx="2.149" ry="2.869"/>
	
		<ellipse transform="matrix(-0.9958 0.0912 -0.0912 -0.9958 265.0624 484.2661)" opacity="0.8" fill="#A21316" enable-background="new    " cx="121.467" cy="248.189" rx="3" ry="4.005"/>
	
		<ellipse transform="matrix(-0.9958 0.0913 -0.0913 -0.9958 298.0676 505.7708)" opacity="0.8" fill="#A21316" enable-background="new    " cx="137.465" cy="259.703" rx="3.905" ry="5.214"/>
	
		<ellipse transform="matrix(-0.9958 0.0914 -0.0914 -0.9958 274.9976 523.7769)" opacity="0.8" fill="#A21316" enable-background="new    " cx="125.505" cy="268.185" rx="2.722" ry="3.634"/>
</g>
<g id="eye_wrapper">
	<clipPath id="eye_clip">
		<circle cx="325.77" cy="272.715" r="63.168" id="bubba"/>
	</clipPath>
	<g id="eye" clip-path="url(#eye_clip)">
		<circle id="white" fill="#FFFFFF" cx="325.751" cy="272.715" r="61.787"/>
		
			<linearGradient id="pupil_1_" gradientUnits="userSpaceOnUse" x1="520.3024" y1="767.7791" x2="520.3024" y2="722.8021" gradientTransform="matrix(1.1342 0.0373 -0.0373 1.1342 -236.5578 -592.6148)">
			<stop  offset="0" style="stop-color:#00544C"/>
			<stop  offset="0.5324" style="stop-color:#046B7F"/>
		</linearGradient>
		<circle id="pupil" fill="url(#pupil_1_)" cx="325.77" cy="272.101" r="25.52"/>
	</g>
</g>
</svg>		<div id="wave"></div>
	</div>

	<div id="city-wrap">
		<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 1400 1120" enable-background="new 0 0 1400 1120" xml:space="preserve" id="city">
<g id="bg2" enable-background="new">
</g>
<g id="bg1">
</g>
<g id="building_15" enable-background="new">
	<rect x="188" y="832" fill-rule="evenodd" clip-rule="evenodd" fill="#B8E4E9" width="65" height="288"/>
</g>
<g id="bridge" enable-background="new">
	<path fill-rule="evenodd" clip-rule="evenodd" fill="#B8E4E9" d="M1254,1120h39V857h-39v10c0,0,3,20-60,20s0,13,0,13s34,3,53-12
		c19-15,7-6,7-6L1254,1120z"/>
</g>
<g id="building_14" enable-background="new">
	<path fill-rule="evenodd" clip-rule="evenodd" fill="#99D8E2" d="M314,617v38h-35v39h-44v426h143V617H314z M267,739h-15v-17h15V739
		z M313,693h-15v-17h15V693z"/>
</g>
<g id="building_13">
	<polygon opacity="0.5" fill-rule="evenodd" clip-rule="evenodd" fill="#BAE8EF" points="1044,537 1044,508 1041,508 1041,537 
		1036,537 1036,489 1033,489 1033,537 993,537 993,1120 1067,1120 1067,537 	"/>
</g>
<g id="building_12">
	<polygon opacity="0.3" fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" points="353,531 389.5,494 426,531 	"/>
	<rect x="352" y="531" opacity="0.5" fill-rule="evenodd" clip-rule="evenodd" fill="#BAE8EF" width="74" height="589"/>
</g>
<g id="building_11">
	<rect x="890" y="600" opacity="0.5" fill-rule="evenodd" clip-rule="evenodd" fill="#99D8E2" width="143" height="520"/>
</g>
<g id="building_10">
	<rect x="631" y="544" opacity="0.5" fill-rule="evenodd" clip-rule="evenodd" fill="#99D8E2" width="74" height="576"/>
</g>
<g id="building_09">
	<rect x="403" y="595" opacity="0.5" fill-rule="evenodd" clip-rule="evenodd" fill="#99D8E2" width="74" height="525"/>
</g>
<g id="building_08" enable-background="new">
	<path fill-rule="evenodd" clip-rule="evenodd" fill="#99D8E2" d="M848,595.1V542h-32v-52h-8v-86h-4v86h-9v52h-33v53.1h-15V1120h117
		V595.1H848z M769,614h-11v-5h11V614z M790,614h-11v-5h11V614z M810,614h-11v-5h11V614z M831,614h-11v-5h11V614z M851,614h-11v-5h11
		V614z"/>
</g>
<g id="circle_1">
	<ellipse opacity="0.3" fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" cx="1118.5" cy="665.5" rx="33.5" ry="32.5"/>
</g>
<g id="circle">
	<ellipse fill-rule="evenodd" clip-rule="evenodd" fill="#AAE5EE" cx="315" cy="901" rx="18" ry="32"/>
</g>
<g id="building_07" enable-background="new">
	<g>
		<polygon opacity="0.5" fill-rule="evenodd" clip-rule="evenodd" fill="#7CC4D0" points="903,702 922.5,682 942,702 		"/>
		<path fill-rule="evenodd" clip-rule="evenodd" fill="#7CC4D0" d="M903,702v17h-74v401h113V724v-5v-17H903z M928,741h-49v-5h49V741
			z M930,718h-15v-4h15V718z"/>
	</g>
</g>
<g id="building_06" enable-background="new">
	<rect x="968" y="656" fill-rule="evenodd" clip-rule="evenodd" fill="#99D8E2" width="160" height="464"/>
</g>
<g id="cloud_5">
	<path opacity="0.8" fill-rule="evenodd" clip-rule="evenodd" fill="#DAF2F4" d="M1208,737.9c0,0-0.7-14.3-14.4-14.3
		s-9.9,6.5-9.9,9.8c0,3.3-9.8-26-29.6-15.8s-34.9,6.1-38,1.5c-3.1-4.6-24.5-11.2-31.1,0c-6.7,11.2-21.8,25.9-34.9,17.3
		c-13.2-8.6-14.8,13.5-8.4,13.5c6.4,0.1,164.1,0,164.1,0L1208,737.9z"/>
</g>
<g id="cloud_4">
	<path opacity="0.8" fill-rule="evenodd" clip-rule="evenodd" fill="#DAF2F4" d="M406.1,701c0,0,0.9-19,19-19c18.1,0,13,8.6,13,13
		c0,4.4,12.9-34.6,39-21c26.1,13.6,45.9,8.1,50,2c4.1-6.1,32.2-14.8,41,0c8.8,14.8,28.7,34.4,46,23c17.3-11.4,19.4,17.9,11,18
		c-8.4,0.1-216,0-216,0L406.1,701z"/>
</g>
<g id="building_05" enable-background="new">
	<path fill-rule="evenodd" clip-rule="evenodd" fill="#8DD0DB" d="M761.1,656c-4.1-18.3-21.8-32-43.1-32c-21.3,0-39,13.7-43.1,32
		H657v464h120V656H761.1z"/>
</g>
<g id="building_04" enable-background="new">
	<rect x="506" y="516" fill-rule="evenodd" clip-rule="evenodd" fill="#99D8E2" width="111" height="604"/>
	<path fill-rule="evenodd" clip-rule="evenodd" fill="#8DD0DB" d="M604,532v19h-86v-19H604z M519,557v13h12v-13H519z"/>
</g>
<g id="building_03" enable-background="new">
	<path fill-rule="evenodd" clip-rule="evenodd" fill="#8FD0DB" d="M1318,911h-98v-79h-19v-21h5v-5h-23v-23h-5v23h-12v-38h-5v38h-22
		v5h4v21h-25v79h-43v-21h-5v21h-7v-30.2l-0.1,0c0.1-0.4,0.1-0.9,0.1-1.3c0-5.8-5.1-10.5-11.5-10.5s-11.5,4.7-11.5,10.5
		c0,0.2,0,0.3,0,0.5l0,0v31h-7v-21h-5v21h-97v209h387V911z M1196,811v21h-13v-21H1196z M1178,811v21h-12v-21H1178z M1148,811h13v21
		h-13V811z"/>
</g>
<g id="building_02">
	<rect x="736" y="699" opacity="0.6" fill-rule="evenodd" clip-rule="evenodd" fill="#B3E3E9" width="74" height="421"/>
</g>
<g id="building_01" enable-background="new">
	<path fill-rule="evenodd" clip-rule="evenodd" fill="#7CC4D0" d="M426,637v190h-73v-22h-16v22h-18v84H166v-43h-17.2v43h-23.6v-43
		H108v252h10h201h119h3l0,0h124V637H426z M356,845h-15v-4h15V845z"/>
	<path fill-rule="evenodd" clip-rule="evenodd" fill="#8DD0DB" d="M439,654h11v32h-11V654z M459,686h11v-32h-11V686z M499,686h11
		v-32h-11V686z M519,686h11v-32h-11V686z M479,686h11v-32h-11V686z M539,686h11v-32h-11V686z"/>
</g>
<g id="cloud_3">
	<path opacity="0.3" fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M204.3,450.2c-4.2-6.6-12.5-3.8-15.4,0.7
		c-2.9,4.6-9.3,8.1-19,1.5c-9.8-6.7-8.1-23.9-17.6-27.2c-9.4-3.3-19.8,0.7-22.7,9.6c-2.9,8.8-4.7,5-12.5,1.5
		c-7.7-3.5-10.3-0.2-11.7,7.4c-1.4,7.6,1.5,15.5,1.5,15.5h96.7C203.5,459,208.5,456.8,204.3,450.2z"/>
</g>
<g id="cloud_2">
	<path opacity="0.3" fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M1210.4,189.7c3.5-5.5,10.4-3.2,12.8,0.6
		c2.4,3.8,7.7,6.7,15.8,1.2c8.1-5.5,6.8-19.8,14.6-22.6c7.9-2.7,16.4,0.6,18.9,7.9c2.4,7.3,3.9,4.1,10.4,1.2
		c6.4-2.9,8.6-0.2,9.7,6.1c1.2,6.3-1.2,12.8-1.2,12.8H1211C1211,197,1206.9,195.2,1210.4,189.7z"/>
</g>
<g id="cloud_1">
	<path opacity="0.5" fill-rule="evenodd" clip-rule="evenodd" fill="#FFFFFF" d="M156,798.4c5.1-7.9,15-4.6,18.4,0.9
		c3.5,5.5,11.1,9.8,22.8,1.8c11.7-8,9.7-28.7,21.1-32.7c11.3-4,23.7,0.9,27.2,11.5c3.5,10.6,5.6,6,14.9,1.8c9.3-4.2,12.3-0.3,14,8.8
		c1.7,9.1-1.8,18.5-1.8,18.5H156.9C156.9,809,150.9,806.3,156,798.4z"/>
</g>
</svg>	</div>
</header>

</body>
</html>