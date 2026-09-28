<?php
require 'config/db.php';
include 'includes/header.php';

$recherche = $_GET['q'] ?? '';
$id_categorie = $_GET['categorie'] ?? '';

$page = max(1, (int)($_GET['page'] ?? 1));
$parPage = 5;
$offset = ($page - 1) * $parPage;

$where = [];
$params = [];
if ($recherche !== '') {
    $where[] = "l.titre LIKE :recherche";
    $params['recherche'] = '%' . $recherche . '%';
}
if ($id_categorie !== '') {
    $where[] = "l.id_categorie = :id_categorie";
    $params['id_categorie'] = $id_categorie;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Compter le total pour la pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM livre l $whereSql");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$nbPages = max(1, ceil($total / $parPage));

$sql = "SELECT l.id_livre, l.titre, l.annee_publication, l.isbn, l.disponible, l.id_categorie,
               c.libelle AS categorie,
               GROUP_CONCAT(CONCAT(a.prenom,' ',a.nom) SEPARATOR ', ') AS auteurs
        FROM livre l
        LEFT JOIN categorie c ON c.id_categorie = l.id_categorie
        LEFT JOIN livre_auteur la ON la.id_livre = l.id_livre
        LEFT JOIN auteur a ON a.id_auteur = la.id_auteur
        $whereSql
        GROUP BY l.id_livre ORDER BY l.titre
        LIMIT $parPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livres = $stmt->fetchAll();

$categories = $pdo->query("SELECT id_categorie, libelle FROM categorie ORDER BY libelle")->fetchAll();
?>
<h2>Liste des livres</h2>
<a href="livre_form.php">+ Ajouter un livre</a>

<form method="get">
    <label>Titre :</label>
    <input type="text" name="q" value="<?= htmlspecialchars($recherche) ?>">
    <label>Catégorie :</label>
    <select name="categorie">
        <option value="">Toutes</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id_categorie'] ?>" <?= $c['id_categorie'] == $id_categorie ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['libelle']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Filtrer</button>
</form>

<table>
    <tr><th>Titre</th><th>Auteur(s)</th><th>Catégorie</th><th>Année</th><th>ISBN</th><th>Disponibilité</th><th>Action</th></tr>
    <?php foreach ($livres as $livre): ?>
    <tr>
        <td><?= htmlspecialchars($livre['titre']) ?></td>
        <td><?= htmlspecialchars($livre['auteurs'] ?? '') ?></td>
        <td><?= htmlspecialchars($livre['categorie'] ?? '') ?></td>
        <td><?= htmlspecialchars($livre['annee_publication']) ?></td>
        <td><?= htmlspecialchars($livre['isbn']) ?></td>
        <td>
            <?php if ($livre['disponible']): ?><span class="disponible">Disponible</span>
            <?php else: ?><span class="emprunte">Emprunté</span><?php endif; ?>
        </td>
        <td><a href="livre_form.php?id=<?= $livre['id_livre'] ?>">Modifier</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<div>
    <?php for ($i = 1; $i <= $nbPages; $i++): ?>
        <a href="?q=<?= urlencode($recherche) ?>&categorie=<?= urlencode($id_categorie) ?>&page=<?= $i ?>"
           style="<?= $i == $page ? 'font-weight:bold;' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>

<?php include 'includes/footer.php'; ?>
