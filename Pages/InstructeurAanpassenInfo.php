<?php
include_once '../php/connect.php';

session_start();

if (!isset($_SESSION['gebruikersnaam'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    echo "User ID is missing.";
    exit;
} else {
    $gebruiker_id = $_GET['id'];  
}

$sql = "SELECT * FROM Gebruiker WHERE Gebruiker_id = :gebruiker_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':gebruiker_id', $gebruiker_id, PDO::PARAM_INT);
$stmt->execute();

$gebruiker = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gebruiker) {
    echo "User not found.";
    exit;
}

$sql_ziekmelding = "SELECT * FROM Ziekmelding WHERE GebruikerGebruiker_id = :gebruiker_id ORDER BY Van DESC LIMIT 1";
$stmt_ziekmelding = $conn->prepare($sql_ziekmelding);
$stmt_ziekmelding->bindParam(':gebruiker_id', $gebruiker_id, PDO::PARAM_INT);
$stmt_ziekmelding->execute();

$ziekmelding = $stmt_ziekmelding->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gebruikersnaam = $_POST['gebruikersnaam'];
    $email = $_POST['email'];
    $voornaam = $_POST['voornaam'];
    $tussenvoegsel = $_POST['tussenvoegsel'];
    $achternaam = $_POST['achternaam'];
    
    $rol = isset($_POST['rol']) ? $_POST['rol'] : $gebruiker['Rol'];  
    $actief = isset($_POST['actief']) ? 1 : 0;

    $update_sql = "UPDATE Gebruiker SET
        Gebruikersnaam = :gebruikersnaam,
        Email = :email,
        Voornaam = :voornaam,
        Tussenvoegsel = :tussenvoegsel,
        Achternaam = :achternaam,
        Rol = :rol,
        Actief = :actief
        WHERE Gebruiker_id = :gebruiker_id";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bindParam(':gebruikersnaam', $gebruikersnaam);
    $update_stmt->bindParam(':email', $email);
    $update_stmt->bindParam(':voornaam', $voornaam);
    $update_stmt->bindParam(':tussenvoegsel', $tussenvoegsel);
    $update_stmt->bindParam(':achternaam', $achternaam);
    $update_stmt->bindParam(':rol', $rol);  
    $update_stmt->bindParam(':actief', $actief);
    $update_stmt->bindParam(':gebruiker_id', $gebruiker_id);

    $update_stmt->execute();

    echo "User information updated successfully!";
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Instructeur</title>
</head>
<body>

<h2>Bewerk Instructeur</h2>

<form method="POST" action="InstructeurAanpassenInfo.php?id=<?php echo $gebruiker_id; ?>">
    <label for="gebruikersnaam">Gebruikersnaam:</label>
    <input type="text" id="gebruikersnaam" name="gebruikersnaam" value="<?php echo htmlspecialchars($gebruiker['Gebruikersnaam']); ?>" required><br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($gebruiker['Email']); ?>" required><br><br>

    <label for="voornaam">Voornaam:</label>
    <input type="text" id="voornaam" name="voornaam" value="<?php echo htmlspecialchars($gebruiker['Voornaam']); ?>" required><br><br>

    <label for="tussenvoegsel">Tussenvoegsel:</label>
    <input type="text" id="tussenvoegsel" name="tussenvoegsel" value="<?php echo htmlspecialchars($gebruiker['Tussenvoegsel']); ?>"><br><br>

    <label for="achternaam">Achternaam:</label>
    <input type="text" id="achternaam" name="achternaam" value="<?php echo htmlspecialchars($gebruiker['Achternaam']); ?>" required><br><br>

    <label for="rol">Rol:</label>
    <select id="rol" name="rol">
        <option value="1" <?php echo $gebruiker['Rol'] == 1 ? 'selected' : ''; ?>>Instructeur</option>
        <option value="2" <?php echo $gebruiker['Rol'] == 2 ? 'selected' : ''; ?>>Leerling</option>
    </select><br><br>

    <label for="actief">Actief:</label>
    <input type="checkbox" id="actief" name="actief" <?php echo $gebruiker['Actief'] ? 'checked' : ''; ?>><br><br>

    <input type="submit" value="Bewerk">
</form>

<h3>Ziekmelding</h3>
<?php if ($ziekmelding) { ?>
    <p>De instructeur heeft zich ziek gemeld van: <?php echo htmlspecialchars($ziekmelding['Van']); ?> tot <?php echo htmlspecialchars($ziekmelding['Tot']); ?></p>
    <p>Toelichting: <?php echo htmlspecialchars($ziekmelding['Toelichting']); ?></p>
<?php } else { ?>
    <p>Er is geen ziekmelding geregistreerd voor deze instructeur.</p>
<?php } ?>

</body>
</html>
