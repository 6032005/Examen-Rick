<?php
session_start();
include_once '../php/connect.php';

if (!isset($_SESSION['gebruikersnaam'])) {
    header('Location: login.php');
    exit;
}

class AutoManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllAutos() {
        $sql = "SELECT Auto_id, Merk, Model, Kenteken, Soort.Type, Soort.Soort_id FROM Auto 
                JOIN Soort ON Auto.SoortSoort_id = Soort.Soort_id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving cars: " . $e->getMessage());
        }
    }

    public function getSoortTypes() {
        $sql = "SELECT * FROM Soort";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error retrieving Soort types: " . $e->getMessage());
        }
    }

    public function addAuto($merk, $model, $kenteken, $type) {
        $sql = "INSERT INTO Auto (Merk, Model, Kenteken, SoortSoort_id) 
                VALUES (:merk, :model, :kenteken, (SELECT Soort_id FROM Soort WHERE Type = :type))";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':merk', $merk, PDO::PARAM_STR);
            $stmt->bindParam(':model', $model, PDO::PARAM_STR);
            $stmt->bindParam(':kenteken', $kenteken, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Error adding car: " . $e->getMessage());
        }
    }

    public function updateAuto($auto_id, $merk, $model, $kenteken, $type) {
        $sql = "UPDATE Auto SET Merk = :merk, Model = :model, Kenteken = :kenteken, 
                SoortSoort_id = (SELECT Soort_id FROM Soort WHERE Type = :type) 
                WHERE Auto_id = :auto_id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':auto_id', $auto_id, PDO::PARAM_INT);
            $stmt->bindParam(':merk', $merk, PDO::PARAM_STR);
            $stmt->bindParam(':model', $model, PDO::PARAM_STR);
            $stmt->bindParam(':kenteken', $kenteken, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Error updating car: " . $e->getMessage());
        }
    }

    public function updateSoort($soort_id, $new_type) {
        $sql = "UPDATE Soort SET Type = :new_type WHERE Soort_id = :soort_id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':new_type', $new_type, PDO::PARAM_STR);
            $stmt->bindParam(':soort_id', $soort_id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die("Error updating Soort type: " . $e->getMessage());
        }
    }

    public function deleteAuto($auto_id) {
        try {
            $sql = "DELETE FROM les WHERE AutoAuto_id = :auto_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':auto_id', $auto_id, PDO::PARAM_INT);
            $stmt->execute();

            $sql = "DELETE FROM Auto WHERE Auto_id = :auto_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':auto_id', $auto_id, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            die("Error deleting car: " . $e->getMessage());
        }
    }

    public function renderAutoTable($autos) {
        if (empty($autos)) {
            echo "<p>Er zijn geen auto's beschikbaar op dit moment.</p>";
            return;
        }

        echo '<table>';
        echo '<tr><th>Merk</th><th>Model</th><th>Kenteken</th><th>Type</th><th>Acties</th></tr>';
        foreach ($autos as $auto) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($auto['Merk'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($auto['Model'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($auto['Kenteken'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($auto['Type'] ?? '') . '</td>';
            echo '<td>';
            echo '<button class="edit-btn" data-auto-id="' . htmlspecialchars($auto['Auto_id']) . '" data-merk="' . htmlspecialchars($auto['Merk']) . '" data-model="' . htmlspecialchars($auto['Model']) . '" data-kenteken="' . htmlspecialchars($auto['Kenteken']) . '" data-type="' . htmlspecialchars($auto['Type']) . '">Bewerken</button>';
            echo '<button class="delete-btn" data-auto-id="' . htmlspecialchars($auto['Auto_id']) . '">Verwijderen</button>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['auto_id']) && isset($_POST['delete'])) {
                $this->deleteAuto($_POST['auto_id']);
                header("Location: Wagenpark.php");
                exit;
            }

            if (isset($_POST['action'])) {
                if ($_POST['action'] == 'add') {
                    $this->addAuto($_POST['merk'], $_POST['model'], $_POST['kenteken'], $_POST['type']);
                } elseif ($_POST['action'] == 'update') {
                    $this->updateAuto($_POST['auto_id'], $_POST['merk'], $_POST['model'], $_POST['kenteken'], $_POST['type']);
                }
                header("Location: Wagenpark.php");
                exit;
            }

            if (isset($_POST['soort_action']) && $_POST['soort_action'] == 'update_type') {
                $this->updateSoort($_POST['soort_id'], $_POST['new_type']);
                header("Location: Wagenpark.php");
                exit;
            }
        }
    }
}

$autoManager = new AutoManager($conn);
$autoManager->handleRequest();
$autos = $autoManager->getAllAutos();
$soorten = $autoManager->getSoortTypes();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wagenpark Overzicht</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        button {
            cursor: pointer;
        }
        .cancel-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 10px 20px;
        }
        .popup {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .popup-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px;
        }
        .popup button {
            margin-top: 10px;
            padding: 10px 20px;
        }
        .popup form {
            display: flex;
            flex-direction: column;
        }
        .popup form input {
            margin-bottom: 10px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h2>Wagenpark Overzicht</h2>

    <button id="add-car-btn">Voeg Nieuwe Auto Toe</button>
    <br><br>

    <?php $autoManager->renderAutoTable($autos); ?>

    <div class="popup" id="add-popup">
        <div class="popup-content">
            <h3>Voeg Nieuwe Auto Toe</h3>
            <form method="post">
                <input type="hidden" name="action" value="add">
                <input type="text" name="merk" placeholder="Merk" required>
                <input type="text" name="model" placeholder="Model" required>
                <input type="text" name="kenteken" placeholder="Kenteken" required>
                <select name="type" required>
                    <?php foreach ($soorten as $soort): ?>
                        <option value="<?= htmlspecialchars($soort['Type']) ?>"><?= htmlspecialchars($soort['Type']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Toevoegen</button>
                <button type="button" id="cancel-add">Annuleren</button>
            </form>
        </div>
    </div>

    <div class="popup" id="edit-popup">
        <div class="popup-content">
            <h3>Bewerk Auto</h3>
            <form method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="auto_id" id="edit-auto-id">
                <input type="text" name="merk" id="edit-merk" placeholder="Merk" required>
                <input type="text" name="model" id="edit-model" placeholder="Model" required>
                <input type="text" name="kenteken" id="edit-kenteken" placeholder="Kenteken" required>
                <select name="type" id="edit-type" required>
                    <?php foreach ($soorten as $soort): ?>
                        <option value="<?= htmlspecialchars($soort['Type']) ?>"><?= htmlspecialchars($soort['Type']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Bewerken</button>
                <button type="button" id="cancel-edit">Annuleren</button>
            </form>
        </div>
    </div>

    <div class="popup" id="delete-popup">
        <div class="popup-content">
            <h3>Weet je zeker dat je deze auto wilt verwijderen?</h3>
            <form method="post">
                <input type="hidden" name="auto_id" id="delete-auto-id">
                <input type="hidden" name="delete" value="1">
                <button type="submit">Ja, Verwijderen</button>
                <button type="button" id="cancel-delete">Annuleren</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('add-car-btn').addEventListener('click', function() {
            document.getElementById('add-popup').style.display = 'flex';
        });

        document.getElementById('cancel-add').addEventListener('click', function() {
            document.getElementById('add-popup').style.display = 'none';
        });

        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const autoId = this.dataset.autoId;
                const merk = this.dataset.merk;
                const model = this.dataset.model;
                const kenteken = this.dataset.kenteken;
                const type = this.dataset.type;

                document.getElementById('edit-auto-id').value = autoId;
                document.getElementById('edit-merk').value = merk;
                document.getElementById('edit-model').value = model;
                document.getElementById('edit-kenteken').value = kenteken;
                document.getElementById('edit-type').value = type;

                document.getElementById('edit-popup').style.display = 'flex';
            });
        });

        document.getElementById('cancel-edit').addEventListener('click', function() {
            document.getElementById('edit-popup').style.display = 'none';
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const autoId = this.dataset.autoId;
                document.getElementById('delete-auto-id').value = autoId;
                document.getElementById('delete-popup').style.display = 'flex';
            });
        });

        document.getElementById('cancel-delete').addEventListener('click', function() {
            document.getElementById('delete-popup').style.display = 'none';
        });
    </script>
</body>
</html>
