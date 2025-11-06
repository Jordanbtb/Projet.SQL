<?php
session_start();

$DB_HOST = 'mysql-ramdani.alwaysdata.net';
$DB_NAME = 'ramdani_projet_boutbien';
$DB_USER = 'ramdani';
$DB_PASS = 'Bilal16122006';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {

    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$message = '';

$mode = $_GET['mode'] ?? 'login';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if ($mode === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($username && $password && $password2 && $password === $password2) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username=?");
        $stmt->execute([$username]);

        if (!$stmt->fetch()) {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users(username, password_hash, role) VALUES (?, ?, 'user')");
            $stmt->execute([$username, $hash]);
            $message = "✅ Compte créé ! Connecte-toi ci-dessous.";
            $mode = 'login'; 
        } else {
            $message = "🚫 Nom d'utilisateur déjà pris.";
        }
    } else {
        $message = "⚠️ Tous les champs sont obligatoires et les mots de passe doivent correspondre.";
    }
}


if ($mode === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
    
        $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'] ?? 'user'; 

     
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: citations.php");
            }
            exit;
        } else {
            $message = "❌ Identifiant ou mot de passe incorrect.";
        }
    } else {
        $message = "⚠️ Tous les champs sont obligatoires.";
    }
}



$imgDir = __DIR__ . '/images';
$allowed = ['jpg','jpeg','png','webp','gif'];
$images = [];
if (is_dir($imgDir)) {
    $d = scandir($imgDir);
    foreach ($d as $f) {
        if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $allowed)) {
            $images[] = 'images/' . $f;
        }
    }
}


$defaultCitations = [
    [
        "text" => "Le bien et le mal ne sont que des reflets du pouvoir, car la justice appartient toujours à celui qui tient le monde entre ses mains.",
        "author" => "Jordan Boutbien"
    ],
    [
        "text" => "N'abandonne jamais, car c'est souvent la dernière clé du trousseau qui ouvre la porte.",
        "author" => "Paulo Coelho"
    ],
    [
        "text" => "Ils ne savaient pas que c'était impossible, alors ils l'ont fait.",
        "author" => "Mark Twain"
    ],
    [
        "text" => "Ce n'est pas la force, mais la persévérance, qui fait les grandes choses.",
        "author" => "Samuel Johnson"
    ],
    [
        "text" => "Le succès n'est pas la clé du bonheur. Le bonheur est la clé du succès.",
        "author" => "Albert Schweitzer"
    ]
];


$dbCitations = [];
try {
    $stmt = $pdo->query("SELECT texte as text, auteur as author FROM citations ORDER BY date_ajout DESC"); 
    $dbCitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {

    error_log("Erreur de récupération des citations DB : " . $e->getMessage());
}


$citations = array_merge($dbCitations, $defaultCitations);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Accueil - Connexion / Inscription / Contenu</title>
<link rel="stylesheet" href="style.css"> </head>
<body>

<div class="form-container">
    <?php if(isset($_SESSION['user_id'])): ?>
        <div class="user-info">
            <h3>👋 Bienvenue<br><?= htmlspecialchars($_SESSION['username'] ?? '') ?></h3>
            <p>Tu es connecté à ton site !</p>

            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="manage-citations-btn">👑 Tableau de bord admin</a><br>
            <?php endif; ?>
            <a href="citations.php" class="manage-citations-btn">💬 Gérer mes citations</a><br>
            <a href="index.php?logout=1" class="logout-btn">🚪 Se déconnecter</a>
        </div>
    <?php else: ?>
        <h2><?= $mode === 'login' ? 'Connexion' : 'Inscription' ?></h2>
        <?php if($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Identifiant" required>
            <input type="password" name="password" placeholder="Mot de passe" required>

            <?php if($mode === 'register'): ?>
                <input type="password" name="password2" placeholder="Confirme le mot de passe" required>
                <button type="submit">S’inscrire</button>
                <br><a href="index.php?mode=login">Déjà un compte ? Se connecter</a>
            <?php else: ?>
                <button type="submit">Se connecter</button>
                <br><a href="index.php?mode=register">Pas encore de compte ? S’inscrire</a>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<?php if (count($images) > 0): ?>
    <div id="carousel" class="carousel">
        <div class="slides">
            <?php foreach ($images as $idx => $src): ?>
                <div class="slide" data-index="<?= $idx ?>">
                    <img src="<?= htmlspecialchars($src) ?>" alt="Image <?= $idx+1 ?>" loading="lazy"/>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div id="citations" class="citations-carousel">
    <?php foreach ($citations as $i => $quote): ?>
        <div class="citation <?= $i === 0 ? 'active' : '' ?>">
            <p class="citation-text"><?= htmlspecialchars($quote['text'] ?? '') ?></p>
            <p class="citation-author">— <?= htmlspecialchars($quote['author'] ?? '') ?></p>
        </div>
    <?php endforeach; ?>
</div>

<script>

(function(){
    const carousel = document.getElementById('carousel');
    if (!carousel) return;
    const slidesWrapper = carousel.querySelector('.slides');
    const slides = Array.from(carousel.querySelectorAll('.slide'));
    let current = 0;
    const interval = 7000; 

    function setPosition() {
     
        const x = -current * 100; 
        slidesWrapper.style.transform = 'translateX(' + x + 'vw)';
    }

    function next(){
        current = (current + 1) % slides.length;
        setPosition();
    }

    setPosition();

    if (slides.length > 1) {
        setInterval(next, interval);
    }
})();


(function(){
    const citations = document.querySelectorAll('.citations-carousel .citation');
    if (citations.length <= 1) return; 
    let current = 0;
    const interval = 10000; 

    function showNext(){
        citations[current].classList.remove('active');
        current = (current + 1) % citations.length;
        citations[current].classList.add('active');
    }

    setInterval(showNext, interval);
})();
</script>

</body>
</html>