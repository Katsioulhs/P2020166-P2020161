<?php

require_once 'helper/functions.php';

session_start();
validate_that_user_is_logged_in();

$userId = $_SESSION['user_id'];

require_once 'helper/database.php';
$db = db_connect();

if ($db === null) {
	header('Location: account.php?status=error&reason=' . urlencode("Αποτυχία σύνδεσης με βάση δεδομένων."));
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
	$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
	$stmt->bind_param("i", $userId);

	try {
		$stmt->execute();
	} catch (mysqli_sql_exception $e) {
		db_disconnect($db);
		header('Location: account.php?status=error&reason=' . urlencode("Σφάλμα κατά την διαγραφή."));
		exit;
	}

	$stmt->close();
	db_disconnect($db);

	logout('index.php?status=account_deleted');
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$name = trim($_POST['name'] ?? '');
	$surname = trim($_POST['surname'] ?? '');
	$username = trim($_POST['username'] ?? '');
	$password = trim($_POST['password'] ?? '');
	$email = trim($_POST['email'] ?? '');

	unset($_POST['password']);
	$redirectParams = $_POST;

	$errors = [];

	if ($name === '')
		$errors[] = "Το όνομα είναι υποχρεωτικό.";
	if ($surname === '')
		$errors[] = "Το επώνυμο είναι υποχρεωτικό.";
	if ($username === '')
		$errors[] = "Το username είναι υποχρεωτικό.";
	if ($email === '') {
		$errors[] = "Το email είναι υποχρεωτικό.";
	} elseif (!str_contains($email, '@')) {
		$errors[] = "Το email δεν είναι έγκυρο.";
	}

	if (!empty($errors)) {
		$redirectParams['status'] = 'error';
		$redirectParams['reason'] = $errors[0];
		header('Location: account.php?' . http_build_query($redirectParams));
		exit;
	}

	$query = "UPDATE users SET name=?, surname=?, username=?, email=?";
	$params = [$name, $surname, $username, $email];
	$types = "ssss";

	if ($password !== '') {
		$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
		$query .= ", password=?";
		$params[] = $hashedPassword;
		$types .= "s";
	}

	$query .= " WHERE id=?";
	$params[] = $userId;
	$types .= "i";

	$stmt = $db->prepare($query);

	if ($stmt === false) {
		db_disconnect($db);
		header("Location: account.php?status=error&reason=" . urlencode("Σφάλμα βάσης δεδομένων."));
		exit;
	}

	$stmt->bind_param($types, ...$params);

	try {
		$stmt->execute();
	} catch (mysqli_sql_exception $e) {
		$reason = "Σφάλμα κατά την ενημέρωση.";
		if ($db->errno === 1062) {
			if (str_contains($db->error, 'username')) {
				$reason = "Το username δεν είναι διαθέσιμο.";
			} elseif (str_contains($db->error, 'email')) {
				$reason = "Το email δεν είναι διαθέσιμο.";
			}
		}
		$stmt->close();
		db_disconnect($db);
		header('Location: account.php?status=error&reason=' . urlencode($reason));
		exit;
	}

	$stmt->close();
	db_disconnect($db);

	header("Location: account.php?status=success");
	exit;
}

$stmt = $db->prepare("SELECT name, surname, username, email FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($name, $surname, $username, $email);
$stmt->fetch();
$stmt->close();

db_disconnect($db);

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Λογαριασμός</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php require 'navigation.php' ?>

	<main class="narrow">
		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
			<fieldset>
				<legend>Όνομα</legend>
				<input required type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>">
			</fieldset>

			<fieldset>
				<legend>Επώνυμο</legend>
				<input required type="text" name="surname" value="<?= htmlspecialchars($surname ?? '') ?>">
			</fieldset>

			<fieldset>
				<legend>Username</legend>
				<input required type="text" name="username" value="<?= htmlspecialchars($username ?? '') ?>">
			</fieldset>

			<fieldset>
				<legend>Password</legend>
				<input type="password" id="password" name="password" placeholder="συμπλήρωσε μόνο αν θες να τον αλλάξεις...">
				<label><input type="checkbox" id="show-passwords" style="margin-right: .5rem;">εμφάνισε</label>
				<script>
					document.getElementById('show-passwords').addEventListener('change', function () {
						document.getElementById('password').type = this.checked ? 'text' : 'password';
					});
				</script>
			</fieldset>

			<fieldset>
				<legend>Email</legend>
				<input required type="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
			</fieldset>

			<div class="buttons">
				<button type="submit">Ανανέωση</button>
				<button type="reset" class="secondary" title="Επαναφορά στοιχείων σε αυτά που είναι αποθηκευμένα στην βάση δεδομένων.">Επαναφορά</button>
			</div>
		</form>

		<?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['status'])): ?>
			<dialog id="result">
				<?php if ($_GET['status'] === 'error'): ?>
					<h1>Αποτυχία!</h1>
					<p><?= htmlspecialchars($_GET['reason'] ?? 'Άγνωστο σφάλμα...') ?></p>
				<?php else: ?>
					<h1>Επιτυχία!</h1>
					<p>Τα στοιχεία σου ενημερώθηκαν.</p>
				<?php endif; ?>
				<form method="dialog">
					<button>Κλείσιμο</button>
				</form>
			</dialog>
			<script>
				document.getElementById('result')?.showModal();
			</script>
		<?php endif; ?>

		<form method="post" onsubmit="return confirm('Σίγουρα θες να διαγράψεις τον λογαριασμό σου; Αυτή η ενέργεια είναι μη αναστρέψιμη!');">
			<input type="hidden" name="delete_account" value="1">
			<div class="buttons"><button type="submit" class="secondary">Διαγραφή</button></div>
		</form>
	</main>
</body>

</html>