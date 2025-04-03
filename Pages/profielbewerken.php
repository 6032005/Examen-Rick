<?php
include_once '../php/connect.php';

session_start();

if (!isset($_SESSION['gebruikersnaam'])) {
    header('Location: login.php');
    exit;
}

class ProfielBewerken {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getGebruikerData($gebruikersnaam) {
        $sql = "SELECT * FROM Gebruiker WHERE Gebruikersnaam = :gebruikersnaam";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':gebruikersnaam', $gebruikersnaam, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfiel($gebruikersnaam, $email, $voornaam, $tussenvoegsel, $achternaam) {
        $update_sql = "UPDATE Gebruiker SET
            Gebruikersnaam = :gebruikersnaam,
            Email = :email,
            Voornaam = :voornaam,
            Tussenvoegsel = :tussenvoegsel,
            Achternaam = :achternaam
            WHERE Gebruikersnaam = :gebruikersnaam";

        $update_stmt = $this->conn->prepare($update_sql);
        $update_stmt->bindParam(':gebruikersnaam', $gebruikersnaam);
        $update_stmt->bindParam(':email', $email);
        $update_stmt->bindParam(':voornaam', $voornaam);
        $update_stmt->bindParam(':tussenvoegsel', $tussenvoegsel);
        $update_stmt->bindParam(':achternaam', $achternaam);

        return $update_stmt->execute();
    }
}

$profielBewerken = new ProfielBewerken($conn);

$gebruikersnaam = $_SESSION['gebruikersnaam'];

$gebruiker = $profielBewerken->getGebruikerData($gebruikersnaam);

if (!$gebruiker) {
    echo "Gebruiker niet gevonden.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gebruikersnaam = $_POST['gebruikersnaam'];
    $email = $_POST['email'];
    $voornaam = $_POST['voornaam'];
    $tussenvoegsel = $_POST['tussenvoegsel'];
    $achternaam = $_POST['achternaam'];

    if ($profielBewerken->updateProfiel($gebruikersnaam, $email, $voornaam, $tussenvoegsel, $achternaam)) {
        echo "Profiel succesvol bijgewerkt!";
        // Herlaad de vernieuwde gegevens na het updaten
        $gebruiker = $profielBewerken->getGebruikerData($gebruikersnaam);
    } else {
        echo "Er is een fout opgetreden bij het bijwerken van het profiel.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profiel Bewerken</title>
</head>
<body>

<h2>Profiel Bewerken</h2>

<form method="POST" action="ProfielBewerken.php">
    <label for="gebruikersnaam">Gebruikersnaam:</label>
    <input type="text" id="gebruikersnaam" name="gebruikersnaam" value="<?php echo htmlspecialchars($gebruiker['Gebruikersnaam']); ?>" required><br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($gebruiker['Email']); ?>" required><br><br>

    <label for="voornaam">Voornaam:</label>
    <input type="text" id="voornaam" name="voornaam" value="<?php echo htmlspecialchars($gebruiker['Voornaam']); ?>" required><br><br>

    <label for="tussenvoegsel">Tussenvoegsel:</label>
    <input type="text" id="tussenvoegsel" name="tussenvoegsel" value="<?php echo htmlspecialchars($gebruiker['Tussenvoegsel']); ?>"><br><br>

    <label for="achternaam">Achternaam:</label>
    <input type="text" id="achternaam" name="achternaam" value="<?php echo htmlspecialchars($gebruiker['Achternaam']); ?>" required><br><br>

    <input type="submit" value="Bewerk Profiel">
</form>

<!-- Terugknop naar Main.php -->
<form action="Main.php">
    <input type="submit" value="Ga terug naar de vorige pagina">
</form>

</body>
</html>
