<?php

session_start();

require_once 'helper/functions.php';

$isLoggedIn = false; // so it gets declared before passing it in the function
validate_that_user_is_logged_in($isLoggedIn);

// ---

require_once 'helper/database.php';

if (isset($_GET['action'])) {
	header('Content-Type: application/json');

	$db = db_connect();
	if (!$db) {
		http_response_code(500);
		echo json_encode(['error' => 'DB connection failed']);
		exit;
	}

	if ($_GET['action'] === 'fetch_lists') {
		$query = "
			SELECT 
				lists.id AS list_id,
				lists.title AS list_title,
				lists.user_id AS owner_id,
				lists.is_public,
				users.name,
				users.surname,
				users.email
			FROM lists
			JOIN users ON users.id = lists.user_id
			WHERE lists.is_public = 1
		";
		$result = $db->query($query);

		if (!$result) {
			http_response_code(500);
			echo json_encode(['error' => 'Database query failed']);
			exit;
		}

		$lists = [];
		while ($row = $result->fetch_assoc()) {
			$lists[] = $row;
		}

		echo json_encode([
			'current_user_id' => $_SESSION['user_id'] ?? null,
			'lists' => $lists
		]);
		exit;
	}

	if ($_GET['action'] === 'follow_status' && $isLoggedIn) {
		$targetUserId = intval($_GET['user_id'] ?? 0);
		$currentUserId = $_SESSION['user_id'];

		$stmt = $db->prepare("SELECT 1 FROM followers WHERE user_id = ? AND follows_user_id = ?");
		$stmt->bind_param("ii", $currentUserId, $targetUserId);
		$stmt->execute();
		$result = $stmt->get_result();

		echo json_encode([
			'is_following' => $result->num_rows > 0
		]);
		exit;
	}

	if ($_GET['action'] === 'toggle_follow' && $isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$targetUserId = intval($_POST['user_id'] ?? 0);
		$currentUserId = $_SESSION['user_id'];

		if ($currentUserId === $targetUserId) {
			http_response_code(400);
			echo json_encode(['error' => 'Cannot follow yourself']);
			exit;
		}

		$stmt = $db->prepare("SELECT 1 FROM followers WHERE user_id = ? AND follows_user_id = ?");
		$stmt->bind_param("ii", $currentUserId, $targetUserId);
		$stmt->execute();
		$isFollowing = $stmt->get_result()->num_rows > 0;

		if ($isFollowing) {
			$stmt = $db->prepare("DELETE FROM followers WHERE user_id = ? AND follows_user_id = ?");
			$stmt->bind_param("ii", $currentUserId, $targetUserId);
			$stmt->execute();
			echo json_encode(['status' => 'unfollowed']);
		} else {
			$stmt = $db->prepare("INSERT INTO followers (user_id, follows_user_id) VALUES (?, ?)");
			$stmt->bind_param("ii", $currentUserId, $targetUserId);
			$stmt->execute();
			echo json_encode(['status' => 'followed']);
		}

		exit;
	}

	http_response_code(400);
	echo json_encode(['error' => 'Invalid action']);
	exit;
}

// ---

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Λίστες</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php require 'navigation.php' ?>

	<main id="list-entries" class="list-entries">
		<script>
			document.addEventListener("DOMContentLoaded", () => {
				loadLists();
			});

			function loadLists() {
				fetch("lists.php?action=fetch_lists").then(res => res.json()).then(data => {
					const currentUserId = data.current_user_id;
					const lists = data.lists;
					const container = document.getElementById("list-entries");
					container.innerHTML = "";

					lists.forEach(list => {
						const section = document.createElement("section");
						section.className = "list-entry";
						section.dataset.list_id = list.list_id;
						section.id = `${list.list_id}-list`;

						const fullName = `${list.name} ${list.surname}`;
						const dialogIdOwner = `${list.list_id}-list-owner-dialog`;

						section.innerHTML = `
							<div>
								<a href="javascript:;" id="${list.list_id}-list-owner-fullname">${fullName}</a>
								<dialog id="${dialogIdOwner}">
									<h1>${fullName}</h1>
									<h3>${list.email}</h3>
									<div id="${dialogIdOwner}-follow-section"></div>
									<hr class="separator">
									<form method="dialog"><button class="secondary">Κλείσιμο</button></form>
								</dialog>
								<h1>${list.list_title}</h1>
							</div>
							<hr class="separator">
						`;

						container.appendChild(section);

						interactivityFollowing(dialogIdOwner, currentUserId, list)
					});
				});
			}

			function interactivityFollowing(dialogId, currentUserId, list) {
				const dialog = document.getElementById(dialogId);
				const followSection = document.getElementById(`${dialogId}-follow-section`);

				if (!currentUserId || currentUserId == list.owner_id) {
					followSection.innerHTML = "(εσύ είσαι)";
					document.getElementById(`${list.list_id}-list-owner-fullname`).addEventListener("click", () => {
						dialog.showModal();
					});
					return;
				}

				document.getElementById(`${list.list_id}-list-owner-fullname`).addEventListener("click", () => {
					fetch(`lists.php?action=follow_status&user_id=${list.owner_id}`).then(res => res.json()).then(data => {
						const followText = "Ακολούθησε";
						const unfollowText = "Σταμάτα να ακολουθάς";

						const isFollowing = data.is_following;
						const btn = document.createElement("button");

						btn.textContent = isFollowing ? unfollowText : followText;
						btn.addEventListener("click", () => {
							const formData = new FormData();
							formData.append("user_id", list.owner_id);

							fetch("lists.php?action=toggle_follow", {
								method: "POST",
								body: formData
							}).then(res => res.json()).then(data => {
								btn.textContent = data.status === "followed" ? unfollowText : followText;
							});
						});
						followSection.innerHTML = "";
						followSection.appendChild(btn);

						dialog.showModal();
					});
				});
			}
		</script>
	</main>
</body>

</html>