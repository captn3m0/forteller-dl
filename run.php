#!/usr/bin/env php
<?php

if(!isset($_ENV['FORTELLER_EMAIL']) || !isset($_ENV['FORTELLER_PASSWORD'])) {
  die("Please set FORTELLER_EMAIL and FORTELLER_PASSWORD in the environment variables.\n");
}

const GAME_BASE_URL = 'https://us-central1-forteller-platform.cloudfunctions.net/games';
const AUDIO_BASE_URL = 'https://us-central1-forteller-platform.cloudfunctions.net/audio/';
const LOGIN_URL = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword';
const TOKEN_URL = 'https://securetoken.googleapis.com/v1/token?key=';
const APP_KEY = 'AIzaSyAs2zSk1Xx-yq6pu4GNCqOUPLuCD1HPDYo';

function doCurl($url, $type, $header=[], $body="") {
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $type);
    curl_setopt($handle, CURLOPT_HEADER, true);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_HTTPHEADER, $header);
    curl_setopt($handle, CURLOPT_POSTFIELDS, $body);

    $response = curl_exec($handle);
    $hlength  = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    $body     = substr($response, $hlength);

    curl_close($handle);

    // If HTTP response is not 200, throw exception
    if ($httpCode != 200) {
        throw new Exception("$httpCode: $body");
    }

    return $body;
}

function getRefreshToken() {
  $response = doCurl(
    LOGIN_URL . "?key=" . APP_KEY,
    "POST",
    ['Content-Type: application/json'],
    json_encode([
      "email" => $_ENV['FORTELLER_EMAIL'],
      "returnSecureToken" => true,
      "password"=> $_ENV['FORTELLER_PASSWORD']
    ])
  );

  if (!$response) {
    die("ERROR: Recieved empty refresh token.\n");
  }

  return json_decode($response)->refreshToken;
}

function getAccessToken($refreshToken){
  $response = doCurl(
    TOKEN_URL . APP_KEY,
    'POST',
    ['Content-Type: application/json'],
    json_encode([
      "grantType" => "refresh_token",
      "refreshToken" => $refreshToken]
    )
  );

  if (!$response) {
    die("ERROR: Recieved empty access token.\n");
  }

  return json_decode($response)->access_token;
}

function login() {
  $refreshToken = getRefreshToken();
  return getAccessToken($refreshToken);
}

function request($url, $accept = 'application/json') {
  global $jwt;

  return doCurl(
    $url,
    'GET',
    [
        "Accept: $accept",
        "User-Agent: Forteller%20Stage/1593634637 CFNetwork/1237 Darwin/20.4.0",
        "Authorization: Bearer $jwt"
    ]
  );
}

// ---------------------------------------
// Main Logic
// ---------------------------------------

if ($argc<2) {
  die("Please pass one of the SKUs: ceph_gh ceph_jaws suc_mid1 ceph_fh skg_iso");
}

if (!in_array($argv[1], ['ceph_gh','ceph_jaws','suc_mid1','ceph_fh','skg_iso'])) {
  die("Invalid SKU");
}

$basedir = getcwd();
if (isset($argv[2])) {
  @mkdir($argv[2]);
  $basedir = $argv[2];
  echo("Saving files in $basedir\n");
}

$jwt = login();
$sku = $argv[1];
$game = null;

foreach (json_decode(request(GAME_BASE_URL), true) as $game) {
  if ($game['sku'] == $sku) {
    break;
  }
}

$gameName = $game['name'];

@mkdir("$basedir/$gameName");
$containerUrl = GAME_BASE_URL . "/" . $game['id'] . "/containers";
$containers = request($containerUrl);

foreach (json_decode($containers, true) as $c) {
  $cid = $c['id'];
  $scenarioName = str_replace('#','', $c['name']);
  $itemsUrl = $containerUrl .  "/$cid/items";
  $data = json_decode(request($itemsUrl), true);

  foreach ($data as $track) {
    $trackName = $track['name'];
    $streamUrlParts = explode('/', $track['streamUri']);
    $trackUrl = AUDIO_BASE_URL . $streamUrlParts[2] . '/' . $streamUrlParts[3] . '/' . $streamUrlParts[4];
    $filename = "$basedir/$gameName/$scenarioName - $trackName.mp3";
    echo "$filename\n";

    if (!file_exists($filename)) {
      $track_data = request($trackUrl, 'audio/mpeg');

      if($track_data) {
        file_put_contents($filename, $track_data);
      } else {
        die("Failed to download $trackUrl\n");
      }
    }
  }
}
