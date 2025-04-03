<?php
session_start();
include_once '../php/connect.php';

class Login {
    private $conn;
    private $errorMessage;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->errorMessage = "";
    }

    public function handleLoginRequest() {
        if (isset($_SESSION['gebruikersnaam'])) {
            header('Location: main.php');
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $sql = "SELECT * FROM Gebruiker WHERE gebruikersnaam = :username";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_get_info($user['Wachtwoord'])['algo']) {
                    if (password_verify($password, $user['Wachtwoord'])) {
                        $_SESSION['gebruikersnaam'] = $username;
                        $_SESSION['rol'] = $user['Rol'];
                        header('Location: main.php');
                        exit;
                    }
                } else {
                    if ($password === $user['Wachtwoord']) {
                        $_SESSION['gebruikersnaam'] = $username;
                        $_SESSION['rol'] = $user['Rol'];
                        header('Location: main.php');
                        exit;
                    }
                }

                $this->errorMessage = "Ongeldige gebruikersnaam of wachtwoord.";
            } else {
                $this->errorMessage = "Ongeldige gebruikersnaam of wachtwoord.";
            }
        }
    }

    public function renderLoginForm() {
        ?>
        <h2>Login</h2>
        <form method="POST" action="login.php">
            <label for="username">Gebruikersnaam:</label>
            <input type="text" id="username" name="username" required><br><br>

            <label for="password">Wachtwoord:</label>
            <input type="password" id="password" name="password" required><br><br>

            <input type="submit" value="Login">
            <a href="Register.php">Registreer</a>
        </form>

        <?php
        if (!empty($this->errorMessage)) {
            echo "<p style='color: red;'>{$this->errorMessage}</p>";
        }
    }
}

$login = new Login($conn);
$login->handleLoginRequest();
$login->renderLoginForm();
?>
