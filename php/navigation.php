<nav>
	<a href="index.php">Αρχική</a>
	<a href="help.php">Βοήθεια</a>
	<hr class="separator horizontal">
	<a href="lists.php">Λίστες</a>
	<?php if (isset($_SESSION['user_id'])): ?>
		<a href="logout.php" style="margin-left: auto">Αποσύνδεση</a>
		<a href="account.php">Λογαριασμός</a>
	<?php else: ?>
		<a href="register.php" style="margin-left: auto">Εγγραφή</a>
		<a href="login.php">Σύνδεση</a>
	<?php endif; ?>
	<hr class="separator horizontal">
	<div id="toggle-color-scheme" onclick="toggleColorScheme()" title="Άλλαξε μεταξύ ανοιχτά και σκούρα χρώματα."></div>
	<script>
		function setColorScheme(scheme) {
			document.documentElement.style.setProperty("color-scheme", scheme);
			localStorage.setItem("color-scheme", scheme);
		}

		function toggleColorScheme() {
			setColorScheme(document.documentElement.style.getPropertyValue("color-scheme") === "dark" ? "light" : "dark");
		}

		setColorScheme(localStorage.getItem("color-scheme") || "light");
	</script>
</nav>

<hr class="separator">

<h1 id="page-title" style="text-align: center; width: 100%">...</h1>
<script>
	const currentPage = window.location.pathname.split("/").pop() || "index.php";

	for (const link of document.querySelectorAll("nav a")) {
		if (link.getAttribute("href").split("/").pop() === currentPage) {
			document.getElementById("page-title").textContent = link.textContent.trim();
			break;
		}
	}
</script>

<hr class="separator">
