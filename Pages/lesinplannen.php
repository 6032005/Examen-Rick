<?php
session_start();
include_once '../php/connect.php';

if (!isset($_SESSION['gebruikersnaam'])) {
    header('Location: login.php');
    exit;
}

class Lespakket {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getLespakkettenByStudent($gebruiker_id) {
        $sql = "SELECT Lespakket.Lespakket_id, Lespakket.Naam, Lespakket.Omschrijving, Lespakket.Aantal, Lespakket.Prijs, Lespakket.Soortles, 
                Gebruiker.LespakketLespakket_id, Gebruiker.Gebruiker_id, Gebruiker.Gebruikersnaam, Gebruiker.Email, Gebruiker.Wachtwoord, 
                Gebruiker.Voornaam, Gebruiker.Tussenvoegsel, Gebruiker.Achternaam, Gebruiker.Rol, Gebruiker.Exameninformatie, Gebruiker.Actief, 
                Gebruiker.Geslaagd
                FROM Lespakket
                INNER JOIN GebruikerLespakket ON Lespakket.Lespakket_id = GebruikerLespakket.Lespakket_id
                INNER JOIN Gebruiker ON GebruikerLespakket.Gebruiker_id = Gebruiker.Gebruiker_id
                WHERE Gebruiker.Gebruiker_id = :gebruiker_id AND Gebruiker.Actief = 1"; // Filter active users only
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':gebruiker_id', $gebruiker_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class Auto {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllAutos() {
        $sql = "SELECT Auto_id, Merk, Model FROM Auto";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class Ophaallocatie {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllOphaallocaties() {
        $sql = "SELECT Ophaallocatie_id, Adres FROM Ophaallocatie";
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

    public function getAllGebruikers() {
        $sql = "SELECT Gebruiker_id, Gebruikersnaam FROM Gebruiker WHERE Rol = 0"; 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $lestijd = $_POST['lestijd'];
    $doel = $_POST['doel'];
    $lespakket_id = $_POST['lespakket_id'];
    $auto_id = $_POST['auto_id'];
    $ophaallocatie_id = $_POST['ophaallocatie_id'];
    $gebruiker_id = $_POST['gebruiker_id'];

    if (empty($lestijd) || empty($doel)) {
        echo "<p>Error: Lestijd and doel are required!</p>";
    } else {
        try {

            $instructeur_id = $_SESSION['gebruiker_id']; 


            $lestijd = date('Y-m-d H:i:s', strtotime($lestijd));

            $sql = "INSERT INTO Les (Lestijd, Doel, LespakketLespakket_id, AutoAuto_id, OphaallocatieOphaallocatie_id, Gebruiker_id)
                    VALUES (:lestijd, :doel, :lespakket_id, :auto_id, :ophaallocatie_id, :gebruiker_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':lestijd', $lestijd);
            $stmt->bindParam(':doel', $doel);
            $stmt->bindParam(':lespakket_id', $lespakket_id);
            $stmt->bindParam(':auto_id', $auto_id);
            $stmt->bindParam(':ophaallocatie_id', $ophaallocatie_id);
            $stmt->bindParam(':gebruiker_id', $gebruiker_id); 
            $stmt->execute();

            echo "<p>Les succesvol ingepland!</p>";
            header("Location: lessen.php");
            exit;
        } catch (Exception $e) {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Les Inplannen</title>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>

    <script>
        function loadLespakketten(gebruikerId) {
            if (gebruikerId) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'get_lespakketten.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById('lespakket_id').innerHTML = xhr.responseText;
                    }
                };
                xhr.send('gebruiker_id=' + gebruikerId);
            }
        }

        function openModal(gebruikerId) {
            document.getElementById('gebruiker_id').value = gebruikerId;
            loadLespakketten(gebruikerId);
            document.getElementById('lessonModal').style.display = "block";
        }

        function closeModal() {
            document.getElementById('lessonModal').style.display = "none";
        }
    </script>
</head>
<body>
    <h2>Plan een Les voor een Gebruiker</h2>

    <h3>Selecteer een gebruiker</h3>
    <?php
    $gebruiker = new Gebruiker($conn);
    $gebruikers = $gebruiker->getAllGebruikers();

    if ($gebruikers) {
        echo '<ul>';
        foreach ($gebruikers as $row) {
            echo '<li><button onclick="openModal(' . htmlspecialchars($row['Gebruiker_id']) . ')">' . htmlspecialchars($row['Gebruikersnaam']) . '</button></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Geen gebruikers gevonden met rol 0.</p>';
    }
    ?>

    <div id="lessonModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Plan een les</h3>
            <form method="POST" action="">
                <input type="hidden" name="gebruiker_id" id="gebruiker_id" value="" />

                <label for="lespakket_id">Lespakket:</label>
                <select name="lespakket_id" id="lespakket_id" required>
                    <option value="">Kies een lespakket</option>
                </select><br><br>

                <label for="lestijd">Lestijd:</label>
                <input type="datetime-local" name="lestijd" id="lestijd" required><br><br>

                <label for="doel">Doel:</label>
                <textarea name="doel" id="doel" required></textarea><br><br>

                <label for="auto_id">Auto:</label>
                <select name="auto_id" id="auto_id" required>
                    <?php
                    $auto = new Auto($conn);
                    $autos = $auto->getAllAutos();
                    foreach ($autos as $row) {
                        echo '<option value="' . htmlspecialchars($row['Auto_id']) . '">' . htmlspecialchars($row['Merk']) . ' ' . htmlspecialchars($row['Model']) . '</option>';
                    }
                    ?>
                </select><br><br>

                <label for="ophaallocatie_id">Ophaallocatie:</label>
                <select name="ophaallocatie_id" id="ophaallocatie_id" required>
                    <?php
                    $ophaallocatie = new Ophaallocatie($conn);
                    $ophaallocaties = $ophaallocatie->getAllOphaallocaties();
                    foreach ($ophaallocaties as $row) {
                        echo '<option value="' . htmlspecialchars($row['Ophaallocatie_id']) . '">' . htmlspecialchars($row['Adres']) . '</option>';
                    }
                    ?>
                </select><br><br>

                <input type="submit" value="Les Inplannen">
            </form>
        </div>
    </div>
</body>
</html>
