<?php
session_start();
include_once '../php/connect.php';

SessionManager::checkSession();

class SessionManager {
    public static function checkSession() {
        if (!isset($_SESSION['gebruikersnaam'])) {
            header('Location: login.php');
            exit;
        }
    }

    public static function getUserRole() {
        return $_SESSION['rol'] ?? null;
    }

    public static function getUserId() {
        return $_SESSION['gebruiker_id'] ?? null;
    }
}

class LesManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllLessons() {
        $sql = "
            SELECT 
                l.Les_id, 
                l.Lestijd, 
                l.Doel, 
                l.Geannuleerd, 
                l.RedenAnnuleren, 
                lp.Naam AS LespakketNaam, 
                a.Merk AS AutoMerk, 
                a.Model AS AutoModel, 
                o.Adres AS OphaallocatieAdres,
                o.Plaats AS OphaallocatiePlaats
            FROM 
                Les l
            LEFT JOIN 
                Lespakket lp ON l.LespakketLespakket_id = lp.Lespakket_id
            LEFT JOIN 
                Auto a ON l.AutoAuto_id = a.Auto_id
            LEFT JOIN 
                Ophaallocatie o ON l.OphaallocatieOphaallocatie_id = o.Ophaallocatie_id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$lesManager = new LesManager($conn);
$lessen = $lesManager->getAllLessons(); 
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overzicht van Alle Lessen</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            cursor: pointer;
        }
        .cancel-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <h2>Overzicht van Alle Lessen</h2>
    <?php if (!empty($lessen)): ?>
        <table>
            <tr>
                <th>Lestijd</th>
                <th>Doel</th>
                <th>Status</th>
                <th>Reden Annuleren</th>
                <th>Lespakket Naam</th>
                <th>Auto</th>
                <th>Ophaallocatie</th>
                <th>Acties</th>
            </tr>
            <?php foreach ($lessen as $les): ?>
                <tr>
                    <td><?= htmlspecialchars($les['Lestijd']); ?></td>
                    <td><?= htmlspecialchars($les['Doel']); ?></td>
                    <td><?= $les['Geannuleerd'] == 1 ? "Geannuleerd" : "Actief"; ?></td>
                    <td><?= !empty($les['RedenAnnuleren']) ? htmlspecialchars($les['RedenAnnuleren']) : "Nvt"; ?></td>
                    <td><?= htmlspecialchars($les['LespakketNaam']); ?></td>
                    <td><?= htmlspecialchars($les['AutoMerk'] . ' ' . $les['AutoModel']); ?></td>
                    <td><?= htmlspecialchars($les['OphaallocatieAdres'] . ', ' . $les['OphaallocatiePlaats']); ?></td>
                    <td>
                        <form action="Lesbewerken.php" method="get" style="display: inline;">
                            <input type="hidden" name="les_id" value="<?= htmlspecialchars($les['Les_id']); ?>">
                            <button type="submit">Bewerken</button>
                        </form>
                        <?php if ($les['Geannuleerd'] == 0): ?>
                        <form action="Lesannuleren.php" method="get" style="display: inline;">
                            <input type="hidden" name="les_id" value="<?= htmlspecialchars($les['Les_id']); ?>">
                            <button type="submit" class="cancel-btn">Annuleren</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Er zijn geen lessen beschikbaar op dit moment.</p>
    <?php endif; ?>
    <p><a href="main.php">Terug naar het hoofdmenu</a></p>
</body>
</html>
