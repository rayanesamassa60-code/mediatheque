<?php
require 'config/db.php';

$erreur = '';
$id_livre = (int)($_GET['id'] ?? 0);
$livre = ['titre' => '', 'isbn' => '', 'annee_publication' => '', 'id_categorie' => ''];

// Si on modifie un livre existant, on charge ses données
if ($id_livre > 0) {
    $stmt = $pdo->prepare("SELECT * FROM livre WHERE id_livre = ?");
    $stmt->execute([$id_livre]);
    $donnees = $stmt->fetch();
    if ($donnees) {
        $livre = $donnees;
    }
}

// Traitement de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $annee = !empty($_POST['annee_publication']) ? (int)$_POST['annee_publication'] : null;
    $id_categorie = (int)($_POST['id_categorie'] ?? 0);

    if ($titre !== '' && $isbn !== '' && $id_categorie > 0) {
        try {
            if ($id_livre > 0) {
                // Modification
                $upd = $pdo->prepare("UPDATE livre SET titre=?, isbn=?, annee_publication=?, id_categorie=? WHERE id_livre=?");
                $upd->execute([$titre, $isbn, $annee, $id_categorie, $id_livre]);
            } else {
                // Création
                $ins = $pdo->prepare("INSERT INTO livre (titre, isbn, annee_publication, disponible, id_categorie) VALUES (?, ?, ?, 1, ?)");
                $ins->execute([$titre, $isbn, $annee, $id_categorie]);
            }

            // Redirection vers la liste des livres (au singulier)
            header('Location: livre.php');
            exit;

        } catch (PDOException $e) {
            // Détection du doublon sur la clé unique ISBN (erreur 1062 ou SQLSTATE 23000)
            if ($e->getCode() == 23000 || $e->errorInfo[1] == 1062) {
                $erreur = "Un livre avec l'ISBN " . htmlspecialchars($isbn) . " existe déjà dans la base.";
            } else {
                $erreur = "Erreur BDD : " . $e->getMessage();
            }
        }
    } else {
        $erreur = "Le titre, l'ISBN et la catégorie sont obligatoires.";
    }
}

// Inclusions d'affichage (toujours APRES le traitement POST)
include 'includes/header.php';
$categories = $pdo->query("SELECT id_categorie, libelle FROM categorie ORDER BY libelle")->fetchAll();
?>

<h2><?= $id_livre > 0 ? 'Modifier le livre' : 'Ajouter un livre' ?></h2>

<?php if ($erreur): ?>
    <p style="color: red; font-weight: bold;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form method="post">
    <label for="titre">Titre :</label>
    <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($livre['titre']) ?>" required>

    <label for="isbn">ISBN :</label>
    <input type="text" id="isbn" name="isbn" value="<?= htmlspecialchars($livre['isbn']) ?>" required>

    <label for="annee_publication">Année de publication :</label>
    <input type="number" id="annee_publication" name="annee_publication" value="<?= htmlspecialchars($livre['annee_publication']) ?>">

    <label for="id_categorie">Catégorie :</label>
    <select id="id_categorie" name="id_categorie" required>
        <option value="">-- Choisir une catégorie --</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id_categorie'] ?>" <?= $c['id_categorie'] == $livre['id_categorie'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['libelle']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit"><?= $id_livre > 0 ? 'Enregistrer' : 'Ajouter' ?></button>
</form>

<?php include 'includes/footer.php'; ?>