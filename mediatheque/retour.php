<?php
require_once 'config/db.php';

$message = '';
$erreur = '';

// --- TRAITEMENT DU RETOUR D'UN LIVRE ---
$id_emprunt = (int)($_GET['id'] ?? 0);

if ($id_emprunt > 0) {
    try {
        // 1. Vérifier si l'emprunt existe et n'est pas déjà rendu
        $stmt = $pdo->prepare("SELECT id_livre FROM emprunt WHERE id_emprunt = ? AND date_retour IS NULL");
        $stmt->execute([$id_emprunt]);
        $emprunt = $stmt->fetch();

        if ($emprunt) {
            $pdo->beginTransaction();

            // Mettre à jour la date de retour dans la table emprunt
            $updateEmprunt = $pdo->prepare("UPDATE emprunt SET date_retour = CURDATE() WHERE id_emprunt = ?");
            $updateEmprunt->execute([$id_emprunt]);

            // Remettre le livre en statut disponible
            $updateLivre = $pdo->prepare("UPDATE livre SET disponible = 1 WHERE id_livre = ?");
            $updateLivre->execute([$emprunt['id_livre']]);

            $pdo->commit();
            $message = "Le retour du livre a bien été enregistré !";
        } else {
            $erreur = "Cet emprunt n'existe pas ou le livre a déjà été rendu.";
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erreur = "Erreur lors de la validation du retour : " . $e->getMessage();
    }
}

// --- RÉCUPÉRATION DES EMPRUNTS EN COURS ---
try {
    $sql = "SELECT e.id_emprunt, e.date_emprunt, l.titre, a.nom, a.prenom 
            FROM emprunt e
            JOIN livre l ON e.id_livre = l.id_livre
            JOIN adherent a ON e.id_adherent = a.id_adherent
            WHERE e.date_retour IS NULL
            ORDER BY e.date_emprunt ASC";
    $emprunts = $pdo->query($sql)->fetchAll();
} catch (Exception $e) {
    $emprunts = [];
    $erreur = "Impossible de charger la liste des emprunts : " . $e->getMessage();
}

include 'includes/header.php';
?>

<h2>Gestion des Retours de Livres</h2>

<?php if ($message): ?>
    <div style="background-color: var(--success-bg); color: var(--success); border: 1px solid var(--success); padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if ($erreur): ?>
    <div style="background-color: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger); padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
        <?= htmlspecialchars($erreur) ?>
    </div>
<?php endif; ?>

<p style="color: var(--text-muted); margin-bottom: 1.5rem;">
    Sélectionnez un emprunt ci-dessous pour valider la restitution du livre dans la base de données.
</p>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Livre</th>
                <th>Adhérent</th>
                <th>Date d'emprunt</th>
                <th>Statut</th>
                <th style="text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($emprunts) > 0): ?>
                <?php foreach ($emprunts as $item): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($item['titre']) ?></strong></td>
                        <td><?= htmlspecialchars($item['prenom'] . ' ' . $item['nom']) ?></td>
                        <td><?= date('d/m/Y', strtotime($item['date_emprunt'])) ?></td>
                        <td><span class="emprunte">En cours</span></td>
                        <td style="text-align: center;">
                            <a href="retour.php?id=<?= $item['id_emprunt'] ?>" 
                               class="btn" 
                               style="padding: 0.4rem 0.8rem; font-size: 0.8rem; display: inline-block; width: auto;"
                               onclick="return confirm('Confirmer le retour de ce livre ?');">
                               Valider le retour
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        Aucun emprunt en cours à retourner actuellement.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>