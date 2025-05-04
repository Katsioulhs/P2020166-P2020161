<?php

session_start();

require_once 'helper/functions.php';

$isLoggedIn = false; // so it gets declared before passing it in the function
validate_that_user_is_logged_in($isLoggedIn);

// ---

require_once 'helper/database.php';

function fetchFilteredLists(mysqli $db, array $filters = [], ?int $currentUserId = null): array
{
	global $isLoggedIn;

	$conditions = [];
	$params = [];
	$types = [];
	$joins = [];

	if (!$isLoggedIn) {
		$conditions[] = "lists.is_public = TRUE";
	} else {
		switch ($filters['visibility'] ?? 'all') {
			case 'public':
				$conditions[] = "lists.is_public = TRUE";
				break;
			case 'private':
				$conditions[] = "lists.is_public = FALSE AND lists.user_id = ?";
				$params[] = $currentUserId;
				$types[] = "i";
				break;
			default: // & case 'all':
				$conditions[] = "(lists.is_public = TRUE OR lists.user_id = ?)";
				$params[] = $currentUserId;
				$types[] = "i";
		}
	}

	if ($isLoggedIn) {
		switch ($filters['owner'] ?? 'all') {
			case 'me':
				$conditions[] = "lists.user_id = ?";
				$params[] = $currentUserId;
				$types[] = "i";
				break;
			case 'following':
				$conditions[] = "lists.user_id IN (
					SELECT follows_user_id FROM followers WHERE user_id = ?
				)";
				$params[] = $currentUserId;
				$types[] = "i";
				break;
		}
	}

	if (!empty($filters['list_title'])) {
		$conditions[] = "lists.title LIKE ?";
		$params[] = "%" . $filters['list_title'] . "%";
		$types[] = "s";
	}

	$needsVideoJoin = false;

	if (!empty($filters['video_title'])) {
		$conditions[] = "videos.title LIKE ?";
		$params[] = "%" . $filters['video_title'] . "%";
		$types[] = "s";
		$needsVideoJoin = true;
	}

	if (!empty($filters['date_from'])) {
		$conditions[] = "videos.added_at >= ?";
		$params[] = $filters['date_from'];
		$types[] = "s";
		$needsVideoJoin = true;
	}
	
	if (!empty($filters['date_to'])) {
		$conditions[] = "videos.added_at <= ?";
		$params[] = $filters['date_to'];
		$types[] = "s";
		$needsVideoJoin = true;
	}

	if ($needsVideoJoin) {
		$joins[] = "JOIN videos ON videos.list_id = lists.id";
	}

	if (!empty($filters['user_info'])) {
		$search = "%" . $filters['user_info'] . "%";
		$conditions[] = "(users.name LIKE ? OR users.surname LIKE ? OR users.email LIKE ? OR users.username LIKE ?)";
		array_push($params, $search, $search, $search, $search);
		array_push($types, "s", "s", "s", "s");
	}

	$whereClause = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";
	$joinClause = $joins ? implode(" ", $joins) : "";

	$query = "
		SELECT 
			DISTINCT lists.id AS list_id,
			lists.title AS list_title,
			lists.user_id AS owner_id,
			lists.is_public,
			users.id AS user_id,
			users.name,
			users.surname,
			users.email
		FROM lists
		JOIN users ON users.id = lists.user_id
		$joinClause
		$whereClause
	";

	$stmt = $db->prepare($query);
	if (!$stmt) {
		throw new RuntimeException("Database error: " . $db->error);
	}

	if (!empty($types)) {
		$stmt->bind_param(implode("", $types), ...$params);
	}

	$stmt->execute();
	$result = $stmt->get_result();

	$lists = [];
	while ($row = $result->fetch_assoc()) {
		$lists[] = $row;
	}

	return $lists;
}

