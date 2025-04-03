<?php
session_start();
include_once '../php/connect.php';

class RegistrationManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
// Check dat het niet leeg is 
    public function registerUser($username, $password, $voornaam, $tussenvoegsel, $achternaam, $email, $role = 0) {
        if (empty($username) || empty($password) || empty($voornaam) || empty($achternaam) || empty($email)) {
            return false; 
        }

        
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO Gebruiker (gebruikersnaam, Wachtwoord, Voornaam, Tussenvoegsel, Achternaam, Email, Rol, Exameninformatie, Actief, Geslaagd) 
                VALUES (:gebruikersnaam, :wachtwoord, :voornaam, :tussenvoegsel, :achternaam, :email, :rol, '', 1, 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':gebruikersnaam', $username);
        $stmt->bindParam(':wachtwoord', $password_hashed);
        $stmt->bindParam(':voornaam', $voornaam);
        $stmt->bindParam(':tussenvoegsel', $tussenvoegsel);
        $stmt->bindParam(':achternaam', $achternaam);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':rol', $role); 

        
        return $stmt->execute();
    }
}

class RegisterPage {
    private $registrationManager;
    private $errorMessage;

    public function __construct($conn) {
        $this->registrationManager = new RegistrationManager($conn);
        $this->errorMessage = "";
    }

    public function handleRegisterRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $voornaam = $_POST['voornaam'] ?? '';
            $tussenvoegsel = $_POST['tussenvoegsel'] ?? '';
            $achternaam = $_POST['achternaam'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = isset($_POST['role']) ? $_POST['role'] : 0; 

            if (empty($username) || empty($password) || empty($voornaam) || empty($achternaam) || empty($email)) {
                $this->errorMessage = "Please fill in all required fields.";
                return;
            }

            if ($this->registrationManager->registerUser($username, $password, $voornaam, $tussenvoegsel, $achternaam, $email, $role)) {
                header('Location: login.php');
                exit;
            } else {
                $this->errorMessage = "Registration failed. Please try again.";
            }
        }
    }

    public function renderRegisterForm() {
        ?>
        <h2>Register</h2>
        <form method="POST" action="Register.php">
            <label for="username">Gebruikersnaam:</label>
            <input type="text" id="username" name="username" required><br><br>

            <label for="password">Wachtwoord:</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="voornaam">Voornaam:</label>
            <input type="text" id="voornaam" name="voornaam" required><br><br>

            <label for="tussenvoegsel">Tussenvoegsel:</label>
            <input type="text" id="tussenvoegsel" name="tussenvoegsel"><br><br>

            <label for="achternaam">Achternaam:</label>
            <input type="text" id="achternaam" name="achternaam" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <input type="submit" value="Register">
        </form>

        <?php
        if (!empty($this->errorMessage)) {
            echo "<p style='color: red;'>{$this->errorMessage}</p>";
        }
    }
}

$page = new RegisterPage($conn);
$page->handleRegisterRequest();
$page->renderRegisterForm();
?>
