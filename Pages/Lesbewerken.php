<?php
session_start();
include_once '../php/connect.php';  

class LessonManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

 
    public function getLessonById($les_id) {
        $stmt = $this->conn->prepare("SELECT 
            l.Les_id,
            l.Lestijd,
            l.Doel,
            l.Geannuleerd,
            l.RedenAnnuleren,
            lp.Naam AS LespakketNaam,
            l.LespakketLespakket_id,
            a.Auto_id,
            o.Ophaallocatie_id
        FROM Les l
        LEFT JOIN Lespakket lp ON l.LespakketLespakket_id = lp.Lespakket_id
        LEFT JOIN Auto a ON l.AutoAuto_id = a.Auto_id
        LEFT JOIN Ophaallocatie o ON l.OphaallocatieOphaallocatie_id = o.Ophaallocatie_id
        WHERE l.Les_id = :les_id");

        $stmt->bindParam(':les_id', $les_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLesson($les_id, $lestijd, $doel, $lespakket_id, $auto_id, $ophaallocatie_id) {
        $stmt = $this->conn->prepare("UPDATE Les SET 
            Lestijd = :lestijd,
            Doel = :doel,
            LespakketLespakket_id = :lespakket_id,
            AutoAuto_id = :auto_id,
            OphaallocatieOphaallocatie_id = :ophaallocatie_id
        WHERE Les_id = :les_id");

        $stmt->bindParam(':lestijd', $lestijd);
        $stmt->bindParam(':doel', $doel);
        $stmt->bindParam(':lespakket_id', $lespakket_id, PDO::PARAM_INT);
        $stmt->bindParam(':auto_id', $auto_id, PDO::PARAM_INT);
        $stmt->bindParam(':ophaallocatie_id', $ophaallocatie_id, PDO::PARAM_INT);
        $stmt->bindParam(':les_id', $les_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    
    public function getLessonPackages() {
        return $this->conn->query("SELECT Lespakket_id, Naam FROM Lespakket")->fetchAll(PDO::FETCH_ASSOC);
    }

  
    public function getAutos() {
        return $this->conn->query("SELECT Auto_id, CONCAT(Merk, ' ', Model) AS AutoNaam FROM Auto")->fetchAll(PDO::FETCH_ASSOC);
    }

  
    public function getPickUpLocations() {
        return $this->conn->query("SELECT Ophaallocatie_id, CONCAT(Adres, ', ', Plaats) AS Ophaallocatie FROM Ophaallocatie")->fetchAll(PDO::FETCH_ASSOC);
    }
}


class SessionValidator {
    public static function checkSession() {
        if (!isset($_SESSION['gebruikersnaam'])) {
            echo "Session not set. Session data: ";
            print_r($_SESSION); 
            exit("Redirecting to login.php...");
            header('Location: login.php');
            exit;
        }
    }
}


SessionValidator::checkSession();


$lessonManager = new LessonManager($conn);


if (isset($_GET['les_id'])) {
    $les_id = $_GET['les_id'];
    $lesson = $lessonManager->getLessonById($les_id);
    
    if (!$lesson) {
        exit("Les niet gevonden.");
    }
} else {
    exit("Geen les ID opgegeven.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lestijd = $_POST['lestijd'];
    $doel = $_POST['doel'];
    $lespakket_id = $_POST['lespakket'];
    $auto_id = $_POST['auto'];
    $ophaallocatie_id = $_POST['ophaallocatie'];

    if ($lessonManager->updateLesson($les_id, $lestijd, $doel, $lespakket_id, $auto_id, $ophaallocatie_id)) {
        header("Location: lessen.php");
        exit;
    } else {
        echo "Er is een fout opgetreden bij het bijwerken van de les.";
    }
}

$lespakketten = $lessonManager->getLessonPackages();
$autos = $lessonManager->getAutos();
$ophaallocaties = $lessonManager->getPickUpLocations();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les Bewerken</title>
    <style>
        form {
            margin: 20px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, button {
            display: block;
            margin-bottom: 15px;
            padding: 8px;
            width: 300px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Les Bewerken</h2>

    <form method="post">
        <label for="lestijd">Lestijd:</label>
        <input type="text" id="lestijd" name="lestijd" value="<?= htmlspecialchars($lesson['Lestijd']); ?>" required>

        <label for="doel">Doel:</label>
        <input type="text" id="doel" name="doel" value="<?= htmlspecialchars($lesson['Doel']); ?>" required>

        <label for="lespakket">Lespakket:</label>
        <select id="lespakket" name="lespakket" required>
            <?php foreach ($lespakketten as $lespakket): ?>
                <option value="<?= $lespakket['Lespakket_id']; ?>" <?= $lespakket['Lespakket_id'] == $lesson['LespakketLespakket_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($lespakket['Naam']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="auto">Auto:</label>
        <select id="auto" name="auto" required>
            <?php foreach ($autos as $auto): ?>
                <option value="<?= $auto['Auto_id']; ?>" <?= $auto['Auto_id'] == $lesson['Auto_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($auto['AutoNaam']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="ophaallocatie">Ophaallocatie:</label>
        <select id="ophaallocatie" name="ophaallocatie" required>
            <?php foreach ($ophaallocaties as $ophaallocatie): ?>
                <option value="<?= $ophaallocatie['Ophaallocatie_id']; ?>" <?= $ophaallocatie['Ophaallocatie_id'] == $lesson['Ophaallocatie_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($ophaallocatie['Ophaallocatie']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Wijzigingen Opslaan</button>
    </form>
</body>
</html>
