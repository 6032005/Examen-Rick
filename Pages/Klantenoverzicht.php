<?php
session_start();
include_once '../php/connect.php';

if (!isset($_SESSION['gebruikersnaam'])) {
    header('Location: login.php');
    exit;
}

class UserManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }


    public function getAllUsers() {
        $sql = "SELECT Gebruiker_id, Gebruikersnaam, Email, Wachtwoord, Voornaam, Tussenvoegsel, Achternaam, Rol, Exameninformatie, Actief, Geslaagd 
                FROM Gebruiker";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving users: " . $e->getMessage());
        }
    }
}

$userManager = new UserManager($conn);
$users = $userManager->getAllUsers(); 
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruikers Overzicht</title>
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
    </style>
</head>
<body>
    <h2>Overzicht van Gebruikers</h2>

    <?php if (!empty($users)): ?>
        <table>
            <tr><th>Gebruiker_id</th><th>Gebruikersnaam</th><th>Email</th><th>Voornaam</th><th>Tussenvoegsel</th><th>Achternaam</th><th>Rol</th><th>Exameninformatie</th><th>Actief</th><th>Geslaagd</th><th>Acties</th></tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['Gebruiker_id']); ?></td>
                    <td><?= htmlspecialchars($user['Gebruikersnaam']); ?></td>
                    <td><?= htmlspecialchars($user['Email']); ?></td>
                    <td><?= htmlspecialchars($user['Voornaam']); ?></td>
                    <td><?= htmlspecialchars($user['Tussenvoegsel']); ?></td>
                    <td><?= htmlspecialchars($user['Achternaam']); ?></td>
                    <td>
                        <?php
    
                        switch ($user['Rol']) {
                            case 0:
                                echo 'Leerling';
                                break;
                            case 1:
                                echo 'Instructeur';
                                break;
                            case 2:
                                echo 'Rijschooleigenaar';
                                break;
                            default:
                                echo 'Onbekend';
                        }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($user['Exameninformatie']); ?></td>
                    <td><?= $user['Actief'] == 1 ? 'Ja' : 'Nee'; ?></td>
                    <td><?= $user['Geslaagd'] == 1 ? 'Ja' : 'Nee'; ?></td>
                    <td>
                        <button class="edit-btn" data-gebruiker-id="<?= htmlspecialchars($user['Gebruiker_id']); ?>">Bewerken</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Er zijn geen gebruikers beschikbaar.</p>
    <?php endif; ?>

    <script>
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const gebruikerId = this.getAttribute('data-gebruiker-id');
                window.location.href = `KlantBewerk.php?gebruiker_id=${gebruikerId}`;
            });
        });
    </script>
</body>
</html>
