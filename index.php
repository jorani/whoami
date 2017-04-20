<?php
/**
 * This single page application is a tutorial explaining how to use
 * Jorani as an identity provider in a SSO scenario based on OAuth2
 * Please the Readme.md file for instructions
 * Doc of OAuth2 Client: https://github.com/thephpleague/oauth2-client
 * @copyright  Copyright (c) 2017 Benjamin BALET
 * @license    http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link       https://github.com/jorani/whoami
 * @since      1.0.0
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta charset="utf-8">
    <title>Jorani OAuth2 demo application</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
<?php

/**********************************************************************************
 * You need to create an OAuth2 client into Jorani prior using this code
 **********************************************************************************/

require_once __DIR__ . '/vendor/autoload.php';

//Register Whoops Exception Handler
use Whoops\Handler\PrettyPageHandler;
$run = new Whoops\Run;
$handler = new PrettyPageHandler;
$run->pushHandler($handler);
$run->register();

//Load the configuration
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$provider = new \League\OAuth2\Client\Provider\GenericProvider([
    'clientId'                => $_SERVER['JORANI_CLIENT_ID'],    // The client ID assigned to you by the provider
    'clientSecret'            => $_SERVER['JORANI_CLIENT_SECRET'],    // The client password assigned to you by the provider
    'redirectUri'             => $_SERVER['JORANI_REDIRECT_URI'],
    'urlAuthorize'            => $_SERVER['JORANI_BASE_URL'] . 'api/authorization/authorize',
    'urlAccessToken'          => $_SERVER['JORANI_BASE_URL'] . 'api/token',
    'urlResourceOwnerDetails' => $_SERVER['JORANI_BASE_URL'] . 'api/authorization/userinfo'
]);

//We'll use PHP session a simplified access token datastorage
$existingAccessToken = NULL;

session_start();
if (isset($_SESSION['accessTokenToJorani'])) {
	$existingAccessToken = unserialize($_SESSION['accessTokenToJorani']);
	if ($existingAccessToken->hasExpired()) {
		$newAccessToken = $provider->getAccessToken('refresh_token', [
			'refresh_token' => $existingAccessToken->getRefreshToken()
		]);

		// Purge old access token and store new access token to your data store.
	}
  
} else {
	// If we don't have an authorization code then get one
	if (!isset($_GET['code'])) {
	 
		// Fetch the authorization URL from the provider; this returns the
		// urlAuthorize option and generates and applies any necessary parameters
		// (e.g. state).
		$authorizationUrl = $provider->getAuthorizationUrl();
	 
		// Get the state generated for you and store it to the session.
		$_SESSION['oauth2state'] = $provider->getState();
	 
		// Redirect the user to the authorization URL.
		header('Location: ' . $authorizationUrl);
		exit;
	 
	// Check given state against previously stored one to mitigate CSRF attack
	} elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
	 
		if (isset($_SESSION['oauth2state'])) {
			unset($_SESSION['oauth2state']);
		}
	   
		exit('Invalid state');
	 
	} else {
	 
		try {
	 
			// Try to get an access token using the authorization code grant.
			$existingAccessToken = $provider->getAccessToken('authorization_code', [
				'code' => $_GET['code']
			]);
			
			$_SESSION['accessTokenToJorani'] = serialize($existingAccessToken);
			
		} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
			// Failed to get the access token or user details.
			exit($e->getMessage());
	 
		}
	}
}

// We have an access token, which we may use in authenticated
// requests against the service provider's API.

// Using the access token, we may look up details about the
// resource owner.
$resourceOwner = $provider->getResourceOwner($existingAccessToken);
$user = $resourceOwner->toArray();

?>
<h1>Jorani OAuth2 demo application</h1>

<p><a href="<?php echo $_SERVER['JORANI_BASE_URL']; ?>">Click here to come back to Jorani</a></p>

<h2>OAuth2 data</h2>

<table class="table">
	<tr>
		<td>Access Token</td><td><?php echo $existingAccessToken->getToken(); ?></td>
	</tr>
	<tr>
		<td>Refresh Token</td><td><?php echo $existingAccessToken->getRefreshToken(); ?></td>
	</tr>
	<tr>
		<td>Expired in</td><td><?php echo $existingAccessToken->getExpires(); ?></td>
	</tr>
	<tr>
		<td>Already expired?</td><td><?php echo ($existingAccessToken ->hasExpired() ? 'expired' : 'not expired'); ?></td>
	</tr>
</table>

<?php

?>

<h2>Who am I?</h2>

<table class="table">
<?php foreach ($user as $key => $value){ ?>
	<tr>
		<td><?php echo $key; ?></td><td><?php echo $value; ?></td>
	</tr>
<?php } ?>
</table>

<?php
        // The provider provides a way to get an authenticated API request for
        // the service, using the access token; it returns an object conforming
        // to Psr\Http\Message\RequestInterface.
        $request = $provider->getAuthenticatedRequest(
            'GET',
            $_SERVER['JORANI_BASE_URL'] . 'api/',
            $existingAccessToken
		);
		//$promise = $client->sendAsync($request)->then(function ($response) {
		//	echo 'I completed! ' . $response->getBody();
		//});
		//$promise->wait();
		//
		//Iterate on the array of leave types
		//...
?>
    </body>
</html>
