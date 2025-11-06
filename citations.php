<?php
session_start();

// Vérifie la connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?mode=login");
    exit;
}

// Connexion à la BDD
$DB_HOST = 'mysql-ramdani.alwaysdata.net';
$DB_NAME = 'ramdani_projet_boutbien';
$DB_USER = 'ramdani';
$DB_PASS = 'Bilal16122006';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Helper pour éviter les warnings PHP 8+
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Message utilisateur
$message = '';

// Détection admin
$isAdmin = ($_SESSION['role'] ?? 'user') === 'admin';

// Ajout d'une citation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texte = trim($_POST['texte'] ?? '');
    $auteur = trim($_POST['auteur'] ?? '');

    if ($texte && $auteur) {
        $stmt = $pdo->prepare("INSERT INTO citations (user_id, texte, auteur) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $texte, $auteur]);
        $message = " Citation ajoutée !";
    } else {
        $message = " Tous les champs doivent être remplis.";
    }
}

// Récupération de toutes les citations
$stmt = $pdo->query("
    SELECT c.id, c.texte, c.auteur, c.date_ajout, u.username, c.user_id
    FROM citations c
    JOIN users u ON u.id = c.user_id
    ORDER BY c.date_ajout DESC
");
$citations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title> Citations</title>
<link rel="stylesheet" href="style.css">
<style>
</style>
</head>
<body>
<div class="container">
    <h2> Ajouter une citation</h2>
    <p>Bienvenue <strong><?= e($_SESSION['username']) ?></strong> </p>

    <?php if ($message): ?>
        <div class="message"><?= e($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="auteur" placeholder="Auteur de la citation" required>
        <textarea name="texte" placeholder="Écris ta citation ici..." required></textarea><br>
        <button type="submit">Ajouter la citation</button>
    </form>

    <hr>

    <h3> Toutes les citations</h3>
    <?php if (empty($citations)): ?>
        <p>Aucune citation pour le moment.</p>
    <?php else: ?>
        <?php foreach ($citations as $c): ?>
            <div class="citation">
                <p>"<?= nl2br(e($c['texte'])) ?>"</p>
                <div class="meta">
                    — <em><?= e($c['auteur']) ?></em><br>
                    Posté par <strong><?= e($c['username']) ?></strong>,
                    le <?= date('d/m/Y à H:i', strtotime($c['date_ajout'])) ?>
                </div>

                <?php if ($c['user_id'] == $_SESSION['user_id'] || $isAdmin): ?>
                    <div class="actions">
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="actions">
        <a href="index.php">⬅️ Retour à l’accueil</a> |
        <a href="index.php?logout=1">🚪 Se déconnecter</a>
    </div>
</div>
</body>
</html>
