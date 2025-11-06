<?php
session_start();

// 🔐 Vérification ADMIN
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header("Location: index.php?mode=login");
    exit;
}

// Connexion BDD
$DB_HOST = 'mysql-ramdani.alwaysdata.net';
$DB_NAME = 'ramdani_projet_boutbien';
$DB_USER = 'ramdani';
$DB_PASS = 'Bilal16122006';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("🚫 Erreur de connexion : " . $e->getMessage());
}

// Helper pour éviter les warnings et sécuriser
function e($str) { return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'); }

$message = '';

// ✏️ Modification
if (isset($_POST['update_id'], $_POST['texte_update'], $_POST['auteur_update'])) {
    $id = intval($_POST['update_id']);
    $texte = trim($_POST['texte_update']);
    $auteur = trim($_POST['auteur_update']);
    if ($texte && $auteur) {
        $stmt = $pdo->prepare("UPDATE citations SET texte=?, auteur=? WHERE id=?");
        $stmt->execute([$texte, $auteur, $id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else $message = "⚠️ La citation et l'auteur doivent être remplis.";
}

// 🗑️ Suppression
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM citations WHERE id=?");
    $stmt->execute([$id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 🔍 Récupération
$stmt = $pdo->query("
    SELECT c.id, c.texte, c.auteur, c.date_ajout, u.username
    FROM citations c
    JOIN users u ON c.user_id = u.id
    ORDER BY c.date_ajout DESC
");
$citations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$edit_id = intval($_GET['edit'] ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>👑 Administration - Citations</title>
<link rel="stylesheet" href="style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <header class="admin-header">
        <h2>👑 Gestion des Citations</h2>
        <p>Bienvenue <strong><?= e($_SESSION['username']) ?></strong></p>
    </header>

    <?php if ($message): ?>
        <div class="message"><?= e($message) ?></div>
    <?php endif; ?>

    <section class="citations-section">
        <h3>🗣️ Liste des citations</h3>

        <?php if (empty($citations)): ?>
            <p>Aucune citation enregistrée.</p>
        <?php else: ?>
            <?php foreach ($citations as $c): ?>
                <?php $current_id = (int)$c['id']; ?>
                <div class="citation">

                    <?php if ($edit_id === $current_id): ?>
                        <form method="POST" class="edit-form">
                            <input type="hidden" name="update_id" value="<?= $current_id ?>">
                            <input type="text" name="auteur_update" value="<?= e($c['auteur']) ?>" required>
                            <textarea name="texte_update" required><?= e($c['texte']) ?></textarea>
                            <div class="edit-actions">
                                <button type="submit" class="btn-save">Sauvegarder</button>
                                <a href="?" class="btn-cancel">Annuler</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>"<?= nl2br(e($c['texte'])) ?>"</p>
                        <div class="meta">
                            — <em><?= e($c['auteur']) ?></em><br>
                            Posté par <strong><?= e($c['username']) ?></strong> le <?= date('d/m/Y à H:i', strtotime($c['date_ajout'])) ?>
                        </div>
                        <div class="actions-citation">
                            <a href="?edit=<?= $current_id ?>" class="btn-edit">✏️ Modifier</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $current_id ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('Supprimer cette citation ?')">🗑️</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <footer class="actions">
        <a href="index.php">⬅️ Retour à l’accueil</a>
        <a href="index.php?logout=1">🚪 Déconnexion</a>
    </footer>
</div>

</body>
</html>
