<?php

session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Αρχική</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php require 'navigation.php' ?>

	<main class="narrow">
		<details>
			<summary>Σκοπός του Ιστοτόπου</summary>
			<p>Ο ιστοτόπος επιτρέπει σε χρήστες να δημιουργούν προφίλ και λίστες περιεχομένου ροής, όπως βίντεο YouTube, με δυνατότητα αναπαραγωγής απευθείας από τη σελίδα.</p>
		</details>

		<details>
			<summary>Πώς γίνεται η εγγραφή;</summary>
			<p>Οι χρήστες μπορούν να εγγραφούν μέσω της <a href="register.php">φόρμας εγγραφής</a>, παρέχοντας βασικά στοιχεία όπως όνομα, username και email. Η εγγραφή επιτρέπει τη δημιουργία και διαχείριση λιστών και προφίλ.</p>
		</details>

		<details>
			<summary>Γιατί να εγγραφώ;</summary>
			<p>Με την εγγραφή αποκτάτε δυνατότητες όπως αποθήκευση προσωπικών λιστών, παρακολούθηση άλλων χρηστών, και αναπαραγωγή περιεχομένου.</p>
		</details>

		<details>
			<summary>Σκοπός του ιστοτόπου</summary>
			<p>Επιτρέπει σε χρήστες να δημιουργούν το προσωπικό τους προφίλ και να οργανώνουν λίστες περιεχομένου ροής, συγκεκριμένα βίντεο YouTube. Οι λίστες μπορεί να είναι ιδιωτικές ή δημόσιες και να αναπαράγονται άμεσα από τη ιστοσελίδα.</p>
		</details>
	</main>
</body>

</html>