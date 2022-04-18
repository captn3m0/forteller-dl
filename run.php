#!/usr/bin/env php
<?php

if(!isset($_ENV['FORTELLER_EMAIL']) || !isset($_ENV['FORTELLER_PASSWORD'])) {
  die("Please set FORTELLER_EMAIL and FORTELLER_PASSWORD in the environment variables");
}

const GAME_BASE_URL = 'https://us-central1-forteller-platform.cloudfunctions.net/games';
const AUDIO_BASE_URL = 'https://us-central1-forteller-platform.cloudfunctions.net/audio/';
const LOGIN_URL = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword';
const TOKEN_URL = 'https://securetoken.googleapis.com/v1/token?key=';
const APP_KEY = 'AIzaSyAs2zSk1Xx-yq6pu4GNCqOUPLuCD1HPDYo';

function getRefreshToken() {
  $curl = curl_init();
  $body = json_encode(["email"=>$_ENV['FORTELLER_EMAIL'],"returnSecureToken"=>true,"password"=>$_ENV['FORTELLER_PASSWORD']]);

  curl_setopt_array($curl, [
    CURLOPT_URL => "https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword?key=" . APP_KEY,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => $body,
  ]);

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    die("cURL Error #:" . $err);
  }
  return json_decode($response)->refreshToken;
}

function getAccessToken($refreshToken){
  $curl = curl_init();
  $body = json_encode(["grantType"=>"refresh_token","refreshToken"=>$refreshToken]);

  curl_setopt_array($curl, [
    CURLOPT_URL => TOKEN_URL . APP_KEY,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => [
      'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => $body,
  ]);

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    die("cURL Error #:" . $err);
  }
  return json_decode($response)->access_token;
}

function login() {
  $refreshToken = getRefreshToken();
  return getAccessToken($refreshToken);
}

function request($url, $accept = 'application/json') {
  global $jwt;
  $opts = [
    "http" => [
        "method" => "GET",
        "header" => "Accept: $accept\r\n" .
                    "User-Agent: Forteller%20Stage/1593634637 CFNetwork/1237 Darwin/20.4.0\r\n" .
                    "Authorization: Bearer $jwt\r\n"
    ]
  ];
  $context = stream_context_create($opts);
  return file_get_contents($url, false, $context);
}

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
        die("Failed to download $trackUrl");
      }
    }
  }
}