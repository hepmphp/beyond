<?php
/*
 * login_with_odesk.php
 *
 * @(#) $Id: login_with_odesk.php,v 1.1 2014/05/15 09:22:50 mlemos Exp $
 *
 */

/*
 *  Get the http.php file from http://www.phpclasses.org/httpclient
 */
require('http.php');
require('oauth_client.php');

$client = new oauth_client_class();
$client->server = 'oDesk';

$client->debug = false;
$client->debug_http = true;
$client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].
    dirname(strtok($_SERVER['REQUEST_URI'], '?')).'/login_with_odesk.php';

$client->client_id = '';
$application_line = __LINE__;
$client->client_secret = '';

if (strlen($client->client_id) == 0
|| strlen($client->client_secret) == 0) {
    die('Please register a new oDesk application in '.
        'https://www.odesk.com/services/api/apply , and in the line '.
        $application_line.' set the client_id to Key and '.
        'client_secret with Secret. '.
        'The callback URL must be '.$client->redirect_uri);
}

/* API permissions, empty is the default for this application
 */
$client->scope = '';
if (($success = $client->Initialize())) {
    if (($success = $client->Process())) {
        if (strlen($client->authorization_error)) {
            $client->error = $client->authorization_error;
            $success = false;
        } elseif (strlen($client->access_token)) {
            $success = $client->CallAPI(
                'https://www.odesk.com/api/auth/v1/info.json',
                'GET',
                array(),
                array(
                    'FailOnAccessError'=>true,
                    'FollowRedirection'=>true
                ),
                $user
            );
        }
    }
    $success = $client->Finalize($success);
}
if ($client->exit) {
    exit;
}
if ($success) {
    ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>oDesk OAuth client results</title>
</head>
<body>
<?php
            echo '<h1>', HtmlSpecialChars($user->auth_user->first_name),
    ' you have logged in successfully with oDesk!</h1>';
    echo '<pre>', HtmlSpecialChars(print_r($user, 1)), '</pre>';
    ?>
</body>
</html>
<?php
} else {
    ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>OAuth client error</title>
</head>
<body>
<h1>OAuth client error</h1>
<pre>Error: <?php echo HtmlSpecialChars($client->error); ?></pre>
</body>
</html>
<?php
}

?>