<?php
require_once 'vendor/autoload.php';

define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
define('CREDENTIALS_PATH', 'credentials/calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', 'credentials/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Calendar::CALENDAR_READONLY)
));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

if(isset($_REQUEST['code'])) {
	$_SESSION['code'] = 1;
	// Get the API client and construct the service object.
	$client = getClient();
	$service = new Google_Service_Calendar($client);

	// Print the next 10 events on the user's calendar.
	$calendarId = 'primary';
	$optParams = array(
	  'maxResults' => 10,
	  'orderBy' => 'startTime',
	  'singleEvents' => TRUE,
	  'timeMin' => date('c'),
	);
	$results = $service->events->listEvents($calendarId, $optParams);

	if (count($results->getItems()) == 0) {
	  $myarr = "No upcoming events found.\n";
	} else {
	  print "Upcoming events:\n";
	  foreach ($results->getItems() as $event) {
		$start = $event->start->dateTime;
		if (empty($start)) {
		  $start = $event->start->date;
		}
		$myarr[] = array("summary" =>  $event->getSummary(), "Start" => $start);
	  }
	}
	$ok='done';
	header("Location: index.php");
	exit;
}
else if(isset($_SESSION['code'])) {
	// Get the API client and construct the service object.
	$client = getClient();
	$service = new Google_Service_Calendar($client);

	// Print the next 10 events on the user's calendar.
	$calendarId = 'primary';
	$optParams = array(
	  'maxResults' => 10,
	  'orderBy' => 'startTime',
	  'singleEvents' => TRUE,
	  'timeMin' => date('c'),
	);
	$results = $service->events->listEvents($calendarId, $optParams);

	if (count($results->getItems()) == 0) {
	  $myarr =  "No upcoming events found.\n";
	} else {
	  print "Upcoming events:\n";
		foreach ($results->getItems() as $event) {
			$start = $event->start->dateTime;
			if (empty($start)) {
			  $start = $event->start->date;
			}
			$myarr[] = array("summary" =>  $event->getSummary(), "Start" => $start);
		}
		$ok='done';
	}	
}
else {
	$client = new Google_Client();
	$client->setApplicationName(APPLICATION_NAME);
	$client->setScopes(SCOPES);
	$client->setAuthConfig(CLIENT_SECRET_PATH);
	$client->setAccessType('offline');
	$authUrl = $client->createAuthUrl();
	$ok='cancel';
}

?>

<!DOCtype html>
<html lang="en-US">
	<head>
		<title>Google Calendar API</title>
	</head>
	
	<body>
		<!-- Start Google API Container -->
		<section>
			<div class="container">
				<?php if($ok == 'cancel') { ?>		
				<a href="<?php echo $authUrl; ?>">Login</a>
				<?php } else { 
				print_r($myarr);
				} ?>
			</div>
		</section>
		<!-- End Google API Container -->
	</body>
</html>
