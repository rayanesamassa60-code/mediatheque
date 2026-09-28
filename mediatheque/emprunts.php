<?php
require 'config/db.php';
include 'includes/header.php';

$adherents = $pdo->query("SELECT nom, prenom, email, date_inscription FROM adherent ORDER BY nom")->fetchAll();
?>
<h2>Liste des adhérents</h2>
<table>
    <tr><th>Nom</th><th>Prénom</th><th>E-mail</th><th>Date d'inscription</th></tr>
    <?php foreach ($adherents as $a): ?>
    <tr>
        <td><?= htmlspecialchars($a['nom']) ?></td>
        <td><?= htmlspecialchars($a['prenom']) ?></td>
        <td><?= htmlspecialchars($a['email']) ?></td>
        <td><?= htmlspecialchars($a['date_inscription']) ?></td>
        <td>
  
    </tr>
    <?php endforeach; ?>
</table>
<?php include 'includes/footer.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Médiathèque</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>