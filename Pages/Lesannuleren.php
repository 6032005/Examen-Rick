<?php
session_start();
include_once '../php/connect.php';

class SessionValidator {
    public static function checkSession() {
        if (!isset($_SESSION['gebruikersnaam'])) {
            echo "Session not set.<br>";  
            echo "Session Data: ";
            print_r($_SESSION);  
            header('Location: login.php');  
            exit;  
        }
    }
}

SessionValidator::checkSession();

class LesAnnuleren {
    private $conn;
    private $les_id;

    public function __construct($conn, $les_id) {
        $this->conn = $conn;
        $this->les_id = $les_id;
    }

    // Get lesson details by ID
    public function getLes() {
        $stmt = $this->conn->prepare("SELECT * FROM Les WHERE Les_id = :les_id");
        $stmt->bindParam(':les_id', $this->les_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cancel lesson
    public function annulerenLes($redenAnnuleren) {
        $stmt = $this->conn->prepare("UPDATE Les SET 
            Geannuleerd = 1, 
            RedenAnnuleren = :redenAnnuleren
            WHERE Les_id = :les_id");
        $stmt->bindParam(':redenAnnuleren', $redenAnnuleren, PDO::PARAM_STR);
        $stmt->bindParam(':les_id', $this->les_id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}

// Check if 'les_id' is passed via GET
if (isset($_GET['les_id'])) {
    $les_id = $_GET['les_id'];

    // Create an object of the LesAnnuleren class
    $lesAnnuleren = new LesAnnuleren($conn, $les_id);
    $les = $lesAnnuleren->getLes();

    if (!$les) {
        echo "Les niet gevonden.";
        exit;
    }

    // Handle form submission to cancel the lesson
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $redenAnnuleren = $_POST['redenAnnuleren'];

        if ($lesAnnuleren->annulerenLes($redenAnnuleren)) {
            echo "Les succesvol geannuleerd.";
            header("Location: lessen.php");
            exit;
        } else {
            echo "Er is een fout opgetreden bij het annuleren van de les.";
        }
    }
} else {
    echo "Geen les ID opgegeven.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les Annuleren</title>
</head>
<body>
    <h2>Les Annuleren</h2>

    <form method="post">
        <p>Weet je zeker dat je deze les wilt annuleren?</p>
        <p><strong>Lestijd:</strong> <?= htmlspecialchars($les['Lestijd']); ?></p>
        <p><strong>Doel:</strong> <?= htmlspecialchars($les['Doel']); ?></p>

        <label for="redenAnnuleren">Reden Annuleren:</label>
        <textarea id="redenAnnuleren" name="redenAnnuleren" required></textarea>
        <br>

        <button type="submit" style="background-color: red; color: white; border: none; padding: 10px 20px; cursor: pointer;">Annuleren</button>
    </form>

    <a href="lessen.php">Terug naar overzicht</a>
</body>
</html>
