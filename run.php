<?php

const BASE_URL = 'https://us-central1-forteller-platform.cloudfunctions.net/games/FF2K2f7IRo6VvzacwW3v/containers/';
const AUDIO_BASE_URL = 'https://us-central1-forteller-platform.cloudfunctions.net/audio/zOoRCBJBRD47XjQAmAkS/';
const LOGIN_URL = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword';
const APP_KEY = 'AIzaSyAs2zSk1Xx-yq6pu4GNCqOUPLuCD1HPDYo';

const EMAIL = 'YOUR_EMAIL_GOES_HERE';
const PASSWORD = 'YOUR_FORETELLER_PASSWORD_GOES_HERE';

function login() {
  $body = json_encode(["email"=>EMAIL,"returnSecureToken"=>false,"password"=>PASSWORD]);

  echo $body;exit;

  $ch = curl_init('https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword?key=' . APP_KEY);
  curl_setopt_array($ch, array(
    CURLOPT_POST => TRUE,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_POSTFIELDS => $body
  ));
  $response = curl_exec($ch);

  if($response === false){
      die(curl_error($ch));
  }
  $responseData = json_decode($response, true);

  return $responseData['idToken'];
}
function request($url, $accept = 'application/json') {

  global $jwt;
  $opts = [
    "http" => [
        "method" => "GET",
        "header" => "Accept: $accept\r\n" .
                  "Authorization: Bearer $jwt\r\n"
    ]
  ];
  $context = stream_context_create($opts);
  return file_get_contents($url, false, $context);
}

$jwt = login();
$scenarios = json_decode(file_get_contents('scenarios.json'), true);

foreach ($scenarios as $s) {
  $sid = $s['id'];
  $scenarioName = $s['name'];
  @mkdir($scenarioName, 0777);
  $itemsUrl = BASE_URL .  $sid . '/items';
  $data = json_decode(request($itemsUrl), true);

  foreach ($data as $track) {
    $trackName = $track['name'];

    $streamUrlParts = explode('/', $track['streamUri']);

    $trackUrl = AUDIO_BASE_URL . $streamUrlParts[3] . '/' . $streamUrlParts[4];
    $filename = "$scenarioName/$trackName.mp3";
    echo "$filename\n";
    if (!file_exists($filename)) {
      $track_data = request($trackUrl, 'audio/mpeg');
      file_put_contents($filename, $track_data);
    }
  }
}