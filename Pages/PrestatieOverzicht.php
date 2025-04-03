<?php
session_start();
include_once '../php/connect.php';

class PerformanceOverview {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getTotalRevenue() {
        $sql = "SELECT SUM(Lespakket.Prijs) AS TotalRevenue 
                FROM Lespakket 
                JOIN gebruiker_lespakket ON Lespakket.Lespakket_id = gebruiker_lespakket.LespakketLespakket_id 
                JOIN Gebruiker ON gebruiker_lespakket.GebruikerGebruiker_id = Gebruiker.Gebruiker_id 
                WHERE Gebruiker.Actief = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['TotalRevenue'] ?? 0;
    }

    public function getActiveStudentsCount() {
        $sql = "SELECT COUNT(*) AS ActiveStudents 
                FROM Gebruiker 
                WHERE Actief = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['ActiveStudents'] ?? 0;
    }

    public function getPassingPercentage() {
        $sql = "SELECT COUNT(*) AS TotalStudents 
                FROM Gebruiker 
                WHERE Actief = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $totalStudents = $stmt->fetch(PDO::FETCH_ASSOC)['TotalStudents'] ?? 0;

        if ($totalStudents > 0) {
            $sql = "SELECT COUNT(*) AS PassingStudents 
                    FROM Gebruiker 
                    WHERE Actief = 1 AND Geslaagd = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $passingStudents = $stmt->fetch(PDO::FETCH_ASSOC)['PassingStudents'] ?? 0;

            return ($passingStudents / $totalStudents) * 100;
        }

        return 0;
    }

    public function renderOverview() {
        $totalRevenue = $this->getTotalRevenue();
        $activeStudents = $this->getActiveStudentsCount();
        $passingPercentage = $this->getPassingPercentage();

        echo "<h2>Prestatie Overzicht</h2>";
        echo "<p><strong>Totale Omzet:</strong> €" . number_format($totalRevenue, 2) . "</p>";
        echo "<p><strong>Aantal Actieve Leerlingen:</strong> " . $activeStudents . "</p>";
        echo "<p><strong>Slagingspercentage:</strong> " . number_format($passingPercentage, 2) . "%</p>";
    }
}

$page = new PerformanceOverview($conn);
$page->renderOverview();
?>
