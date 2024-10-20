<?php
/*
 * login_with_salesforce.php
 *
 * @(#) $Id: login_with_salesforce.php,v 1.2 2013/07/31 11:48:04 mlemos Exp $
 *
 */

/*
 *  Get the http.php file from http://www.phpclasses.org/httpclient
 */
require('http.php');
require('oauth_client.php');

$client = new oauth_client_class();
$client->server = 'Salesforce';

$client->debug = true;
$client->debug_http = true;
$client->redirect_uri = 'https://'.$_SERVER['HTTP_HOST'].
    dirname(strtok($_SERVER['REQUEST_URI'], '?')).'/login_with_salesforce.php';

$client->client_id = '';
$application_line = __LINE__;
$client->client_secret = '';

if (strlen($client->client_id) == 0
|| strlen($client->client_secret) == 0) {
    die('Please login with your Salesforce developer account, click on the'.
        ' Setup link, then click on the Develop link, then click on the '.
        'Remote Access link, create a new connected app, and in the line '.
        $application_line.' set the client_id to Consumer key and '.
        'client_secret with Consumer Secret. '.
        'The callback URL must be '.$client->redirect_uri.' but make sure '.
        'it is a secure URL (https://).');
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
            /*
             * Requests just to get just the user details can be obtained
             * sending a request to the URL defined by the variable:
             * $client->access_token_response['id']
             *
             * Other API requests need to be sent to the base URL defined by:
             * $client->access_token_response['instance_url']
             */
            $success = $client->CallAPI(
                $client->access_token_response['id'],
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
<title>Salesforce OAuth client results</title>
</head>
<body>
<?php
            echo '<h1>', HtmlSpecialChars($user->first_name),
    ' you have logged in successfully with Salesforce!</h1>';
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