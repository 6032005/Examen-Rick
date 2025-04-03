-- Maak het schema 'Rijschool' aan
CREATE SCHEMA Rijschool;

-- Gebruik het schema
USE Rijschool;

-- Maak de 'Soort' tabel aan
CREATE TABLE Soort (
    Soort_id INT(10) AUTO_INCREMENT PRIMARY KEY,
    Type VARCHAR(255) NOT NULL
);

-- Maak de 'Auto' tabel aan
CREATE TABLE Auto (
    Auto_id INT(10) AUTO_INCREMENT PRIMARY KEY,
    Merk VARCHAR(255) NOT NULL,
    Model VARCHAR(255) NOT NULL,
    Kenteken VARCHAR(10) NOT NULL,
    SoortSoort_id INT(10),
    FOREIGN KEY (SoortSoort_id) REFERENCES Soort(Soort_id)
);

-- Maak de 'Ophaallocatie' tabel aan
CREATE TABLE Ophaallocatie (
    Ophaallocatie_id INT(10) AUTO_INCREMENT PRIMARY KEY,
    Adres VARCHAR(255) NOT NULL,
    Postcode VARCHAR(7) NOT NULL,
    Plaats VARCHAR(100) NOT NULL
);

-- Maak de 'Gebruiker' tabel aan
CREATE TABLE Gebruiker (
    Gebruiker_id INT AUTO_INCREMENT PRIMARY KEY,
    Gebruikersnaam VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    Wachtwoord VARCHAR(255) NOT NULL,
    Voornaam VARCHAR(255) NOT NULL,  
    Tussenvoegsel VARCHAR(20),
    Achternaam VARCHAR(255) NOT NULL,
    Rol INT(1) NOT NULL,
    Exameninformatie TEXT NOT NULL,
    Actief INT(1) NOT NULL,
    Geslaagd INT(1) NOT NULL
);

-- Maak de 'Lespakket' tabel aan
CREATE TABLE Lespakket (
    Lespakket_id INT(10) AUTO_INCREMENT PRIMARY KEY,
    Naam VARCHAR(255) NOT NULL,
    Omschrijving TEXT NOT NULL,
    Aantal INT(3) NOT NULL,
    Prijs DECIMAL(15, 2) NOT NULL,
    Soortles VARCHAR(255) NOT NULL
);

-- Maak de 'Onderwerp' tabel aan
CREATE TABLE Onderwerp (
    Onderwerp_id INT(10) AUTO_INCREMENT PRIMARY KEY,
    Onderwerp VARCHAR(255) NOT NULL,
    Omschrijving TEXT NOT NULL
);

-- Maak de 'Les' tabel aan
CREATE TABLE Les (
    Les_id INT(10) AUTO_INCREMENT PRIMARY KEY,
    Lestijd TIME NOT NULL,
    Doel TEXT NOT NULL,
    Geannuleerd INT(1),
    RedenAnnuleren TEXT,
    LespakketLespakket_id INT(10),
    AutoAuto_id INT(10),
    OphaallocatieOphaallocatie_id INT(10),
    Gebruiker_id INT(10),
    FOREIGN KEY (LespakketLespakket_id) REFERENCES Lespakket(Lespakket_id),
    FOREIGN KEY (AutoAuto_id) REFERENCES Auto(Auto_id),
    FOREIGN KEY (OphaallocatieOphaallocatie_id) REFERENCES Ophaallocatie(Ophaallocatie_id),
    FOREIGN KEY (Gebruiker_id) REFERENCES Gebruiker(Gebruiker_id)
);

-- Maak de 'Les_onderwerp' tabel aan
CREATE TABLE Les_onderwerp (
    LesLes_id INT(10),
    OnderwerpOnderwerp_id INT(10),
    PRIMARY KEY (LesLes_id, OnderwerpOnderwerp_id),
    FOREIGN KEY (LesLes_id) REFERENCES Les(Les_id),
    FOREIGN KEY (OnderwerpOnderwerp_id) REFERENCES Onderwerp(Onderwerp_id)
);

-- Maak de 'Ziekmelding' tabel aan
CREATE TABLE Ziekmelding (
    Ziekmelding_id INT(10) AUTO_INCREMENT PRIMARY KEY,
    Van TIMESTAMP NOT NULL,
    Tot TIMESTAMP NOT NULL,
    Toelichting TEXT NOT NULL,
    GebruikerGebruiker_id INT(10),
    FOREIGN KEY (GebruikerGebruiker_id) REFERENCES Gebruiker(Gebruiker_id)
);

-- Maak de 'Gebruiker_lespakket' tabel aan
CREATE TABLE Gebruiker_lespakket (
    Gebruiker_Lespakket_id INT(10) AUTO_INCREMENT PRIMARY KEY,
    LespakketLespakket_id INT(10),
    GebruikerGebruiker_id INT(10),
    FOREIGN KEY (LespakketLespakket_id) REFERENCES Lespakket(Lespakket_id),
    FOREIGN KEY (GebruikerGebruiker_id) REFERENCES Gebruiker(Gebruiker_id)
);

