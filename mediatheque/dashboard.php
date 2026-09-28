<?php
require 'config/db.php';
include 'includes/header.php';

$nb_livres = $pdo->query("SELECT COUNT(*) FROM livre")->fetchColumn();
$nb_adherents = $pdo->query("SELECT COUNT(*) FROM adherent")->fetchColumn();
$nb_emprunts_en_cours = $pdo->query("SELECT COUNT(*) FROM emprunt WHERE date_retour IS NULL")->fetchColumn();
$nb_retards = $pdo->query("SELECT COUNT(*) FROM emprunt WHERE date_retour IS NULL AND date_retour_prevue < CURDATE()")->fetchColumn();
?>
<h2>Tableau de bord</h2>
<table>
    <tr><th>Indicateur</th><th>Valeur</th></tr>
    <tr><td>Livres</td><td><?= $nb_livres ?></td></tr>
    <tr><td>Adhérents</td><td><?= $nb_adherents ?></td></tr>
    <tr><td>Emprunts en cours</td><td><?= $nb_emprunts_en_cours ?></td></tr>
    <tr><td>Retards</td><td><span class="retard"><?= $nb_retards ?></span></td></tr>
</table>
<?php include 'includes/footer.php'; ?>
