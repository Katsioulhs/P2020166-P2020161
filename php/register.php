<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$surname = trim($_POST['surname'] ?? '');
	$username = trim($_POST['username'] ?? '');
	$password = trim($_POST['password'] ?? '');
	$email = trim($_POST['email'] ?? '');

	unset($_POST['password']);
	$redirectParams = $_POST;

	// ---

	$errors = [];

	if ($name === '') {
		$errors[] = "Το όνομα είναι υποχρεωτικό.";
	}

	if ($surname === '') {
		$errors[] = "Το επώνυμο είναι υποχρεωτικό.";
	}

	if ($username === '') {
		$errors[] = "Το username είναι υποχρεωτικό.";
	}

	if ($password === '') {
		$errors[] = "Το password είναι υποχρεωτικό.";
	}

	if ($email === '') {
		$errors[] = "Το email είναι υποχρεωτικό.";
	} elseif (!str_contains($email, '@')) {
		$errors[] = "Το email δεν είναι έγκυρο.";
	}

	if (!empty($errors)) {
		$redirectParams['status'] = 'error';
		$redirectParams['reason'] = $errors[0];

		header('Location: register.php?' . http_build_query($redirectParams));
		exit;
	}

	// ---

	require 'helper/database.php';
	$db = db_connect();

	if ($db === null) {
		$redirectParams['status'] = 'error';
		$redirectParams['reason'] = "Αποτυχία σύνδεσης με βάση δεδομένων.";

		header('Location: register.php?' . http_build_query($redirectParams));
		exit;
	}

	$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

	$stmt = $db->prepare("
		INSERT INTO users (name, surname, username, password, email)
		VALUES (?, ?, ?, ?, ?)
	");

	if ($stmt === false) {
		db_disconnect($db);

		$redirectParams['status'] = 'error';
		$redirectParams['reason'] = "Σφάλμα βάσης δεδομένων.";

		header('Location: register.php?' . http_build_query($redirectParams));
		exit;
	}

	// ---

	$stmt->bind_param("sssss", $name, $surname, $username, $hashedPassword, $email);

	try {
		$stmt->execute();
	} catch (mysqli_sql_exception $e) {
		$reason = "Σφάλμα εισαγωγής δεδομένων στην βάση δεδομένων.";

		if ($db->errno === 1062) { // unique constraint violation
			if (str_contains($db->error, 'username')) {
				$reason = "Το username δεν είναι διαθέσιμο.";
			} elseif (str_contains($db->error, 'email')) {
				$reason = "Το email δεν είναι διαθέσιμο.";
			} else {
				$reason = "Δεν θα έπρεπε να φτάσει εδώ. Τσέκαρε τα error logs!";
			}
		}

		$stmt->close();

		db_disconnect($db);

		$redirectParams['status'] = 'error';
		$redirectParams['reason'] = $reason;

		header('Location: register.php?' . http_build_query($redirectParams));
		exit;
	}

	$stmt->close();

	db_disconnect($db);

	header("Location: register.php?status=success");
	exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Εγγραφή</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php require 'navigation.php' ?>

	<main class="narrow">
		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
			<fieldset>
				<legend>Όνομα</legend>
				<input required type="text" name="name" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
			</fieldset>

			<fieldset>
				<legend>Επώνυμο</legend>
				<input required type="text" name="surname" value="<?= htmlspecialchars($_GET['surname'] ?? '') ?>">
			</fieldset>

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

			<fieldset>
				<legend>Email</legend>
				<input required type="email" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
			</fieldset>

			<div class="buttons">
				<button type="submit">Συνέχεια</button>
				<button type="reset" class="secondary">Καθαρισμός</button>
			</div>
		</form>

		<?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['status'])): ?>
			<dialog id="result">
				<?php if ($_GET['status'] === 'error'): ?>
					<h1>Αποτυχία!</h1>
					<p><?= htmlspecialchars($_GET['reason'] ?? 'Άγνωστο σφάλμα...') ?></p>
				<?php else: ?>
					<h1>Επιτυχία!</h1>
					<p>Η εγγραφή ολοκληρώθηκε επιτυχώς! Μπορείς πλέον να <a href="login.php">συνδεθείς</a> στον λογαριασμό σου.</p>
				<?php endif; ?>
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