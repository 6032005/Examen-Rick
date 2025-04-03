<?php
include_once '../php/connect.php';

session_start();

if (!isset($_SESSION['gebruikersnaam'])) {
    header('Location: login.php');
    exit;
}

$sql = "SELECT Gebruiker_id, gebruikersnaam, email, voornaam, tussenvoegsel, achternaam, rol, actief FROM Gebruiker WHERE rol = 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$instructeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructeurs</title>
</head>
<body>
    <h2>Instructeurs</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Gebruikersnaam</th>
                <th>Email</th>
                <th>Voornaam</th>
                <th>Tussenvoegsel</th>
                <th>Achternaam</th>
                <th>Rol</th>
                <th>Actief</th>
                <th>Actie</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($instructeurs as $instructeur): ?>
                <tr>
                    <td><?php echo htmlspecialchars($instructeur['gebruikersnaam']); ?></td>
                    <td><?php echo htmlspecialchars($instructeur['email']); ?></td>
                    <td><?php echo htmlspecialchars($instructeur['voornaam']); ?></td>
                    <td><?php echo htmlspecialchars($instructeur['tussenvoegsel']); ?></td>
                    <td><?php echo htmlspecialchars($instructeur['achternaam']); ?></td>
                    <td><?php echo $instructeur['rol'] == 1 ? 'Instructeur' : 'Onbekend'; ?></td>
                    <td><?php echo $instructeur['actief'] == 1 ? 'Ja' : 'Nee'; ?></td>
                    <td>
                        <a href="InstructeurAanpassenInfo.php?id=<?php echo $instructeur['Gebruiker_id']; ?>">Bewerken</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
