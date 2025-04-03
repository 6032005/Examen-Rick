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

    public function getUserById($gebruiker_id) {
        $sql = "SELECT Gebruiker_id, Gebruikersnaam, Email, Wachtwoord, Voornaam, Tussenvoegsel, Achternaam, Rol, Exameninformatie, Actief, Geslaagd 
                FROM Gebruiker WHERE Gebruiker_id = :gebruiker_id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':gebruiker_id', $gebruiker_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving user: " . $e->getMessage());
        }
    }

    public function updateUser($gebruiker_id, $gebruikersnaam, $email, $wachtwoord, $voornaam, $tussenvoegsel, $achternaam, $rol, $exameninformatie, $actief, $geslaagd) {
        $sql = "UPDATE Gebruiker 
                SET Gebruikersnaam = :gebruikersnaam, Email = :email, Wachtwoord = :wachtwoord, Voornaam = :voornaam, Tussenvoegsel = :tussenvoegsel, 
                    Achternaam = :achternaam, Rol = :rol, Exameninformatie = :exameninformatie, Actief = :actief, Geslaagd = :geslaagd
                WHERE Gebruiker_id = :gebruiker_id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':gebruiker_id', $gebruiker_id, PDO::PARAM_INT);
            $stmt->bindParam(':gebruikersnaam', $gebruikersnaam);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':wachtwoord', $wachtwoord);
            $stmt->bindParam(':voornaam', $voornaam);
            $stmt->bindParam(':tussenvoegsel', $tussenvoegsel);
            $stmt->bindParam(':achternaam', $achternaam);
            $stmt->bindParam(':rol', $rol, PDO::PARAM_INT);
            $stmt->bindParam(':exameninformatie', $exameninformatie);
            $stmt->bindParam(':actief', $actief, PDO::PARAM_INT);
            $stmt->bindParam(':geslaagd', $geslaagd, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Error updating user: " . $e->getMessage());
        }
    }
}

$userManager = new UserManager($conn);

if (isset($_GET['gebruiker_id'])) {
    $gebruiker_id = $_GET['gebruiker_id'];
    $user = $userManager->getUserById($gebruiker_id);
} else {
    die("Geen gebruiker ID opgegeven.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gebruiker_id'])) {
    $gebruiker_id = $_POST['gebruiker_id'];
    $gebruikersnaam = $_POST['gebruikersnaam'];
    $email = $_POST['email'];
    $wachtwoord = $_POST['wachtwoord'];
    $voornaam = $_POST['voornaam'];
    $tussenvoegsel = $_POST['tussenvoegsel'];
    $achternaam = $_POST['achternaam'];
    $rol = $_POST['rol'];
    $exameninformatie = $_POST['exameninformatie'];
    $actief = isset($_POST['actief']) ? 1 : 0;
    $geslaagd = isset($_POST['geslaagd']) ? 1 : 0;

    $updateSuccess = $userManager->updateUser($gebruiker_id, $gebruikersnaam, $email, $wachtwoord, $voornaam, $tussenvoegsel, $achternaam, $rol, $exameninformatie, $actief, $geslaagd);

    if ($updateSuccess) {
    
        header('Location: klantenoverzicht.php');
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruiker Bewerken</title>
    <style>
        form {
            width: 60%;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button.cancel {
            background-color: red;
        }
    </style>
</head>
<body>
    <h2>Gegevens Bewerken van Gebruiker</h2>

    <?php if ($user): ?>
        <form action="" method="POST">
            <input type="hidden" name="gebruiker_id" value="<?= htmlspecialchars($user['Gebruiker_id']); ?>">

            <label for="gebruikersnaam">Gebruikersnaam</label>
            <input type="text" name="gebruikersnaam" value="<?= htmlspecialchars($user['Gebruikersnaam']); ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['Email']); ?>" required>

            <label for="wachtwoord">Wachtwoord</label>
            <input type="password" name="wachtwoord" value="<?= htmlspecialchars($user['Wachtwoord']); ?>" required>

            <label for="voornaam">Voornaam</label>
            <input type="text" name="voornaam" value="<?= htmlspecialchars($user['Voornaam']); ?>" required>

            <label for="tussenvoegsel">Tussenvoegsel</label>
            <input type="text" name="tussenvoegsel" value="<?= htmlspecialchars($user['Tussenvoegsel']); ?>">

            <label for="achternaam">Achternaam</label>
            <input type="text" name="achternaam" value="<?= htmlspecialchars($user['Achternaam']); ?>" required>

            <label for="rol">Rol</label>
            <select name="rol" required>
                <option value="0" <?= $user['Rol'] == 0 ? 'selected' : ''; ?>>Leerling</option>
                <option value="1" <?= $user['Rol'] == 1 ? 'selected' : ''; ?>>Instructeur</option>
                <option value="2" <?= $user['Rol'] == 2 ? 'selected' : ''; ?>>Rijschooleigenaar</option>
            </select>

            <label for="exameninformatie">Exameninformatie</label>
            <input type="text" name="exameninformatie" value="<?= htmlspecialchars($user['Exameninformatie']); ?>">

            <label for="actief">Actief</label>
            <input type="checkbox" name="actief" <?= $user['Actief'] == 1 ? 'checked' : ''; ?>>

            <label for="geslaagd">Geslaagd</label>
            <input type="checkbox" name="geslaagd" <?= $user['Geslaagd'] == 1 ? 'checked' : ''; ?>>

            <button type="submit">Opslaan</button>
            <a href="klantenoverzicht.php"><button type="button" class="cancel">Annuleren</button></a>
        </form>
    <?php else: ?>
        <p>Geen gebruiker gevonden om te bewerken.</p>
    <?php endif; ?>

</body>
</html>
