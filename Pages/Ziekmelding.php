<?php
session_start();

include_once '../php/connect.php';

class Ziekmelding {
    private $conn;
    private $gebruiker_id;
    private $van;
    private $tot;
    private $toelichting;

    public function __construct($conn, $gebruiker_id, $van, $tot, $toelichting) {
        $this->conn = $conn;
        $this->gebruiker_id = $gebruiker_id;
        $this->van = $van;
        $this->tot = $tot;
        $this->toelichting = $toelichting;
    }

    public function indienen() {
        $sql = "INSERT INTO ziekmelding (Van, Tot, Toelichting, GebruikerGebruiker_id) 
                VALUES (:van, :tot, :toelichting, :gebruiker_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':van', $this->van);
        $stmt->bindParam(':tot', $this->tot);
        $stmt->bindParam(':toelichting', $this->toelichting);
        $stmt->bindParam(':gebruiker_id', $this->gebruiker_id);

        if ($stmt->execute()) {
            return "Ziekmelding succesvol ingediend!";
        } else {
            return "Er is een fout opgetreden bij het indienen van de ziekmelding.";
        }
    }
}

if (!isset($_SESSION['gebruikersnaam'])) {
    header('Location: login.php');
    exit;
}

$gebruikersnaam = $_SESSION['gebruikersnaam'];
$sql = "SELECT Gebruiker_id FROM Gebruiker WHERE Gebruikersnaam = :gebruikersnaam";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':gebruikersnaam', $gebruikersnaam);
$stmt->execute();
$gebruiker = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gebruiker) {
    echo "Gebruiker niet gevonden.";
    exit;
}

$gebruiker_id = $gebruiker['Gebruiker_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $van = $_POST['van'];
    $tot = $_POST['tot'];
    $toelichting = $_POST['toelichting'];

    $ziekmelding = new Ziekmelding($conn, $gebruiker_id, $van, $tot, $toelichting);
    $result = $ziekmelding->indienen();
    echo $result;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ziekmelding Indienen</title>
</head>
<body>

<h2>Ziekmelding Indienen</h2>

<form method="POST" action="">
    <label for="van">Datum van ziekte (van):</label>
    <input type="date" id="van" name="van" required><br><br>

    <label for="tot">Datum van ziekte (tot):</label>
    <input type="date" id="tot" name="tot" required><br><br>

    <label for="toelichting">Toelichting:</label><br>
    <textarea id="toelichting" name="toelichting" rows="4" cols="50" required></textarea><br><br>

    <input type="submit" value="Indienen">
</form>

</body>
</html>
