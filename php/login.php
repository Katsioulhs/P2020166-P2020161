<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$password = trim($_POST['password'] ?? '');

	unset($_POST['password']);
	$redirectParams = $_POST;

	// ---

	$errors = [];

	if ($username === '') {
		$errors[] = "Το username είναι υποχρεωτικό.";
	}

	if ($password === '') {
		$errors[] = "Το password είναι υποχρεωτικό.";
	}

	if (!empty($errors)) {
		$redirectParams['status'] = 'error';
		$redirectParams['reason'] = $errors[0];

		header('Location: login.php?' . http_build_query($redirectParams));
		exit;
	}

	require 'helper/database.php';
	$db = db_connect();

	if ($db === null) {
		$redirectParams['status'] = 'error';
		$redirectParams['reason'] = "Αποτυχία σύνδεσης με βάση δεδομένων.";

		header('Location: login.php?' . http_build_query($redirectParams));
		exit;
	}

	$stmt = $db->prepare("SELECT id, password FROM users WHERE username = ?");

	if ($stmt === false) {
		db_disconnect($db);

		$redirectParams['status'] = 'error';
		$redirectParams['reason'] = 'Σφάλμα βάσης δεδομένων.';

		header('Location: login.php?' . http_build_query($redirectParams));
		exit;
	}

	$stmt->bind_param("s", $username);

	$stmt->execute();

	$result = $stmt->get_result();

	if ($result->num_rows === 0) {
		$stmt->close();

		db_disconnect($db);

		$redirectParams['status'] = 'error';
		$redirectParams['reason'] = "Λάθος διαπιστευτήρια. Δεν βρέθηκε αντίστοιχος χρήστης.";

		header('Location: login.php?' . http_build_query($redirectParams));
		exit;
	}

	$user = $result->fetch_assoc();

	$stmt->close();

	db_disconnect($db);

	if (password_verify($password, $user['password']) === false) {
		$redirectParams['status'] = 'error';
		$redirectParams['reason'] = "Λάθος διαπιστευτήρια. Δεν βρέθηκε αντίστοιχος χρήστης.";

		header('Location: login.php?' . http_build_query($redirectParams));
		exit;
	}

	$_SESSION['user_id'] = $user['id'];

	header("Location: index.php?status=success.login");
	exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Σύνδεση</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php require 'navigation.php' ?>

	<main class="narrow">
		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
			<fieldset>
				<legend>Username</legend>
				<input required type="text" name="username" value="<?= htmlspecialchars($_GET['username'] ?? '') ?>">
			</fieldset>

			<fieldset>
				<legend>Password</legend>
				<input required type="password" id="password" name="password">
				<label><input type="checkbox" id="show-passwords" style="margin-right: .5rem;">εμφάνισε</label>
				<script>
					document.getElementById('show-passwords').addEventListener('change', function () {
						document.getElementById('password').type = this.checked ? 'text' : 'password';
					});
				</script>
			</fieldset>

			<div class="buttons">
				<button type="submit">Συνέχεια</button>
				<button type="reset" class="secondary">Καθαρισμός</button>
			</div>
		</form>

		<?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['status']) && $_GET['status'] === 'error'): ?>
			<dialog id="result">
				<h1>Αποτυχία!</h1>
				<p><?= htmlspecialchars($_GET['reason'] ?? 'Άγνωστο σφάλμα...') ?></p>
				<form method="dialog">
					<button>Κλείσιμο</button>
				</form>
			</dialog>
			<script>
				document.getElementById('result')?.showModal();
			</script>
		<?php endif; ?>
	</main>
</body>

</html>