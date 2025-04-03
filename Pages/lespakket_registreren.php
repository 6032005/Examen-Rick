<?php
session_start();

if (!isset($_SESSION['gebruikersnaam'])) { 
    echo "Session not set properly. Session data: ";
    print_r($_SESSION);
    exit("Redirecting to login.php...");
    header('Location: login.php');
    exit;
}

include_once '../php/connect.php';

class Lespakket {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllLespakketten() {
        $sql = "SELECT * FROM Lespakket";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class Gebruiker {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getGebruikerIdByUsername($username) {
        $sql = "SELECT Gebruiker_id FROM Gebruiker WHERE gebruikersnaam = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user['Gebruiker_id'] ?? null;
    }

    public function linkLespakketToGebruiker($gebruiker_id, $lespakket_id) {
        $sql = "INSERT INTO Gebruiker_lespakket (GebruikerGebruiker_id, LespakketLespakket_id) 
                VALUES (:gebruiker_id, :lespakket_id)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':gebruiker_id', $gebruiker_id, PDO::PARAM_INT);
        $stmt->bindParam(':lespakket_id', $lespakket_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

class LespakketPage {
    private $lespakketManager;
    private $gebruikerManager;
    private $message;

    public function __construct($lespakketManager, $gebruikerManager) {
        $this->lespakketManager = $lespakketManager;
        $this->gebruikerManager = $gebruikerManager;
        $this->message = '';
    }

    public function handleFormSubmission() {
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['lespakket_id'])) {
            $lespakket_id = (int) $_POST['lespakket_id'];
            $username = $_SESSION['gebruikersnaam']; 
            $gebruiker_id = $this->gebruikerManager->getGebruikerIdByUsername($username);

            if ($gebruiker_id && $this->gebruikerManager->linkLespakketToGebruiker($gebruiker_id, $lespakket_id)) {
                $_SESSION['message'] = "Lespakket succesvol toegevoegd!";
            } else {
                $_SESSION['message'] = "Er is een fout opgetreden bij het toevoegen van het lespakket.";
            }

            header('Location: Lespakketten.php');
            exit;
        }
    }

    public function renderLespakketForm() {
        $lespakketten = $this->lespakketManager->getAllLespakketten();

        echo '<form method="POST" action="">
                <label for="lespakket_id">Kies Lespakket:</label>
                <select name="lespakket_id" id="lespakket_id" required>';
        
        foreach ($lespakketten as $row) {
            echo '<option value="' . htmlspecialchars($row['Lespakket_id']) . '">'
                 . htmlspecialchars($row['Naam']) . ' - ' 
                 . htmlspecialchars($row['Prijs']) . ' € - ' 
                 . htmlspecialchars($row['Aantal']) . ' lessen</option>';
        }

        echo '</select><br><br>
              <input type="submit" value="Lespakket registreren">
            </form>';

        echo '<br><br>';
        echo '<a href="Lespakketten.php">Terug naar Lespakket</a>';
    }

    public function renderLespakkettenTable() {
        $lespakketten = $this->lespakketManager->getAllLespakketten();

        echo '<h3>Alle beschikbare lespakketten</h3>';
        echo '<table border="1">
                <tr>
                    <th>Naam</th>
                    <th>Omschrijving</th>
                    <th>Aantal</th>
                    <th>Prijs (€)</th>
                    <th>Soortles</th>
                </tr>';

        foreach ($lespakketten as $row) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['Naam']) . "</td>
                    <td>" . htmlspecialchars($row['Omschrijving']) . "</td>
                    <td>" . htmlspecialchars($row['Aantal']) . "</td>
                    <td>" . htmlspecialchars($row['Prijs']) . "</td>
                    <td>" . htmlspecialchars($row['Soortles']) . "</td>
                  </tr>";
        }

        echo '</table>';
    }

    public function renderMessage() {
        if (isset($_SESSION['message'])) {
            echo '<p>' . htmlspecialchars($_SESSION['message']) . '</p>';
            unset($_SESSION['message']);  
        }
    }

    public function renderPage() {
        $this->renderMessage();
        $this->renderLespakketForm();
        $this->renderLespakkettenTable();
    }
}

$app = new LespakketPage(
    new Lespakket($conn),
    new Gebruiker($conn)
);
$app->handleFormSubmission();
$app->renderPage();
?>
