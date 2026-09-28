<?php
require 'config/db.php';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_adherent = (int)$_POST['id_adherent'];
    $id_livre = (int)$_POST['id_livre'];

    // on revérifie côté serveur que le livre est toujours dispo (sécurité)
    $check = $pdo->prepare("SELECT disponible FROM livre WHERE id_livre = ?");
    $check->execute([$id_livre]);
    $livre = $check->fetch();

    if ($livre && $livre['disponible']) {
        $pdo->beginTransaction(); // les 2 requêtes réussissent ensemble ou aucune

        $insert = $pdo->prepare("INSERT INTO emprunt (id_adherent, id_livre, date_emprunt, date_retour_prevue)
                                  VALUES (:id_adherent, :id_livre, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY))");
        $insert->execute(['id_adherent' => $id_adherent, 'id_livre' => $id_livre]);

        $update = $pdo->prepare("UPDATE livre SET disponible = 0 WHERE id_livre = ?");
        $update->execute([$id_livre]);

        $pdo->commit();
        header('Location: emprunts.php'); // redirection après succès
        exit;
    } else {
        $erreur = "Ce livre n'est plus disponible.";
    }
}

include 'includes/header.php';
$adherents = $pdo->query("SELECT id_adherent, nom, prenom FROM adherent ORDER BY nom")->fetchAll();
$livres = $pdo->query("SELECT id_livre, titre FROM livre WHERE disponible = 1 ORDER BY titre")->fetchAll();
?>
<h2>Nouvel emprunt</h2>
<?php if ($erreur): ?><p class="retard"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>
<form method="post">
    <label>Adhérent :</label>
    <select name="id_adherent" required>
        <?php foreach ($adherents as $a): ?>
            <option value="<?= $a['id_adherent'] ?>"><?= htmlspecialchars($a['nom'].' '.$a['prenom']) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Livre disponible :</label>
    <select name="id_livre" required>
        <?php foreach ($livres as $l): ?>
            <option value="<?= $l['id_livre'] ?>"><?= htmlspecialchars($l['titre']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Enregistrer l'emprunt</button>
</form>
<?php include 'includes/footer.php'; ?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Médiathèque</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>