-- Maak de 'Gebruiker_Les' (tussentabel) aan
CREATE TABLE Gebruiker_Les (
    Gebruiker_Les_id INT(10) AUTO_INCREMENT PRIMARY KEY,
    Gebruiker_id INT(10) NOT NULL,
    Les_id INT(10) NOT NULL,
    FOREIGN KEY (Gebruiker_id) REFERENCES Gebruiker(Gebruiker_id),
    FOREIGN KEY (Les_id) REFERENCES Les(Les_id)
);

-- Voeg dummy data in de 'Soort' tabel
INSERT INTO Soort (Type) VALUES 
('B'),
('A'),
('C'),
('D');

-- Voeg dummy data in de 'Auto' tabel
INSERT INTO Auto (Merk, Model, Kenteken, SoortSoort_id) VALUES 
('Volkswagen', 'Golf', 'XX-11-YY', 1),
('BMW', '3 Series', 'YY-22-ZZ', 2),
('Mercedes', 'A-Klasse', 'ZZ-33-AA', 3),
('Audi', 'A4', 'AA-44-BB', 4);

-- Voeg dummy data in de 'Ophaallocatie' tabel
INSERT INTO Ophaallocatie (Adres, Postcode, Plaats) VALUES 
('Stationsweg 1', '1234 AB', 'Amsterdam'),
('Laan van Zuid 45', '2345 BC', 'Rotterdam'),
('Kerkstraat 78', '3456 CD', 'Utrecht'),
('Dorpsplein 9', '4567 DE', 'Den Haag');

-- Voeg dummy data in de 'Gebruiker' tabel (met versleutelde wachtwoorden)
INSERT INTO Gebruiker (Gebruikersnaam, Email, Wachtwoord, Voornaam, Tussenvoegsel, Achternaam, Rol, Exameninformatie, Actief, Geslaagd) VALUES 
('johndoe', 'johndoe@email.com', 'password123', 'John', '', 'Doe', 1, 'Exameninformatie van John', 1, 0),
('janedoe', 'janedoe@email.com', 'mypassword456', 'Jane', '', 'Doe', 2, 'Exameninformatie van Jane', 1, 1),
('peterparker', 'peterparker@email.com', 'parker789', 'Peter', 'van', 'Parker', 0, 'Exameninformatie van Peter', 1, 0),
('maryjane', 'maryjane@email.com', 'mjane123', 'Mary', 'Jane', 'Watson', 0, 'Exameninformatie van Mary', 1, 1);

-- Voeg dummy data in de 'Lespakket' tabel
INSERT INTO Lespakket (Naam, Omschrijving, Aantal, Prijs, Soortles) VALUES 
('Basisrijles', 'Basisopleiding voor rijbewijs B', 20, 1000.00, 'Rijbewijs B'),
('Motorrijles', 'Opleiding voor motorrijbewijs A', 15, 800.00, 'Rijbewijs A'),
('Vrachtwagenrijles', 'Opleiding voor vrachtwagenrijbewijs C', 25, 2000.00, 'Rijbewijs C'),
('Busrijles', 'Opleiding voor busrijbewijs D', 30, 2500.00, 'Rijbewijs D');

-- Voeg dummy data in de 'Gebruiker_lespakket' tabel
INSERT INTO Gebruiker_lespakket (LespakketLespakket_id, GebruikerGebruiker_id) VALUES 
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- Voeg dummy data in de 'Onderwerp' tabel
INSERT INTO Onderwerp (Onderwerp, Omschrijving) VALUES 
('Verkeersregels', 'Basisverkeersregels en verkeersborden'),
('Motoronderhoud', 'Onderhoud en veiligheid van motoren'),
('Vrachtwagenbesturing', 'Besturing en techniek van vrachtwagens'),
('Busbesturing', 'Besturing en techniek van bussen');

-- Voeg dummy data in de 'Les' tabel
INSERT INTO Les (Lestijd, Doel, Geannuleerd, RedenAnnuleren, LespakketLespakket_id, AutoAuto_id, OphaallocatieOphaallocatie_id, Gebruiker_id) VALUES 
('10:00:00', 'Begin met het leren van de basisprincipes van autorijden', 0, NULL, 1, 1, 1, 1),
('14:00:00', 'Motorrijles, leren hoe je een motor bestuurt', 0, NULL, 2, 2, 2, 2),
('09:00:00', 'Lessen voor het vrachtwagenrijbewijs', 0, NULL, 3, 3, 3, 3),
('13:00:00', 'Lessen voor het busrijbewijs', 1, 'Les geannuleerd wegens ziekte', 4, 4, 4, 4);

-- Voeg dummy data in de 'Gebruiker_Les' tabel
INSERT INTO Gebruiker_Les (Gebruiker_id, Les_id) VALUES 
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- Voeg dummy data in de 'Les_onderwerp' tabel
INSERT INTO Les_onderwerp (LesLes_id, OnderwerpOnderwerp_id) VALUES 
(1, 1),
(2, 2),
(3, 3),
(4, 4);
