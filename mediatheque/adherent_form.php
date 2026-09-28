<?php
require 'config/db.php';

$erreur = '';

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nom !== '' && $prenom !== '' && $email !== '') {
        try {
            // Insertion dans la base de données
            $insert = $pdo->prepare("INSERT INTO adherent (nom, prenom, email, date_inscription)
                                     VALUES (:nom, :prenom, :email, CURDATE())");
            $insert->execute([
                'nom'    => $nom,
                'prenom' => $prenom,
                'email'  => $email
            ]);

            // Redirection vers la liste des adhérents
            header('Location: adherents.php');
            exit;

        } catch (PDOException $e) {
            // Gestion du doublon sur la clé e-mail (erreur 1062 / SQLSTATE 23000)
            if ($e->getCode() == 23000 || $e->errorInfo[1] == 1062) {
                $erreur = "Cet e-mail (" . htmlspecialchars($email) . ") est déjà utilisé par un autre adhérent.";
            } else {
                $erreur = "Erreur SQL : " . $e->getMessage();
            }
        }
    } else {
        $erreur = "Tous les champs sont obligatoires.";
    }
}

// Inclusions d'affichage
include 'includes/header.php';
?>

<h2>Ajouter un adhérent</h2>

<?php if ($erreur): ?>
    <p style="color: red; font-weight: bold; background-color: #fee; padding: 10px; border: 1px solid red; border-radius: 4px;">
        <?= htmlspecialchars($erreur) ?>
    </p>
<?php endif; ?>

<form method="post">
    <div>
        <label for="nom">Nom :</label><br>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
    </div>
    
    <br>

    <div>
        <label for="prenom">Prénom :</label><br>
        <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
    </div>

    <br>

    <div>
        <label for="email">E-mail :</label><br>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    </div>

    <br>

    <button type="submit">Enregistrer l'adhérent</button>
    <a href="adherents.php" style="margin-left: 10px;">Annuler</a>
</form>

<?php include 'includes/footer.php'; ?>