if (isset($_GET['action'])) {
	header('Content-Type: application/json');

	$db = db_connect();
	if (!$db) {
		http_response_code(500);
		echo json_encode(['error' => 'DB connection failed']);
		exit;
	}

	if ($_GET['action'] === 'fetch_lists') {
		$currentUserId = $_SESSION['user_id'] ?? null;

		$lists = fetchFilteredLists($db, $_GET, $currentUserId);

		echo json_encode([
			'current_user_id' => $currentUserId,
			'lists' => $lists
		]);
		exit;
	} else if ($_GET['action'] === 'follow_status' && $isLoggedIn) {
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
	} else if ($_GET['action'] === 'toggle_follow' && $isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
	} else if ($_GET['action'] === 'get_list_content') {
		$listId = intval($_GET['list_id'] ?? 0);
		$currentUserId = $_SESSION['user_id'] ?? null;

		$stmt = $db->prepare("
			SELECT user_id, is_public
			FROM lists
			WHERE id = ?
		");
		$stmt->bind_param("i", $listId);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows === 0) {
			http_response_code(404);
			echo json_encode(['error' => 'List not found']);
			exit;
		}

		$row = $result->fetch_assoc();

		$isPublic = $row['is_public'];
		$listOwnerId = $row['user_id'];

		if (!$currentUserId && !$isPublic) {
			http_response_code(403);
			echo json_encode(['error' => 'Access denied']);
			exit;
		}

		if ($currentUserId && (!$isPublic && $currentUserId !== $listOwnerId)) {
			http_response_code(403);
			echo json_encode(['error' => 'Access denied']);
			exit;
		}

		$stmt = $db->prepare("
			SELECT title, added_at, youtube_id
			FROM videos
			WHERE list_id = ?
			ORDER BY added_at DESC
		");
		$stmt->bind_param("i", $listId);
		$stmt->execute();
		$contentResult = $stmt->get_result();

		$content = [];
		while ($video = $contentResult->fetch_assoc()) {
			$content[] = $video;
		}

		echo json_encode($content);
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

	<aside class="container horizontal" style="justify-content: center; width: 100%">
		<button id="open-search-dialog" onclick="document.getElementById('search-dialog').showModal();">Αναζήτηση</button>

		<dialog id="search-dialog" class="search">
			<h1>Αναζήτηση</h1>

			<hr class="separator">

			<form id="search-form" method="dialog">
				<fieldset>
					<legend>Τίτλος λίστας</legend>
					<input type="text" name="list_title">
				</fieldset>
				<fieldset>
					<legend>Τίτλος βίντεο</legend>
					<input type="text" name="video_title">
				</fieldset>
				<fieldset>
					<legend>Περίοδος (από - έως)</legend>
					<input type="datetime-local" name="date_from" step=1>
					<input type="datetime-local" name="date_to" step=1>
				</fieldset>
				<fieldset>
					<legend>Στοιχεία χρήστη</legend>
					<input type="text" name="user_info" placeholder="πχ όνομα, επώνυμο, email, username...">
				</fieldset>

				<?php if ($isLoggedIn): ?>
					<fieldset>
						<legend>Πρόσβαση</legend>
						<select name="visibility">
							<option value="all" selected>Οποιαδήποτε</option>
							<option value="public">Δημόσια</option>
							<option value="private">Ιδιωτική</option>
						</select>
					</fieldset>
					<fieldset>
						<legend>Δημιουργός</legend>
						<select name="owner">
							<option value="all" selected>Οποιοσδήποτε</option>
							<option value="me">Εγώ</option>
							<option value="following">Ακολουθούμενοι</option>
						</select>
					</fieldset>
				<?php endif; ?>

				<hr class="separator">

				<div class="container horizontal">
					<button type="submit">Συνέχεια</button>
					<button class="secondary" type="reset">Καθαρισμός</button>
					<button class="secondary" type="button" onclick="document.getElementById('search-dialog').close()">Άκυρο</button>
				</div>
			</form>
		</dialog>

		<?php if ($isLoggedIn): ?>
			<button id="create-list">Δημιουργία</button>
			<button id="export-lists" class="secondary">Εξαγωγή</button>
		<?php endif; ?>
	</aside>

	<main id="list-entries" class="list-entries">
		<script>
			document.addEventListener("DOMContentLoaded", () => {
				loadLists();

				document.getElementById("search-form").addEventListener("submit", (e) => {
					const formData = new FormData(e.target);

					const dateFrom = formData.get("date_from");
					const dateTo = formData.get("date_to");

					if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
						alert('Η ημερομηνία "από" πρέπει να είναι πριν την ημερομηνία "έως".');
						e.preventDefault();
						return;
					}

					loadLists(formData);
				});
			});

			function loadLists(formData = new FormData()) {
				formData.set("action", "fetch_lists");

				fetch(`lists.php?${new URLSearchParams(formData).toString()}`).then(res => res.json()).then(data => {
					const currentUserId = data.current_user_id;
					const lists = data.lists;
					const container = document.getElementById("list-entries");
					container.innerHTML = "";

					if (lists.length === 0) {
						container.innerHTML = "<p>Δεν υπάρχουν διαθέσιμες λίστες για προβολή.</p>";
						return;
					}

					lists.forEach(list => {
						const section = document.createElement("section");
						section.className = "list-entry";
						section.dataset.list_id = list.list_id;
						section.id = `${list.list_id}-list`;

						const fullName = `${list.name} ${list.surname}`;
						const dialogIdOwner = `${list.list_id}-list-owner-dialog`;
						const dialogIdContent = `${list.list_id}-list-content-dialog`;

						section.innerHTML = `
							<div>
								<a href="javascript:;" id="${list.list_id}-list-owner-fullname">${fullName}</a>
								<dialog id="${dialogIdOwner}">
									<div>
										<h1>${fullName}</h1>
										<h3>${list.email}</h3>
									</div>
									<div id="${dialogIdOwner}-follow-section"></div>
									<hr class="separator">
									<form method="dialog"><button class="secondary">Κλείσιμο</button></form>
								</dialog>
								<h1>${list.list_title}</h1>
							</div>
							<hr class="separator">
							<div class="container horizontal">
								<button id="${list.list_id}-list-videos-show">Περιεχόμενο</button>
								<dialog id="${dialogIdContent}">
									<div>
										<h1>${list.list_title}</h1>
										<h3>${fullName}</h3>
									</div>
									<hr class="separator">
									<div id="${dialogIdContent}-content-section" style="overflow-y: auto; max-height: 50dvh;"></div>
									<hr class="separator">
									<form method="dialog"><button class="secondary">Κλείσιμο</button></form>
								</dialog>
								<button id="${list.list_id}-list-videos-edit" class="secondary" style="display: none"><!-- TODO -->Επεξεργασία</button>
							</div>
						`;

						container.appendChild(section);

						interactivityFollowing(dialogIdOwner, currentUserId, list);
						interactivityVideos(dialogIdContent, list);
					});
				});
			}

			function interactivityFollowing(dialogId, currentUserId, list) {
				const dialog = document.getElementById(dialogId);
				const followSection = document.getElementById(`${dialogId}-follow-section`);

				if (!currentUserId || currentUserId == list.owner_id) {
					followSection.style.display = "none";
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

			function interactivityVideos(dialogId, list) {
				const dialog = document.getElementById(dialogId);
				const videos = document.getElementById(`${dialogId}-content-section`);

				document.getElementById(`${list.list_id}-list-videos-show`).addEventListener("click", () => {
					fetch(`lists.php?action=get_list_content&list_id=${list.list_id}`).then(res => res.json()).then(data => {
						if (data.length === 0) {
							videos.innerHTML = "<p>Δεν υπάρχουν βίντεο σε αυτή τη λίστα.</p>";
						} else {
							videos.innerHTML = "";

							data.forEach(video => {
								videos.innerHTML += `
									<div class="video" onclick="openVideoDialog('https://www.youtube.com/embed/${video.youtube_id}')">
										<h1>${video.title}</h1>
										<h3>Προστέθηκε: ${new Date(video.added_at).toLocaleString()}</h3>
									</div>
								`;
							});
						}

						dialog.showModal();
					});
				});
			}
		</script>
	</main>

	<dialog id="videoDialog" style="max-width: none;">
		<iframe id="videoIframe" width="660" height="375" frameborder="0" allowfullscreen></iframe>
		<form method="dialog"><button class="secondary">Κλείσιμο</button></form>
	</dialog>
	<script>
		function openVideoDialog(url) {
			document.getElementById('videoIframe').src = url;
			document.getElementById('videoDialog').showModal();
		}

		document.getElementById('videoDialog').addEventListener("close", () => {
			document.getElementById('videoIframe').src = "";
		});
	</script>
</body>

</html>