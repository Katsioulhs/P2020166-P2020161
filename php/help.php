<?php

session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Βοήθεια</title>
	<link rel="stylesheet" href="style.css">
</head>

<body>
	<?php require 'navigation.php' ?>

	<main class="narrow">
		<details>
			<summary>Εγγραφή και σύνδεση</summary>
			<p>Για να δημιουργήσετε λογαριασμό, μεταβείτε στη <a href="register.php">σελίδα εγγραφής</a> και συμπληρώστε τα απαιτούμενα πεδία. Μετά την εγγραφή, μπορείτε να <a href="login.php">συνδεθείτε</a> και να αποκτήσετε πρόσβαση στις λειτουργίες του ιστοτόπου.</p>
		</details>

		<details>
			<summary>Δημιουργία λίστας</summary>
			<p>Αφού συνδεθείτε, μπορείτε να δημιουργήσετε νέες λίστες περιεχομένου YouTube, να προσθέσετε βίντεο και να τις ορίσετε ως ιδιωτικές ή δημόσιες.</p>
		</details>

		<details>
			<summary>Αναπαραγωγή περιεχομένου</summary>
			<p>Τα βίντεο των λιστών μπορούν να αναπαραχθούν απευθείας από τις σελίδες λιστών, με ενσωματωμένο player YouTube.</p>
		</details>
	</main>
</body>

</html>