<?php
include_once '../php/connect.php';
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class LespakketManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getUserId($username) {
        $sql = "SELECT Gebruiker_id FROM Gebruiker WHERE gebruikersnaam = :username";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC)['Gebruiker_id'];
        }

        return null;
    }

    public function getUserLespakket($user_id) {
        $sql = "SELECT * 
                FROM Lespakket 
                JOIN Gebruiker_Lespakket ON Lespakket.Lespakket_id = Gebruiker_Lespakket.LespakketLespakket_id 
                WHERE Gebruiker_Lespakket.GebruikerGebruiker_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class LespakketPage {
    private $lespakketManager;
    private $username;
    private $lespakketten;

    public function __construct($lespakketManager, $username) {
        $this->lespakketManager = $lespakketManager;
        $this->username = $username;
        $this->lespakketten = [];
    }

    public function loadUserLespakketten() {
        $user_id = $this->lespakketManager->getUserId($this->username);

        if ($user_id) {
            $this->lespakketten = $this->lespakketManager->getUserLespakket($user_id);
        }
    }

    public function renderLespakketten() {
        if (empty($this->lespakketten)) {
            echo "<p>No lespakket data found for this user.</p>";
            return;
        }

        echo "<h1>Lessen van " . htmlspecialchars($this->username) . "</h1>";
        echo "<table border='1'>";
        echo "<tr><th>Lespakket ID</th><th>Naam</th><th>Omschrijving</th><th>Aantal</th><th>Prijs</th><th>Soortles</th></tr>";

        foreach ($this->lespakketten as $row) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['Lespakket_id']) . "</td>
                    <td>" . htmlspecialchars($row['Naam']) . "</td>
                    <td>" . htmlspecialchars($row['Omschrijving']) . "</td>
                    <td>" . htmlspecialchars($row['Aantal']) . "</td>
                    <td>" . htmlspecialchars($row['Prijs']) . " €</td>
                    <td>" . htmlspecialchars($row['Soortles']) . "</td>
                  </tr>";
        }

        echo "</table>";
    }

    public function renderRegistrationForm() {
        echo '<form action="lespakket_registreren.php" method="get">
                <input type="submit" value="Lespakket Registreren">
              </form>';
    }

    public function renderPage() {
        $this->renderLespakketten();
        $this->renderRegistrationForm();
        $this->renderBackToMainMenuLink();
    }

    private function renderBackToMainMenuLink() {
        echo '<p><a href="Main.php">Terug naar het hoofdmenu</a></p>';
    }
}

class LespakketApp {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function run() {
        if (!isset($_SESSION['gebruikersnaam'])) {
            header('Location: login.php');
            exit;
        }

        $username = $_SESSION['gebruikersnaam'];
        $lespakketManager = new LespakketManager($this->conn);
        $lespakketPage = new LespakketPage($lespakketManager, $username);

        $lespakketPage->loadUserLespakketten();
        $lespakketPage->renderPage();
    }
}


$app = new LespakketApp($conn);
$app->run();
?>
