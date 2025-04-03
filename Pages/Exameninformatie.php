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
                FROM Gebruiker WHERE Rol = 0";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving users: " . $e->getMessage());
        }
    }

    public function updateExameninformatie($gebruikerId, $exameninformatie) {
        $sql = "UPDATE Gebruiker SET Exameninformatie = :exameninformatie WHERE Gebruiker_id = :gebruiker_id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':exameninformatie', $exameninformatie);
            $stmt->bindParam(':gebruiker_id', $gebruikerId);
            $stmt->execute();
        } catch (PDOException $e) {
            die("Error updating exameninformatie: " . $e->getMessage());
        }
    }
}

$userManager = new UserManager($conn);
$users = $userManager->getAllUsers();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gebruiker_id']) && isset($_POST['exameninformatie'])) {
    $userManager->updateExameninformatie($_POST['gebruiker_id'], $_POST['exameninformatie']);
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}
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
        input[type="text"] {
            padding: 5px;
            width: 80%;
        }
        .back-btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .back-btn:hover {
            background-color: #2980b9;
        }
        .back-btn i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <h2>Overzicht van Leerlingen</h2>

    <a href="main.php" class="back-btn"><i>&#8592;</i> Terug naar Home</a>

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
                    <td>Leerling</td> 
                    <td>
                        <form method="POST" action="">
                            <input type="text" name="exameninformatie" value="<?= htmlspecialchars($user['Exameninformatie']); ?>" required>
                            <input type="hidden" name="gebruiker_id" value="<?= htmlspecialchars($user['Gebruiker_id']); ?>">
                            <button type="submit">Opslaan</button>
                        </form>
                    </td>
                    <td><?= $user['Actief'] == 1 ? 'Ja' : 'Nee'; ?></td>
                    <td><?= $user['Geslaagd'] == 1 ? 'Ja' : 'Nee'; ?></td>
                    <td>
                    
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Er zijn geen Leerlingen beschikbaar.</p>
    <?php endif; ?>
</body>
</html>
