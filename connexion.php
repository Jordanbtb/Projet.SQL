<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<style>
body{font-family:Arial;text-align:center;padding-top:50px;background:#f5f5f5}
a{color:red;text-decoration:none;font-weight:bold}
</style>
</head>
<body>
<h1>Bienvenue <?= htmlspecialchars($_SESSION['username']) ?> 👋</h1>
<p>Tu es connecté à ton site !</p>
<p><a href="index.php?logout=1">Se déconnecter</a></p>
</body>
</html>
