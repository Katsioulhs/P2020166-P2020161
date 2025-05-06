<?php

require_once 'vendor/autoload.php';
require_once 'helper/functions.php';

use Dotenv\Dotenv;

header('Content-Type: application/json');

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();


$isLoggedIn = false;

validate_that_user_is_logged_in($isLoggedIn);
if (!$isLoggedIn) {
	echo json_encode(['status' => 'login']);
	exit();
}

$client = new Google_Client();
$client->setClientId($_ENV['CLIENT_ID']);
$client->setClientSecret($_ENV['CLIENT_SECRET']);
$client->setRedirectUri($_ENV['REDIRECT_URI']);
$client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);

if (isset($_GET['code'])) {
	$client->authenticate($_GET['code']);
	$_SESSION['access_token'] = $client->getAccessToken();
	header('Location: ' . filter_var($client->getRedirectUri(), FILTER_SANITIZE_URL));
	exit();
}

try {
	if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
		$client->setAccessToken($_SESSION['access_token']);
		$youtube = new Google_Service_YouTube($client);

		$query = isset($_GET['query']) ? trim($_GET['query']) : '';

		if ($query === '') {
			echo json_encode([
				'status' => 'error',
				'message' => 'Missing or empty search query.'
			]);
			exit();
		}

		$searchResponse = $youtube->search->listSearch('snippet', [
			'q' => $query,
			'maxResults' => 7,
			'type' => 'video',
		]);

		$entries = [];
		foreach ($searchResponse['items'] as $searchResult) {
			if (isset($searchResult['id']['videoId'])) {
				$entries[] = [
					'title' => $searchResult['snippet']['title'],
					'videoId' => $searchResult['id']['videoId'],
				];
			}
		}

		echo json_encode([
			'status' => 'success',
			'entries' => $entries
		]);
	} else {
		echo json_encode(['status' => 'auth', 'url' => $client->createAuthUrl()]);
	}
} catch (Google\Service\Exception $e) {
	echo json_encode(['status' => 'auth', 'url' => $client->createAuthUrl()]);
} catch (Exception $e) {
	echo json_encode([
		'status' => 'error',
		'message' => 'Unexpected error: ' . $e->getMessage()
	]);
}