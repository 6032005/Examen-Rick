<?php
session_start();

class User {
    private $role;
    private $username;
    private $roleName;

    public function __construct($role, $username) {
        $this->role = $role;
        $this->username = $username;
        $this->setRoleName();
    }

    private function setRoleName() {
        switch ($this->role) {
            case 0:
                $this->roleName = 'Leerling';
                break;
            case 1:
                $this->roleName = 'Instructeur';
                break;
            case 2:
                $this->roleName = 'RijschoolEigenaar';
                break;
            default:
                $this->roleName = 'Unknown';
        }
    }

    public function getUsername() {
        return $this->username;
    }

    public function getRoleName() {
        return $this->roleName;
    }

    public function getRole() {
        return $this->role;
    }
}

if (!isset($_SESSION['gebruikersnaam'])) {
    header('Location: login.php');
    exit;
}

$user = new User($_SESSION['rol'], $_SESSION['gebruikersnaam']);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rijschool Vierkante Wielen</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Rijschool Vierkante Wielen</h1>
        <nav>
            <a href="main.php">Home</a>
            <?php if ($user->getRole() != 2): ?>
                <a href="profielbewerken.php">Profiel Bewerken</a>
            <?php endif; ?>
            <a href="logout.php">Uitloggen</a>
        </nav>
    </header>

    <div class="logo-title">
        <h2>Welkom bij Rijschool Vierkante Wielen!</h2>
    </div>

    <div class="main-content">
        <h2>Welkom, <?php echo htmlspecialchars($user->getUsername()); ?>!</h2>
        <p>Your role: <?php echo $user->getRoleName(); ?></p>

        <div class="menu">
            <?php if ($user->getRole() == 0):  ?>
                <a href="Lespakketten.php">Lespakket Registreren</a>
            <?php endif; ?>

            <?php if ($user->getRole() == 1 || $user->getRole() == 0):  ?>
                <a href="Lessen.php">Lessen</a>
            <?php endif; ?>

            <?php if ($user->getRole() == 1):  ?>
                <a href="Ziekmelding.php">Ziekmelden</a>
                <a href="Exameninformatie.php">Exameninformatie Bewerken</a>
                <a href="lesinplannen.php">Les Inplannen</a>
            <?php endif; ?>

            <?php if ($user->getRole() == 2): ?>
                <a href="InstructeurAanpassen.php">Instructeurs Aanpassen</a>
                <a href="Klantenoverzicht.php">Klantenoverzicht</a>
                <a href="Wagenpark.php">Wagenpark</a>
                <a href="Prestatieoverzicht.php">Prestatie Overzicht</a>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Rijschool Vierkante Wielen. All rights reserved.</p>
        <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
    </footer>
</body>
</html>
