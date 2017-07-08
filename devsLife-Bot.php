#!/usr/bin/env php
<?php

// Call set_include_path() as needed to point to your client library.
if (!file_exists($file = __DIR__ . '/vendor/autoload.php')) {
    throw new \Exception('please run "composer install" in "' . __DIR__ .'"');
}
require_once __DIR__ . '/vendor/autoload.php';

use RestCord\DiscordClient;


// DB info (production only)
$DB_USER = '';
$DB_NAME = '';
$DB_PASSWD = '';
$DB_HOST = '';
$error_log = '';

// Discord info
$DISCORD_TOKEN = 'App-Bot-Token';
$DISCORD_GUID_ID = 'Guild_ID'; // server id
$DISCORD_CHANNEL_ID = 'Channel-ID';

// Google info
$GOOGLE_APPLICATION_NAME = 'App-Name';
$GOOGLE_DEV_API = 'API-Key';

// YouTube Channel ID
$YOUTUBE_CHANNEL_ID = 'UCu1xbgCV5o48h_BYCQD7KJg';

// Initialize Discord client object
$discord = new DiscordClient(['token' => $DISCORD_TOKEN]);
$discord->guild->getGuild(['guild.id' => $DISCORD_GUID_ID]);

// Initialize Google client object
$client = new Google_Client();
$client->setApplicationName($GOOGLE_APPLICATION_NAME);
$client->setDeveloperKey($GOOGLE_DEV_API);

// Object that will be used to make all YouTube API requests
$youtube = new Google_Service_YouTube($client);

// Optional CLI options
$shortopts = 'd::';
$longopts  = array('development::', 'dev::');
$options = getopt($shortopts, $longopts);

$DEV = false;

if (!empty($options)) {
	echo "\nRunning devsLife-Bot in development mode.\n\n";
	$DEV = true;
}

try {
	$searchResponse = $youtube->search->listSearch('id, snippet', array(
		'channelId' => $YOUTUBE_CHANNEL_ID,
		'type' => 'recentUpload',
		'maxResults' => 1,
		'order' => 'date',
		));

	//print_r($searchResponse);
	if(empty($searchResponse))
		die("Received an empty responce from Google's YouTube Data API\n");

} catch (Exception $e) {
	echo 'Failed to make Google YouTube Data v3 API Call: ',  $e->getMessage(), "\n";
	exit(1);
}

if ($DEV) {
	if (dev($searchResponse['modelData']['items'][0]))
		message($searchResponse['modelData']['items'][0]);
} else { 
	if (prod($searchResponse['modelData']['items'][0]))
		message($searchResponse['modelData']['items'][0]);
}

// Bot environments
function prod($response) {
	global $DB_HOST, $DB_USER, $DB_PASSWD, $DB_NAME;
	
	$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASSWD, $DB_NAME);
	if ($mysqli->connect_errno) {
    	die("Failed to connect to MySQL: " . $mysqli->connect_error . "\n");
	}

	// Check if it's a new video post
	if ($stmt = $mysqli->prepare("SELECT count(*) FROM `video_uploads` WHERE videoId=? AND publishedAt=? AND live=?")) {

		$stmt->bind_param('sss', $response['id']['videoId'], $response['snippet']['publishedAt'], $response['snippet']['liveBroadcastContent']);
		$stmt->execute();
		$stmt->bind_result($check_rows);
		$stmt->fetch();
		$stmt->close();

		// Insert Video data onto database
		if(!$check_rows) {
			if ($stmt = $mysqli->prepare("INSERT INTO `video_uploads` (`channelId`, `videoId`, `publishedAt`, `live`, `title`)
			VALUES (?, ?, ?, ?, ?)")) {
				$stmt->bind_param('sssss',
					$response['snippet']['channelId'],
					$response['id']['videoId'],
					$response['snippet']['publishedAt'],
					$response['snippet']['liveBroadcastContent'],
					$response['snippet']['title']);
				
				$stmt->execute();
				$stmt->close();
			} else {
				die($mysqli->error . "\n");
			}
		} else {
			echo "No new YouTube Live Stream or Video Upload detected.\n";
			return false;
		}

		return true;
	}

}

function dev($response) {
	if(!file_exists('.development'))
		mkdir('.development');

	$log_files = glob('.development/*.log');

	$response_array = array('channelId'    => $response['snippet']['channelId'],
					'videoId'              => $response['id']['videoId'],
					'publishedAt'          => $response['snippet']['publishedAt'],
					'liveBroadcastContent' => $response['snippet']['liveBroadcastContent'],
					'title'                => $response['snippet']['title']);

	if (empty($log_files)) {
		file_put_contents('.development/' . rand() . '.log', base64_encode(serialize($response_array)));

	} else {
		foreach ($log_files as $log){
			// If any of the logged data matches with our response
			if(!array_diff(unserialize(base64_decode(file_get_contents($log))), $response_array)) {
				echo "No new YouTube Live Stream or Video Upload detected.\n";
				return false;
			} 
		}
		// Checked all files and didn't find any matches, so it must be a new stream/upload
		file_put_contents('.development/' . rand() . '.log', base64_encode(serialize($response_array)));
	}
	return true;
	
}

// Message Discord
function message($response) {
	global $discord, $DISCORD_CHANNEL_ID;

	$message = ($response['snippet']['liveBroadcastContent'] == 'live' ? ':red_circle: New Live Stream Started!' : ':camera: New Video Uploaded!');
	
	try {
		$discord->channel->createMessage(['channel.id' => $DISCORD_CHANNEL_ID,
				'content' => "$message \nhttps://www.youtube.com/watch?v={$response['id']['videoId']}"]);
	} catch (Exception $e) {
		echo 'Failed to send message to Discord: ',  $e->getMessage(), "\n";
	}
}
