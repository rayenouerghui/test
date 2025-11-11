-- Base de données NextGen
-- Module: Gestion des Sessions de Matchmaking

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS nextgen_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nextgen_db;

-- Table des utilisateurs (supposée existante pour la relation)
-- Cette table doit exister dans le système principal
CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('utilisateur', 'admin') DEFAULT 'utilisateur',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des jeux (supposée existante pour la relation)
-- Cette table doit exister dans le système principal
CREATE TABLE IF NOT EXISTS jeux (
    id_jeu INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    prix DECIMAL(10, 2) NOT NULL,
    categorie VARCHAR(100),
    stock INT DEFAULT 0,
    image_url VARCHAR(255),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des commandes (supposée existante pour vérifier les achats)
CREATE TABLE IF NOT EXISTS commandes (
    id_commande INT PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    id_jeu INT NOT NULL,
    date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'confirmee', 'livree', 'annulee') DEFAULT 'confirmee',
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_jeu) REFERENCES jeux(id_jeu) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table AttenteMatch : Utilisateurs en attente de match pour un jeu
CREATE TABLE IF NOT EXISTS AttenteMatch (
    id_attente INT PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    id_jeu INT NOT NULL,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    matched BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_jeu) REFERENCES jeux(id_jeu) ON DELETE CASCADE,
    INDEX idx_jeu_matched (id_jeu, matched),
    INDEX idx_utilisateur (id_utilisateur)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table SessionMatch : Sessions créées quand un match est trouvé
CREATE TABLE IF NOT EXISTS SessionMatch (
    id_session INT PRIMARY KEY AUTO_INCREMENT,
    id_jeu INT NOT NULL,
    lien_session VARCHAR(255) NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    participants TEXT NOT NULL COMMENT 'JSON array des IDs utilisateurs',
    statut ENUM('active', 'terminee', 'expiree') DEFAULT 'active',
    FOREIGN KEY (id_jeu) REFERENCES jeux(id_jeu) ON DELETE CASCADE,
    INDEX idx_jeu (id_jeu),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données de test (optionnel)
-- Insertion d'utilisateurs de test
INSERT INTO utilisateurs (email, nom, prenom, mot_de_passe, role) VALUES
('admin@nextgen.com', 'Admin', 'NextGen', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('user1@test.com', 'Dupont', 'Jean', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'utilisateur'),
('user2@test.com', 'Martin', 'Marie', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'utilisateur');

-- Insertion de jeux de test
INSERT INTO jeux (nom, description, prix, categorie, stock, image_url) VALUES
('Jungle Quest', 'Aventure dans la jungle', 29.99, 'Aventure', 100, 'https://via.placeholder.com/300x200?text=Jungle+Quest'),
('Space Warriors', 'Combat spatial épique', 39.99, 'Action', 100, 'https://via.placeholder.com/300x200?text=Space+Warriors'),
('Kingdom Builder', 'Construisez votre royaume', 34.99, 'Stratégie', 100, 'https://via.placeholder.com/300x200?text=Kingdom+Builder');

-- Note: Le mot de passe de test est "password" (hashé avec bcrypt)


