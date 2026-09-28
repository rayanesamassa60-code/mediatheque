<?php
require 'config/db.php';
include 'includes/header.php';

$recherche = $_GET['q'] ?? '';

$sql = "SELECT * FROM adherent";
if ($recherche !== '') {
    $sql .= " WHERE nom LIKE :recherche OR prenom LIKE :recherche OR email LIKE :recherche";
}
$sql .= " ORDER BY nom, prenom";

$stmt = $pdo->prepare($sql);
if ($recherche !== '') {
    $stmt->bindValue(':recherche', '%' . $recherche . '%');
}
$stmt->execute();
$adherents = $stmt->fetchAll();
?>

<h2>Liste des adhérents</h2>

<!-- Zone d'action : Recherche + Bouton Ajouter -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <form method="get" style="margin: 0;">
        <label for="q">Rechercher :</label>
        <input type="text" id="q" name="q" value="<?= htmlspecialchars($recherche) ?>" placeholder="Nom, prénom, email...">
        <button type="submit">Filtrer</button>
    </form>

    <!-- BOUTON D'AJOUT D'ADHÉRENT -->
    <a href="adherent_form.php" style="background-color: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-weight: bold;">
        + Ajouter un adhérent
    </a>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>E-mail</th>
            <th>Date d'inscription</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($adherents)): ?>
            <?php foreach ($adherents as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['id_adherent'] ?? $a['id'] ?? '') ?></td>
                <td><?= htmlspecialchars($a['nom']) ?></td>
                <td><?= htmlspecialchars($a['prenom']) ?></td>
                <td><?= htmlspecialchars($a['email']) ?></td>
                <td><?= htmlspecialchars($a['date_inscription']) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">Aucun adhérent trouvé.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>