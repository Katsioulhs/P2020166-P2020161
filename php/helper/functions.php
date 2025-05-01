<?php

function logout(string|false $redirectUri = 'login.php'): void
{
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	session_destroy();
	$_SESSION = NULL;

	if ($redirectUri !== false) {
		header("Location: " . $redirectUri);
		exit;
	}
}

function validate_that_user_is_logged_in(&$is = null): void
{
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	$loggedIn = isset($_SESSION['user_id']);

	if ($loggedIn) {
		require_once 'database.php';
		$db = db_connect();

		if ($db === null) {
			$loggedIn = false;
		} else {
			$stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
			if ($stmt === false) {
				$loggedIn = false;
			} else {
				$stmt->bind_param("i", $_SESSION['user_id']);
				$stmt->execute();
				$result = $stmt->get_result();

				if ($result->num_rows === 0) {
					$loggedIn = false;
				}

				$stmt->close();
			}

			db_disconnect($db);
		}
	}

	if ($is !== null) {
		$is = $loggedIn;
		if ($loggedIn === false) {
			logout(false);
		}
	} else {
		if ($loggedIn === false) {
			logout();
			exit;
		}
	}
}
