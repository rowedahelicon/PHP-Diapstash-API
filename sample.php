<?php

include('diapstash.php');

/*
This is a really rough example of how to use this!
The API is still a work in progress, so I expect a lot of this to change!

Ideally, none of these steps should be done on the same page, especially step one and two
Otherwise, you're generating a new link while handling the link you had just generated
*/

$diapstash = new diapstash('CLIENTID', 'CLIENTSECRET', 'REDIRECT_URI');

//STEP ONE -- Generate a authentication link for me!
$login = $diapstash->getLoginUrl();

echo $login['url']; //This is my login URL
echo $login['code_verifier']; //This is my code verifier! I need to save this!

//STEP TWO -- I have gone to that URL, and authenticated, now I should be seeing a code and state in my return URL!
//MAKE SURE YOU SAVED YOUR CODE VERIFIER FROM STEP 1!

if (isset($_GET['code']) && isset($_GET['state']))
{
    $token = $diapstash->getToken($_GET['code'], $_GET['state'], $login['code_verifier']);
}

// Your access token and refresh token
// I'd store these in an encrypted location because they get switched out a lot
$access_token = $token['access_token'];
$refresh_token = $token['refresh_token'];

//STEP THREE (Sometimes) -- Refreshing our token!
//Did our access token expire? If we saved our refresh token, we can regenerate it here!

$refresh = $diapstash->refreshToken($refresh_token);

//Here are new tokens, save them!
//$refresh['access_token'] //Save me somewhere!
//$refresh['refresh_token'] //Save me somewhere!

//STEP FOUR -- AUTHENTICATE WITH OUR ACCESS TOKEN
$diapstash->generateAuthHeader($access_token);

//We should now be properly authenticated!
$disposables = $diapstash->getDisposables();

//What if want to start from page 10 and only return 5 items per page?
$brands = $diapstash->getBrands(['page' => 10, 'size' => 5]);
