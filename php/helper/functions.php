<?php

function logout($redirectUri = 'login.php'): void
{
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	session_destroy();
	$_SESSION = NULL;

	header("Location: " . $redirectUri);
	exit;
}

function validate_that_user_is_logged_in(): void
{
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	if (!isset($_SESSION['user_id'])) {
		header("Location: login.php");
		exit;
	}

	require_once 'database.php';
	$db = db_connect();

	if ($db === null) {
		header("Location: login.php");
		exit;
	}

	$stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
	if ($stmt === false) {
		db_disconnect($db);
		header("Location: login.php");
		exit;
	}

	$stmt->bind_param("i", $_SESSION['user_id']);
	$stmt->execute();
	$result = $stmt->get_result();

	if ($result->num_rows === 0) {
		$stmt->close();
		db_disconnect($db);
		unset($_SESSION['user_id']); // optional: clear it
		header("Location: login.php");
		exit;
	}

	$stmt->close();

	db_disconnect($db);
}

