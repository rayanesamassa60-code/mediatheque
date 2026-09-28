<?php
require 'config/db.php';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);

    if ($nom && $prenom && $email) {
        $insert = $pdo->prepare("INSERT INTO adherent (nom, prenom, email, date_inscription)
                                  VALUES (:nom, :prenom, :email, CURDATE())");
        $insert->execute(['nom' => $nom, 'prenom' => $prenom, 'email' => $email]);
        header('Location: adherents.php');
        exit;
    } else {
        $erreur = "Tous les champs sont obligatoires.";
    }
}

include 'includes/header.php';
?>
<h2>Ajouter un adhérent</h2>
<?php if ($erreur): ?><p class="retard"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>
<form method="post">
    <label>Nom :</label>
    <input type="text" name="nom" required>
    <label>Prénom :</label>
    <input type="text" name="prenom" required>
    <label>E-mail :</label>
    <input type="email" name="email" required>
    <button type="submit">Ajouter</button>
</form>
<?php include 'includes/footer.php'; ?>
