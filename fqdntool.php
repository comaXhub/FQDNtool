<!DOCTYPE html>
<html lang="en">
	<head>
		<title>FQDN Tool</title>
		<meta name=viewport content="width=device-width, initial-scale=1">
		<meta name="description" content="Find which TLDs are already taken for a given domain name."/>
		<meta name="keywords" content="FQDN, TLD, Domain, Name, IP, Registries"/>
		<meta name="author" content="comaX"/>
		<meta name="reply-to" content="contact@comax.fr"/>
		<meta name="robots" content="all"/>
		<meta name="theme-color" content="#AA1111"/>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<link rel="manifest" href="/manifest.json" />
		<link rel="icon" type="image/png" href="https://comax.fr/images/favicon.png"/>
		<!-- Global site tag (gtag.js) - Google Analytics -->

		<link href="https://comax.fr/msimdb/DB_dark.css" rel="stylesheet" type="text/css" media="all" />
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
		<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	</head>
	<body lang="en">
		<div id="haut">
			<h1 class="title">
				<a href="#" style="color: white;">FQDN Tool</a></h1>
				<div id="search_form">
						<form name="form" action="/fqdntool.php" method="get"> 
							<label for = "search">
								<p class="search">
									Search:
									<input type="text" name="q" placeholder="Domain name without TLD" />
									<input type="submit" value="Search" />
									<br />
									<input type="checkbox" id="registrar" name="registrar" ><label for="registrar">Check for registrar as well?</label>
								</p>
							</label>
						</form>
				</div>
		</div>
		<div id="content">	

			<p>We're checking over 1200 TLDs, it might take a <b>few minutes</b>. Do not refresh the page after clicking search. Go and enjoy a cup of coffee, we'll give you the results when you're back.</p>
			<p>Checking for the registrar takes even longer, as some TLDs will timeout and the feature is still in developpment.</p>

<?php
set_include_path('/var/www');
set_time_limit(0);

// Get the search variable from URL

  $var = htmlentities(idn_to_ascii(@$_GET['q']),ENT_QUOTES);  
  $dn = trim($var); //trim whitespace from the stored variable
  $registrar = htmlentities(@$_GET['registrar'],ENT_QUOTES);
 
// check for an empty string and display a message.
if ($dn == "")
{
//echo "<p>You haven't searched for anything</p>";
//do nothing
}
else
{
	$output = shell_exec("/home/admin/used_tlds.sh ".$dn."");
	$exclude = file_get_contents('/home/admin/tlds-false-positive.txt');
	
	if ($output == "")
	{
		echo "No domain name found for '$dn' in any TLD.<br />";
	}
	else
	{

		if ($registrar == "on")
		{
			echo "<p>The registrar feature is still in dev. You might get inaccurate results, especially on ccTLDs.</p>
			<br />
			";

			//modifying output so we can pass it in a single line variable to the shell

			$fqdns = str_replace(PHP_EOL, ' ', $output);

			//var_dump($fqdns);

			$rav=shell_exec("/home/admin/masswhois.sh ".$fqdns."");
			//var_dump($rav);

			echo "<table>";
			echo "$rav";
			echo "</table><br />";
		}
		else {
		echo "<pre>$output</pre>";

		}
	}



	echo "<p>Beware, the following TLDs could not be checked:<br />
	".$exclude."</p>
	<br />
	<p>These TLDs always provide a positive answer for any recording on a 'dig' command and therefore return falsely positive results. Those should be checked manually." ;


	//shell_exec("rm /tmp/tlds-false-positive.txt");

}

// check for a search parameter

?>
		</div>
</body>
</html>
