-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 08 mai 2026 à 12:37
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `greenbite`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id_avis` int(10) UNSIGNED NOT NULL,
  `id_produit` int(10) UNSIGNED NOT NULL,
  `id_utilisateur` int(10) UNSIGNED NOT NULL,
  `note` tinyint(4) NOT NULL CHECK (`note` between 1 and 5),
  `titre` varchar(150) NOT NULL,
  `texte` text NOT NULL,
  `statut` enum('en-attente','approuve','rejet') NOT NULL DEFAULT 'en-attente',
  `nombre_utilites` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `date_avis` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id_avis`, `id_produit`, `id_utilisateur`, `note`, `titre`, `texte`, `statut`, `nombre_utilites`, `date_avis`) VALUES
(2, 51, 22, 4, 'WOWW Produit mch normale', 'yeser bniiiiin w fih les vitamines', 'approuve', 0, '2026-05-04 23:01:48'),
(3, 51, 22, 4, 'raw3a', 'behy barcha', 'approuve', 0, '2026-05-05 09:39:59');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id_categorie` int(11) NOT NULL,
  `nom` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `icone` varchar(50) DEFAULT NULL,
  `couleur` varchar(7) DEFAULT '#16a34a',
  `date_ajout` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id_categorie`, `nom`, `description`, `icone`, `couleur`, `date_ajout`) VALUES
(1, 'Produits laitiers', NULL, '🥛', '#3b82f6', '2026-05-04 11:38:27'),
(2, 'Boissons', NULL, '🥤', '#06b6d4', '2026-05-04 11:38:27'),
(3, 'Cereales & Pains', NULL, '🍞', '#f59e0b', '2026-05-04 11:38:27'),
(4, 'Epicerie', NULL, '🏪', '#8b5cf6', '2026-05-04 11:38:27'),
(5, 'Snacks & Biscuits', NULL, '🍪', '#ec4899', '2026-05-04 11:38:27'),
(6, 'Conserves', NULL, '🥫', '#6366f1', '2026-05-04 11:38:27'),
(7, 'Fruits & Legumes', NULL, '🥦', '#10b981', '2026-05-04 11:38:27'),
(8, 'Viandes & Poissons', NULL, '🍗', '#ef4444', '2026-05-04 11:38:27'),
(9, 'Produits surgelés', NULL, '🧊', '#0ea5e9', '2026-05-04 11:38:27'),
(10, 'Chocolat & Bonbons', NULL, '🍫', '#a16207', '2026-05-04 11:38:27'),
(11, 'Cafe & The', NULL, '☕', '#b45309', '2026-05-04 11:38:27'),
(12, 'Miel & Confitures', NULL, '🍯', '#dcfce7', '2026-05-04 11:38:27'),
(13, 'Huiles & Condiments', NULL, '🧈', '#fef3c7', '2026-05-04 11:38:27'),
(14, 'Produits bio', NULL, '🌱', '#dcfce7', '2026-05-04 11:38:27'),
(15, 'Petit déjeuner', NULL, '🥐', '#fed7aa', '2026-05-04 11:38:27');

-- --------------------------------------------------------

--
-- Structure de la table `chat_analytics`
--

CREATE TABLE `chat_analytics` (
  `id_analytics` int(11) NOT NULL,
  `date_jour` date NOT NULL,
  `nombre_conversations` int(11) DEFAULT 0,
  `nombre_messages` int(11) DEFAULT 0,
  `nombre_recommendations` int(11) DEFAULT 0,
  `taux_conversion` decimal(5,2) DEFAULT 0.00,
  `panier_moyen` decimal(10,2) DEFAULT 0.00,
  `intent_populaire` varchar(50) DEFAULT NULL,
  `satisfaction_moyenne` decimal(3,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id_conversation` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `titre` varchar(200) DEFAULT 'Nouvelle conversation',
  `statut` enum('actif','archive','supprime') DEFAULT 'actif',
  `sentiment_general` varchar(20) DEFAULT 'neutral',
  `budget_estime` decimal(10,2) DEFAULT 0.00,
  `preferences_detectees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences_detectees`)),
  `allergies_detectees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allergies_detectees`)),
  `regime_detecte` varchar(100) DEFAULT NULL,
  `date_debut` datetime NOT NULL DEFAULT current_timestamp(),
  `date_dernier_message` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_conversations`
--

INSERT INTO `chat_conversations` (`id_conversation`, `id_utilisateur`, `titre`, `statut`, `sentiment_general`, `budget_estime`, `preferences_detectees`, `allergies_detectees`, `regime_detecte`, `date_debut`, `date_dernier_message`) VALUES
(1, 1, 'Conversation du 04/05/2026 13:09', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 12:09:33', '2026-05-04 12:09:33'),
(2, 1, 'Conversation du 04/05/2026 13:09', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 12:09:40', '2026-05-04 12:09:40'),
(3, 1, 'Conversation du 04/05/2026 13:10', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 12:10:25', '2026-05-04 12:10:25'),
(4, 1, 'Conversation du 04/05/2026 13:11', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 12:11:00', '2026-05-04 12:11:00'),
(5, 1, 'Conversation du 04/05/2026 13:11', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 12:11:24', '2026-05-04 12:11:24'),
(6, 1, 'Conversation du 04/05/2026 13:11', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 12:11:32', '2026-05-04 12:11:32'),
(7, 1, 'Chat du 04/05/2026 13:12', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 12:12:38', '2026-05-04 12:12:38'),
(8, 1, 'Chat du 04/05/2026 13:13', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 12:13:19', '2026-05-04 12:13:19'),
(9, 1, 'Chat du 04/05/2026 13:13', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 12:13:33', '2026-05-04 12:13:33'),
(10, 1, 'Chat du 04/05/2026 13:13', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 13:13:55', '2026-05-04 12:13:55'),
(11, 1, 'Chat du 04/05/2026 13:14', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 13:14:09', '2026-05-04 12:14:09'),
(12, 1, 'Chat du 04/05/2026 13:14', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 13:14:36', '2026-05-04 12:14:36'),
(13, 1, 'Chat du 04/05/2026 13:14', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 13:14:51', NULL),
(14, 1, 'Chat du 04/05/2026 13:15', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 13:15:01', '2026-05-04 12:15:01'),
(15, 1, 'Chat du 04/05/2026 13:15', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 13:15:24', NULL),
(16, 1, 'Chat du 04/05/2026 13:15', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 13:15:53', '2026-05-04 12:15:53'),
(17, 1, 'Chat du 04/05/2026 13:16', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 13:16:06', '2026-05-04 12:16:06'),
(18, 1, 'Chat du 04/05/2026 13:16', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 13:16:28', '2026-05-04 12:16:34'),
(19, 1, 'Chat du 04/05/2026 20:00', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 20:00:27', '2026-05-04 19:00:42'),
(20, 1, 'Chat du 04/05/2026 20:10', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-04 20:10:30', '2026-05-04 19:11:39'),
(21, 1, 'Chat du 05/05/2026 00:04', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-05 00:04:22', '2026-05-04 23:04:46'),
(22, 1, 'Chat du 05/05/2026 00:14', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-05 00:14:32', '2026-05-04 23:14:32'),
(23, 1, 'Chat du 05/05/2026 00:41', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-05 00:41:44', '2026-05-04 23:41:44'),
(24, 1, 'Chat du 05/05/2026 00:42', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-05 00:42:33', '2026-05-04 23:42:33'),
(25, 1, 'Chat du 05/05/2026 00:46', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-05 00:46:07', '2026-05-04 23:46:25'),
(26, 1, 'Chat du 05/05/2026 00:47', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-05 00:47:01', '2026-05-04 23:47:01'),
(27, 1, 'Chat du 05/05/2026 00:47', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-05 00:47:35', '2026-05-04 23:49:40'),
(28, 1, 'Chat du 05/05/2026 00:52', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-05 00:52:58', '2026-05-04 23:52:58'),
(29, 1, 'Chat du 05/05/2026 10:35', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-05 10:35:20', '2026-05-05 09:36:14'),
(30, 1, 'Chat du 08/05/2026 00:20', 'actif', 'neutral', 0.00, NULL, NULL, NULL, '2026-05-08 00:20:21', '2026-05-07 23:20:21');

-- --------------------------------------------------------

--
-- Structure de la table `chat_intent_patterns`
--

CREATE TABLE `chat_intent_patterns` (
  `id_pattern` int(11) NOT NULL,
  `intent` varchar(50) NOT NULL,
  `pattern` varchar(255) NOT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`keywords`)),
  `categorie` varchar(100) DEFAULT NULL,
  `poids` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_intent_patterns`
--

INSERT INTO `chat_intent_patterns` (`id_pattern`, `intent`, `pattern`, `keywords`, `categorie`, `poids`) VALUES
(1, 'product_search', 'je cherche', '[\"cherche\", \"besoin\", \"avoir\", \"acheter\"]', 'search', 2),
(2, 'product_search', 'avez-vous', '[\"avez\", \"vous\", \"existe\", \"disponible\"]', 'search', 2),
(3, 'product_search', 'je veux', '[\"veux\", \"voudrais\", \"aimerais\", \"désire\"]', 'search', 2),
(4, 'diet_preference', 'vegan', '[\"vegan\", \"végétal\", \"sans viande\"]', 'preferences', 3),
(5, 'diet_preference', 'keto', '[\"keto\", \"cétogène\", \"faible carb\"]', 'preferences', 3),
(6, 'diet_preference', 'bio', '[\"bio\", \"organique\", \"naturel\"]', 'preferences', 2),
(7, 'diet_preference', 'sans gluten', '[\"gluten\", \"coeliaque\", \"glutenfree\"]', 'preferences', 3),
(8, 'allergies', 'allergie', '[\"allergie\", \"allergique\", \"intolérance\"]', 'health', 3),
(9, 'allergies', 'arachides', '[\"arachide\", \"cacahuète\", \"noix\", \"peanut\"]', 'allergies', 3),
(10, 'allergies', 'produits laitiers', '[\"lactose\", \"lait\", \"dairy\"]', 'allergies', 3),
(11, 'budget_constraint', 'pas cher', '[\"pas cher\", \"bon marché\", \"économique\", \"budget\"]', 'budget', 2),
(12, 'budget_constraint', 'haut de gamme', '[\"haut\", \"gamme\", \"premium\", \"luxe\"]', 'budget', 2),
(13, 'health_nutrition', 'calories', '[\"calorie\", \"énergie\", \"kcal\"]', 'health', 2),
(14, 'health_nutrition', 'protéines', '[\"protéine\", \"protéin\", \"muscle\"]', 'health', 2),
(15, 'health_nutrition', 'faible sucre', '[\"sucre\", \"diabète\", \"glucose\"]', 'health', 2),
(16, 'taste_preference', 'sucré', '[\"sucré\", \"sucre\", \"miel\", \"confiture\"]', 'preferences', 1),
(17, 'taste_preference', 'salé', '[\"salé\", \"salé\", \"sel\", \"savoureux\"]', 'preferences', 1),
(18, 'taste_preference', 'épicé', '[\"épicé\", \"piment\", \"harissa\", \"fort\"]', 'preferences', 1),
(19, 'category_request', 'fruits légumes', '[\"fruit\", \"légume\", \"légume\", \"frais\"]', 'category', 2),
(20, 'category_request', 'boissons', '[\"boisson\", \"jus\", \"café\", \"thé\"]', 'category', 2),
(21, 'help_request', 'aide', '[\"aide\", \"help\", \"aide\", \"comment\"]', 'support', 1),
(22, 'help_request', 'conseil', '[\"conseil\", \"suggestion\", \"recommande\"]', 'support', 1),
(46, 'diet_preference', 'régime vegan', '[\"vegan\",\"vegetarien\"]', 'Régime', 8),
(47, 'diet_preference', 'régime keto', '[\"keto\",\"cetogene\"]', 'Régime', 7),
(48, 'diet_preference', 'produits bio', '[\"bio\",\"organique\"]', 'Régime', 8),
(50, 'allergies', 'allergies', '[\"allergie\",\"allergique\"]', 'Santé', 9),
(52, 'allergies', 'lactose', '[\"lactose\"]', 'Allergies', 10),
(53, 'allergies', 'noix', '[\"noix\",\"amande\"]', 'Allergies', 8),
(54, 'budget_constraint', 'budget', '[\"pas cher\",\"economique\",\"petit budget\",\"bon marche\"]', 'Budget', 6),
(57, 'taste_preference', 'saveur épicée', '[\"\\u00e9pic\\u00e9\",\"piment\"]', 'Goût', 5);

-- --------------------------------------------------------

--
-- Structure de la table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id_message` int(11) NOT NULL,
  `id_conversation` int(11) NOT NULL,
  `type` enum('utilisateur','bot','système') DEFAULT 'utilisateur',
  `contenu` longtext NOT NULL,
  `intent` varchar(50) DEFAULT NULL,
  `entities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`entities`)),
  `sentiment` varchar(20) DEFAULT 'neutral',
  `confiance` decimal(3,2) DEFAULT 0.50,
  `date_message` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `chat_messages`
--

INSERT INTO `chat_messages` (`id_message`, `id_conversation`, `type`, `contenu`, `intent`, `entities`, `sentiment`, `confiance`, `date_message`) VALUES
(1, 1, 'utilisateur', 'Je cherche des produits bio', NULL, NULL, 'neutral', 0.50, '2026-05-04 12:09:33'),
(2, 2, 'utilisateur', 'bonjour', NULL, NULL, 'neutral', 0.50, '2026-05-04 12:09:40'),
(3, 3, 'utilisateur', 'Je cherche des produits bio', NULL, NULL, 'neutral', 0.50, '2026-05-04 12:10:25'),
(4, 4, 'utilisateur', 'bonjour', NULL, NULL, 'neutral', 0.50, '2026-05-04 12:11:00'),
(5, 5, 'utilisateur', 'bonjour', NULL, NULL, 'neutral', 0.50, '2026-05-04 12:11:24'),
(6, 6, 'utilisateur', 'bonjour', NULL, NULL, 'neutral', 0.50, '2026-05-04 12:11:32'),
(7, 7, 'utilisateur', 'Je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-04 12:12:38'),
(8, 8, 'utilisateur', 'Salut! Je cherche quelque chose', NULL, NULL, 'neutre', 0.50, '2026-05-04 12:13:19'),
(9, 9, 'utilisateur', 'Bonjour', NULL, NULL, 'neutre', 0.50, '2026-05-04 12:13:33'),
(10, 10, 'utilisateur', 'Bonjour', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:13:55'),
(11, 11, 'utilisateur', 'Bonjour, je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:14:09'),
(12, 12, 'utilisateur', 'Bonjour, je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:14:36'),
(13, 13, 'utilisateur', 'Bonjour, je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:14:51'),
(14, 14, 'utilisateur', 'Bonjour, je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:15:01'),
(15, 15, 'utilisateur', 'Bonjour, je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:15:24'),
(16, 16, 'utilisateur', 'Bonjour, je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:15:53'),
(17, 17, 'utilisateur', 'Bonjour, je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:16:06'),
(18, 18, 'utilisateur', 'bro', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:16:28'),
(19, 18, 'bot', 'Bonjour! 👋 Dites-moi ce que vous cherchez!', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:16:28'),
(20, 18, 'utilisateur', 'cava', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:16:33'),
(21, 18, 'bot', 'Bonjour! 👋 Dites-moi ce que vous cherchez!', NULL, NULL, 'neutre', 0.50, '2026-05-04 13:16:34'),
(22, 19, 'utilisateur', 'bonjour', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:00:27'),
(23, 19, 'bot', 'Bonjour! 👋 Dites-moi ce que vous cherchez!', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:00:27'),
(24, 19, 'utilisateur', 'donne moi des produits fraiche et energitique', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:00:42'),
(25, 19, 'bot', 'Bonjour! 👋 Dites-moi ce que vous cherchez!', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:00:42'),
(26, 20, 'utilisateur', 'salut', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:10:30'),
(27, 20, 'bot', 'Bonjour! 👋 Dites-moi ce que vous cherchez!', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:10:30'),
(28, 20, 'utilisateur', 'je veux produit energitique', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:10:42'),
(29, 20, 'bot', 'Bien sûr! 🛍️ Je vais chercher les produits correspondant à votre demande.', NULL, NULL, 'positif', 0.50, '2026-05-04 20:10:42'),
(30, 20, 'utilisateur', 'ok', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:10:48'),
(31, 20, 'bot', 'Bonjour! 👋 Dites-moi ce que vous cherchez!', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:10:48'),
(32, 20, 'utilisateur', 'je veux manger des patte donne moi recommandation', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:11:24'),
(33, 20, 'bot', 'Bien sûr! 🛍️ Je vais chercher les produits correspondant à votre demande.', NULL, NULL, 'positif', 0.50, '2026-05-04 20:11:24'),
(34, 20, 'utilisateur', 'donne moi', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:11:39'),
(35, 20, 'bot', 'Bonjour! 👋 Dites-moi ce que vous cherchez!', NULL, NULL, 'neutre', 0.50, '2026-05-04 20:11:39'),
(36, 21, 'utilisateur', 'je veux un jus', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:04:22'),
(37, 21, 'bot', 'Bien sûr! 🛍️ Je vais chercher les produits correspondant à votre demande.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:04:22'),
(38, 21, 'utilisateur', 'Comment utiliser le chat?', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:04:34'),
(39, 21, 'bot', 'Bonjour! 👋 Dites-moi ce que vous cherchez!', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:04:34'),
(40, 21, 'utilisateur', 'Je suis allergique aux cacahuètes', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:04:37'),
(41, 21, 'bot', 'C\'est important! 🏥 Je vais chercher des produits sûrs pour vous.', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:04:37'),
(42, 21, 'utilisateur', 'J\'aimerais des produits vegan', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:04:41'),
(43, 21, 'bot', 'Excellent! 💚 Je vais vous proposer des produits adaptés à votre régime.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:04:41'),
(44, 21, 'utilisateur', 'Je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:04:46'),
(45, 21, 'bot', 'Excellent! 💚 Je vais vous proposer des produits adaptés à votre régime.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:04:46'),
(46, 22, 'utilisateur', 'Je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:14:32'),
(47, 22, 'bot', 'Excellent! 💚 Je vais vous proposer des produits adaptés à votre régime.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:14:32'),
(48, 23, 'utilisateur', 'Je cherche des légumes bio', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:41:44'),
(49, 23, 'utilisateur', 'Des produits vegan pas chers', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:41:44'),
(50, 23, 'utilisateur', 'Fruits riches en protéines', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:41:44'),
(51, 23, 'utilisateur', 'Snacks moins de 3 euros', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:41:44'),
(52, 23, 'utilisateur', 'Je cherche des produits bio vegan pas chers', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:41:44'),
(53, 23, 'bot', 'Très bien! 💚 Je vais vous proposer nos meilleurs produits vegan.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:41:44'),
(54, 24, 'utilisateur', 'Je cherche des légumes bio', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:42:33'),
(55, 24, 'utilisateur', 'Des produits vegan pas chers', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:42:33'),
(56, 24, 'utilisateur', 'Fruits riches en protéines', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:42:33'),
(57, 24, 'utilisateur', 'Snacks moins de 3 euros', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:42:33'),
(58, 24, 'utilisateur', 'Je cherche des produits bio vegan pas chers', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:42:33'),
(59, 24, 'bot', 'Parfait! 👌 J\'ai trouvé exactement ce qu\'il faut pour vous.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:42:33'),
(60, 25, 'utilisateur', 'Je cherche des produits bio', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:46:07'),
(61, 25, 'bot', 'Très bien! 💚 Je vais vous proposer nos meilleurs produits bio.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:46:07'),
(62, 25, 'utilisateur', 'jus', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:46:25'),
(63, 25, 'bot', 'Parfait! 🛍️ Je cherche les jus disponibles dans notre catalogue.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:46:25'),
(64, 26, 'utilisateur', 'jus', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:47:01'),
(65, 26, 'bot', 'Parfait! 🛍️ Je cherche les jus disponibles dans notre catalogue.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:47:01'),
(66, 27, 'utilisateur', 'jus', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:47:35'),
(67, 27, 'bot', 'Parfait! 🛍️ Je cherche les jus disponibles dans notre catalogue.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:47:35'),
(68, 27, 'utilisateur', 'je veux jus et pain', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:49:40'),
(69, 27, 'bot', 'Parfait! 🛍️ Je cherche les pain, jus disponibles dans notre catalogue.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:49:40'),
(70, 28, 'utilisateur', 'je veux manger jus et pain', NULL, NULL, 'neutre', 0.50, '2026-05-05 00:52:58'),
(71, 28, 'bot', 'Parfait! 🛍️ Je cherche les pain, jus disponibles dans notre catalogue.', NULL, NULL, 'positif', 0.50, '2026-05-05 00:52:58'),
(72, 29, 'utilisateur', 'je veux manger jus et pain', NULL, NULL, 'neutre', 0.50, '2026-05-05 10:35:20'),
(73, 29, 'bot', 'Super ! 🔍 Je parcours notre sélection pour trouver exactement ce qu\'il vous faut.', NULL, NULL, 'positif', 0.50, '2026-05-05 10:35:20'),
(74, 29, 'utilisateur', 'yaought', NULL, NULL, 'neutre', 0.50, '2026-05-05 10:36:06'),
(75, 29, 'bot', 'Salut! 😊 Je suis là pour vous aider. Que puis-je faire pour vous?', NULL, NULL, 'neutre', 0.50, '2026-05-05 10:36:06'),
(76, 29, 'utilisateur', 'lait', NULL, NULL, 'neutre', 0.50, '2026-05-05 10:36:14'),
(77, 29, 'bot', 'Super ! 🔍 Je parcours notre sélection pour trouver exactement ce qu\'il vous faut.', NULL, NULL, 'positif', 0.50, '2026-05-05 10:36:14'),
(78, 30, 'utilisateur', 'JE VEUX JUS ET SNACKS', NULL, NULL, 'neutre', 0.50, '2026-05-08 00:20:21'),
(79, 30, 'bot', 'Parfait! 🛍️ Je cherche les jus, snack disponibles dans notre catalogue.', NULL, NULL, 'positif', 0.50, '2026-05-08 00:20:21');

-- --------------------------------------------------------

--
-- Structure de la table `chat_recommendations`
--

CREATE TABLE `chat_recommendations` (
  `id_recommendation` int(11) NOT NULL,
  `id_message` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `raison` varchar(200) DEFAULT NULL,
  `confiance_score` decimal(3,2) DEFAULT 0.50,
  `position_rank` int(11) DEFAULT 0,
  `clique` tinyint(1) DEFAULT 0,
  `ajoute_panier` tinyint(1) DEFAULT 0,
  `acheté` tinyint(1) DEFAULT 0,
  `date_recommendation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `id_commande` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_commande` datetime NOT NULL DEFAULT current_timestamp(),
  `statut` enum('en-cours','en-preparation','confirmee','livree','annulee') DEFAULT 'en-cours',
  `mode_livraison` enum('standard','express') NOT NULL DEFAULT 'standard',
  `date_livraison_souhaitee` date DEFAULT NULL,
  `adresse_livraison` text NOT NULL,
  `methode_paiement` enum('cash','carte') NOT NULL DEFAULT 'cash',
  `numero_carte` varchar(20) DEFAULT NULL,
  `nom_titulaire` varchar(100) DEFAULT NULL,
  `date_expiration` varchar(5) DEFAULT NULL,
  `cvv` varchar(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id_commande`, `id_produit`, `id_utilisateur`, `quantite`, `prix_total`, `date_commande`, `statut`, `mode_livraison`, `date_livraison_souhaitee`, `adresse_livraison`, `methode_paiement`, `numero_carte`, `nom_titulaire`, `date_expiration`, `cvv`) VALUES
(1, 1, 1, 2, 13.00, '2026-05-04 11:38:27', 'confirmee', 'standard', '2026-05-06', '12 Rue Bourguiba, Tunis', 'cash', NULL, NULL, NULL, NULL),
(2, 5, 2, 3, 3.60, '2026-05-04 11:38:27', 'en-cours', 'express', '2026-05-05', '45 Avenue 14 Janvier, Sfax', 'cash', NULL, NULL, NULL, NULL),
(3, 6, 3, 1, 9.50, '2026-05-04 11:38:27', 'livree', 'standard', '2026-05-07', '8 Rue Mondher Bey, Sousse', 'cash', NULL, NULL, NULL, NULL),
(4, 32, 4, 4, 66.00, '2026-05-04 11:38:27', 'en-preparation', 'express', '2026-05-05', '22 Boulevard Tahar Maamouri, Monastir', 'cash', NULL, NULL, NULL, NULL),
(5, 34, 5, 2, 44.00, '2026-05-04 11:38:27', 'confirmee', 'standard', '2026-05-08', '77 Avenue Habib Bourguiba, Bizerte', 'cash', NULL, NULL, NULL, NULL),
(6, 3, 22, 3, 13.50, '2026-05-04 22:39:40', 'confirmee', 'express', '2026-05-07', 'ben arous , El mourouj 1 , ibn jazar', 'carte', '', '', '', ''),
(7, 51, 22, 1, 1.50, '2026-05-04 23:03:51', 'en-cours', 'express', '2026-05-21', 'ben arous , El mourouj 1 , ibn jazar', 'cash', NULL, NULL, NULL, NULL),
(8, 51, 22, 1, 1.50, '2026-05-05 09:34:16', 'confirmee', 'standard', '2026-05-06', 'ben arous , El mourouj 1 , ibn jazar', 'carte', '', '', '', '');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id_produit` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `marque` varchar(120) NOT NULL,
  `code_barre` varchar(80) DEFAULT NULL,
  `categorie` varchar(120) DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL DEFAULT 0.00,
  `calories` decimal(10,2) DEFAULT 0.00,
  `proteines` decimal(10,2) DEFAULT 0.00,
  `glucides` decimal(10,2) DEFAULT 0.00,
  `lipides` decimal(10,2) DEFAULT 0.00,
  `nutriscore` char(1) DEFAULT 'C',
  `image` text DEFAULT NULL,
  `quantite_disponible` int(11) NOT NULL DEFAULT 0,
  `statut` enum('actif','inactif','attente') DEFAULT 'actif',
  `date_ajout` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id_produit`, `nom`, `marque`, `code_barre`, `categorie`, `prix`, `calories`, `proteines`, `glucides`, `lipides`, `nutriscore`, `image`, `quantite_disponible`, `statut`, `date_ajout`) VALUES
(1, 'Yaourt Nature Bio 500g', 'Tunisian Dairy', '3017620422003', 'Produits laitiers', 6.50, 95.00, 4.50, 12.00, 3.20, 'A', 'https://bonwithlove.com/en/image?FileId=608&preset=C_ZOOM', 25, 'actif', '2026-05-04 11:38:27'),
(2, 'Fromage Blanc Frais 400g', 'Ben Arous', '3017620422004', 'Produits laitiers', 8.50, 110.00, 14.00, 6.00, 4.50, 'A', 'https://www.vrai.fr/wp-content/uploads/2022/10/dsc09650-copie-scaled.jpg', 20, 'actif', '2026-05-04 11:38:27'),
(3, 'Lait Entier Frais 1L', 'Vitalait', '3017620422005', 'Produits laitiers', 4.50, 60.00, 3.20, 4.80, 3.50, 'A', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=800&q=80', 27, 'actif', '2026-05-04 11:38:27'),
(4, 'Fromage Emmental Tranches', 'Galbani', '3017620422006', 'Produits laitiers', 15.90, 380.00, 28.00, 0.70, 30.00, 'A', 'https://www.sodiaalprofessionnel.com/app/uploads/2017/02/emmental-sandwich2-copie.png', 15, 'actif', '2026-05-04 11:38:27'),
(5, 'Yaourt aux Fruits 125g', 'Sidi Saad', '3017620422007', 'Produits laitiers', 1.20, 120.00, 3.50, 18.00, 2.80, 'A', 'https://media.carrefour.fr/medias/63d6578d1de536e685001b1e20d136e9/p_1500x1500/3456770521103-0.jpg', 50, 'actif', '2026-05-04 11:38:27'),
(6, 'Jus Orange Frais 1L', 'Tunisian Citrus', '8437001234567', 'Boissons', 9.50, 44.00, 0.70, 9.80, 0.20, 'A', 'https://cloudtiktak.com/media/static/media/JUS_ORANGE_1_L_DELICE.webp', 40, 'actif', '2026-05-04 11:38:27'),
(7, 'Eau Minérale 1.5L', 'Safia', '8437001234568', 'Boissons', 1.50, 0.50, 0.90, 0.60, 0.02, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSxv_SFKv_-_rJgGy2RZXK0LnzKTau-R2M3sQ&s', 100, 'actif', '2026-05-04 11:38:27'),
(8, 'Jus Fraise Nectar 200ml', 'Kech', '8437001234569', 'Boissons', 2.50, 60.00, 0.50, 14.00, 0.20, 'A', 'https://cdn.shopify.com/s/files/1/0836/2698/3754/files/3556000330106-ambiance_TRAITE_fe9ff24b-485d-4445-aae5-de93da78368f.jpg?v=1749038319', 35, 'actif', '2026-05-04 11:38:27'),
(9, 'Thé Chamomille 20 Sachets', 'Aïda', '8437001234570', 'Boissons', 4.50, 2.00, 0.30, 0.30, 0.03, 'A', 'https://cdn.competec.ch/images2/4/8/6/368932684/368932684_xxl3.jpg', 22, 'actif', '2026-05-04 11:38:27'),
(10, 'Café Moulu Premium 250g', 'Caftan', '8437001234571', 'Cafe & The', 12.00, 0.01, 0.40, 0.10, 0.30, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSPCQYWMgp3rTsaDkWYXFa5PnBIjklLN5gX1A&s', 18, 'actif', '2026-05-04 11:38:27'),
(11, 'Pain Complet Frais 600g', 'Boulangerie Municipale', '6111245678901', 'Cereales & Pains', 3.50, 247.00, 9.00, 41.00, 3.50, 'A', 'https://images.unsplash.com/photo-1608198093002-ad4e005484ec?w=800&q=80', 45, 'actif', '2026-05-04 11:38:27'),
(12, 'Granola Miel Amandes 500g', 'Natura', '7613035678901', 'Cereales & Pains', 16.00, 410.00, 8.20, 62.00, 13.00, 'A', 'https://www.ambrosiae.fr/cdn/shop/files/ambrosiae0003_1_1080x.jpg?v=1718882168', 20, 'actif', '2026-05-04 11:38:27'),
(13, 'Pâtes Complètes 500g', 'Barilla', '8076809512345', 'Cereales & Pains', 7.50, 350.00, 12.50, 67.00, 2.50, 'A', 'https://www.houra.fr/_next/image?url=http%3A%2F%2Flocalhost%3A83%2FART-IMG-XL%2F49%2F67%2F3596710546749-2.jpg&w=3840&q=75', 30, 'actif', '2026-05-04 11:38:27'),
(14, 'Riz Complet Bio 1kg', 'Ali Bey', '6111245678902', 'Cereales & Pains', 8.50, 360.00, 7.50, 75.00, 3.00, 'A', 'https://www.lemasdesagriculteurs.fr/img/p/4/7/4/7/4747.jpg', 25, 'actif', '2026-05-04 11:38:27'),
(15, 'Couscous Traditionnel 500g', 'Couscous Ben', '6111245678903', 'Cereales & Pains', 5.50, 340.00, 12.00, 70.00, 1.50, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRjLc59mM09sX_3k-qY1hpLaAfrRCZJ12M2tg&s', 35, 'actif', '2026-05-04 11:38:27'),
(16, 'Biscuits Digestifs 400g', 'McVities', '7622210123456', 'Snacks & Biscuits', 8.90, 480.00, 6.00, 62.00, 20.00, 'C', 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=800&q=80', 28, 'actif', '2026-05-04 11:38:27'),
(17, 'Chips Nature 150g', 'Lay\'s', '5053990123456', 'Snacks & Biscuits', 4.50, 536.00, 5.50, 49.00, 34.00, 'D', 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=800&q=80', 40, 'actif', '2026-05-04 11:38:27'),
(18, 'Crackers Complets 200g', 'Tisano', '5053990123457', 'Snacks & Biscuits', 5.50, 420.00, 8.50, 56.00, 16.00, 'A', 'https://www.crich.it/public/prodotti/crackers/crackers_integrali_200g_big.jpg', 32, 'actif', '2026-05-04 11:38:27'),
(19, 'Pop-Corn Non Salé 100g', 'Maïs Tunisien', '5053990123458', 'Snacks & Biscuits', 3.50, 380.00, 10.00, 76.00, 5.50, 'A', 'https://image.migros.ch/d/mo-boxed/v-w-1000-h-1000/o-af-1-t.clr-fff/f189b1a2f911f55f40fe95fe9f6ede765c3274ff/m-classic-popcorn-leger-sale.jpg', 50, 'actif', '2026-05-04 11:38:27'),
(20, 'Thon Naturel 180g', 'Rio Mare', '8004030123456', 'Conserves', 11.50, 115.00, 25.00, 3.00, 1.20, 'A', 'https://cloudtiktak.com/media/static/media/Design_sans_titre_-_2026-04-25T124101.webp', 35, 'actif', '2026-05-04 11:38:27'),
(21, 'Tomates Pelées 400g', 'San Raso', '8004030123457', 'Conserves', 3.50, 18.00, 1.00, 3.50, 0.20, 'A', 'https://m.media-amazon.com/images/I/71omgAqfGzL._AC_UF350,350_QL80_.jpg', 45, 'actif', '2026-05-04 11:38:27'),
(22, 'Pois Chiches 400g', 'Ben Youssef', '8004030123458', 'Conserves', 4.00, 120.00, 8.50, 18.00, 2.00, 'A', 'https://cdn.greenweez.com/products/1LUCE0008/pois-chiches-400g_0.jpg', 40, 'actif', '2026-05-04 11:38:27'),
(23, 'Olives Noires Dénoyautées 350g', 'Zebar', '8004030123459', 'Conserves', 8.50, 115.00, 1.40, 0.60, 11.00, 'A', 'https://iod.keplrstatic.com/api/ar_1.25,g_north,c_fill/ar_1.5,w_800,q_70,c_fill,f_auto,dpr_auto/mon_marche/30845__OLIVES_MAROC_POT_37CL_NOIRES_DENOYAUTEES.jpg', 25, 'actif', '2026-05-04 11:38:27'),
(24, 'Compote Pomme Bio 400g', 'Fruit de Bonheur', '3608580123456', 'Fruits & Legumes', 6.50, 72.00, 0.30, 16.20, 0.10, 'A', 'https://m.media-amazon.com/images/I/61h2HE7AWtL.jpg', 30, 'actif', '2026-05-04 11:38:27'),
(25, 'Jus Pamplemousse Frais 200ml', 'Citrus Sud', '3608580123457', 'Fruits & Legumes', 3.50, 38.00, 0.60, 9.00, 0.10, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTRQsUY9Oox8Rzio4m3Pyz_bgIlHY1C7dOuHQ&s', 25, 'actif', '2026-05-04 11:38:27'),
(26, 'Filet Poulet Surgélé 500g', 'Agro-Meat', '8004030123460', 'Viandes & Poissons', 18.50, 165.00, 31.00, 66.00, 3.60, 'A', 'https://images.openfoodfacts.org/images/products/325/622/710/8280/front_fr.45.full.jpg', 20, 'actif', '2026-05-04 11:38:27'),
(27, 'Poisson Blanc Frais 400g', 'Pêche Côtière', '8004030123461', 'Viandes & Poissons', 22.00, 90.00, 20.00, 790.00, 1.00, 'A', 'https://static.thiriet.com/data/common_public/gallery_images/site/18756/18774/141514,filets_de_merlu_blanc_du_cap.jpg', 15, 'actif', '2026-05-04 11:38:27'),
(28, 'Légumes Surgelés Mélange 400g', 'Agrifresh', '8004030123462', 'Produits surgelés', 6.50, 35.00, 2.50, 6.00, 0.30, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT_vyNFyr1vxAP2iIA1cSGRiD6i94bWJKQk_Q&s', 28, 'actif', '2026-05-04 11:38:27'),
(29, 'Pizza Surgelée 400g', 'Mr Frost', '8004030123463', 'Produits surgelés', 10.50, 285.00, 12.00, 32.00, 12.00, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSTJqgIR1FtqZQY2bpRBCDCY4FpJJvLhobDvQ&s', 22, 'actif', '2026-05-04 11:38:27'),
(30, 'Chocolat Noir 70% 100g', 'Lindt', '7622210123457', 'Chocolat & Bonbons', 9.50, 530.00, 7.00, 46.00, 30.00, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcStzUJlrYghZfcH5SmZQzRGOaYCUHZEkmtH8w&s', 30, 'actif', '2026-05-04 11:38:27'),
(31, 'Bonbons Fruités 200g', 'Haribo', '7622210123458', 'Chocolat & Bonbons', 7.50, 340.00, 5.00, 77.00, 0.50, 'A', 'https://sc04.alicdn.com/kf/Ha6a17bd852d0486c83db866552ec9edde.jpg', 35, 'actif', '2026-05-04 11:38:27'),
(32, 'Miel Naturel 500g', 'Apiculture Tunisienne', '3608580123458', 'Miel & Confitures', 14.50, 304.00, 0.30, 82.00, 0.04, 'A', 'https://mesabeilles.fr/wp-content/uploads/2023/10/miel-d-ete-de-normandie.jpg', 20, 'actif', '2026-05-04 11:38:27'),
(33, 'Confiture Fraise 400g', 'Délices du Sud', '3608580123459', 'Miel & Confitures', 8.50, 272.00, 0.50, 66.00, 0.20, 'A', 'https://www.cevital-agro-industrie.com/wp-content/uploads/2025/12/confiture-fraiz-8.jpg', 25, 'actif', '2026-05-04 11:38:27'),
(34, 'Huile Olive Extra Vierge 500ml', 'Chemlal', '8004030123464', 'Huiles & Condiments', 22.00, 884.00, 0.20, 500.00, 100.00, 'B', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT5j7_-2pDxqhLSM_icAJuRJq-hAMhxhiiJFA&s', 15, 'actif', '2026-05-04 11:38:27'),
(35, 'Sauce Tomate Classique 200g', 'Deglet Noor', '8004030123465', 'Huiles & Condiments', 4.50, 25.00, 1.00, 5.00, 0.30, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSHO2JrEJ5AHsA1kH8ti10GnaS7nxK6h5kBvw&s', 35, 'actif', '2026-05-04 11:38:27'),
(36, 'Moutarde Française 200g', 'Pommery', '8004030123466', 'Huiles & Condiments', 5.50, 66.00, 3.00, 4.00, 4.00, 'A', 'https://champs60.com/cdn/shop/files/moutardededijon.heic?v=1741273464&width=1946', 28, 'actif', '2026-05-04 11:38:27'),
(37, 'Riz Basmati Bio 1kg', 'Nature & Saveur', '6111245678904', 'Produits bio', 12.50, 360.00, 7.00, 80.00, 0.50, 'A', 'https://www.green-shop.ch/cdn/shop/files/riz-basmati-himalaya-bio-1kg-rapunzel_efea789f-756e-4255-9d32-8197601daed0.jpg?v=1762176468&width=800', 18, 'actif', '2026-05-04 11:38:27'),
(38, 'Poudre Amande Bio 200g', 'Bio Santé', '6111245678905', 'Produits bio', 13.50, 579.00, 21.00, 21.00, 50.00, 'A', 'https://moulindesmoines.com/128169-large_default/poudre-d-amandes-blanche-bio.jpg', 12, 'actif', '2026-05-04 11:38:27'),
(39, 'Oeufs Fermiers 6 Pièces', 'Farm Fresh', '8004030123467', 'Oeufs & Produits frais', 6.00, 155.00, 13.00, 1.10, 11.00, 'A', 'https://www.loue.fr/storage/modelfiles/products/25/01/21/ee/6-lr-omega-3-tc-loue-3-251-320-000-813-profil.png', 40, 'actif', '2026-05-04 11:38:27'),
(40, 'Muesli Fruits Secs 500g', 'Cereals Plus', '6111245678906', 'Petit déjeuner', 10.50, 390.00, 9.00, 70.00, 8.00, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSjT5pyrLJYg4QbugryTOZn5Xq80_t7WFpZwQ&s', 22, 'actif', '2026-05-04 11:38:27'),
(41, 'Miel Eucalyptus 300g', 'Api Pure', '3608580123460', 'Petit déjeuner', 11.00, 304.00, 0.20, 82.00, 20.00, 'A', 'https://www.deyma.tn/193-thickbox_default/miel-multifleurs.jpg', 18, 'actif', '2026-05-04 11:38:27'),
(42, 'Harissa Traditionnelle 200g', 'Spice Master', '8004030123468', 'Sauces & Assaisonnements', 6.50, 140.00, 4.00, 12.00, 6.00, 'A', 'https://patisseriemasmoudi.fr/cdn/shop/products/Harissa-traditionnelle_grande.png?v=1748509202', 30, 'actif', '2026-05-04 11:38:27'),
(43, 'Sauce Soja 250ml', 'Tamari', '8004030123469', 'Sauces & Assaisonnements', 7.50, 65.00, 11.00, 5.00, 0.50, 'A', 'https://ben-yaghlane.com/cdn/shop/files/soja_sauce_1.png?v=1772020676', 25, 'actif', '2026-05-04 11:38:27'),
(44, 'Amandes Grillées Non Salées 200g', 'Nuts & Co', '8004030123470', 'Noix & Graines', 14.50, 579.00, 21.00, 21.00, 50.00, 'A', 'https://cdn.auchan.fr/media/P02000000000BILPRIMARY_2048x2048/B2CD/', 20, 'actif', '2026-05-04 11:38:27'),
(45, 'Graines Courge 150g', 'Seed Power', '8004030123471', 'Noix & Graines', 8.50, 541.00, 29.00, 20.00, 46.00, 'A', 'https://www.relaisbio.fr/12731-large_default/graines-de-courge-grillees-salees-150g-pepite.jpg', 24, 'actif', '2026-05-04 11:38:27'),
(46, 'Cacahuètes Grillées 250g', 'Peanut Joy', '8004030123472', 'Noix & Graines', 9.50, 567.00, 26.00, 16.00, 49.00, 'A', 'https://www.drivezeclerc.re/les-terrass/24837-large_default/cacahuetes-grilles-et-sales-tokapi-250gr.jpg', 28, 'actif', '2026-05-04 11:38:27'),
(47, 'Miel Tilleul 400g', 'Golden Hive', '3608580123461', 'Sucres & Miel', 13.00, 304.00, 0.40, 82.00, 50.00, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGCSALAj7-GCOyzixt6mi_UVaKRtKYR-QYpA&s', 16, 'actif', '2026-05-04 11:38:27'),
(48, 'Sucre Complet Bio 500g', 'Bio Sweet', '8004030123473', 'Sucres & Miel', 6.50, 387.00, 0.02, 97.00, 330.00, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTDALrb3FiUlfo4PyMKkKTCSdsBId9t8ifRng&s', 35, 'actif', '2026-05-04 11:38:27'),
(49, 'Yaourt Probiotique 500g', 'BioLive', '3017620422008', 'Produits fermentés', 7.50, 85.00, 4.00, 10.00, 2.50, 'A', 'https://www.biolaboratorium.com/cdn/shop/products/473ab0b1f19f649fe15b5bfdb0908c23.jpg?v=1673554335', 20, 'actif', '2026-05-04 11:38:27'),
(50, 'Kimchi Traditionnel 300g', 'Korea Fresh', '8004030123474', 'Produits fermentés', 10.50, 30.00, 2.00, 4.00, 1.20, 'A', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRhQhViEhZFr_xGjCnYnq448AdaZU2pxsowbQ&s', 15, 'actif', '2026-05-04 11:38:27'),
(51, 'Tropico', 'tropicooooo', '3324700057278', 'Boissons', 1.50, 110.30, 0.50, 7.80, 2.50, 'A', 'https://m.media-amazon.com/images/I/61AF9QeULCL._AC_UF350,350_QL80_.jpg', 1, 'actif', '2026-05-04 22:13:14');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id_avis`),
  ADD KEY `idx_produit` (`id_produit`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id_categorie`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Index pour la table `chat_analytics`
--
ALTER TABLE `chat_analytics`
  ADD PRIMARY KEY (`id_analytics`),
  ADD UNIQUE KEY `unique_date` (`date_jour`),
  ADD KEY `idx_analytics_period` (`date_jour`);

--
-- Index pour la table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id_conversation`),
  ADD KEY `idx_utilisateur` (`id_utilisateur`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_conversation_date` (`date_dernier_message`);

--
-- Index pour la table `chat_intent_patterns`
--
ALTER TABLE `chat_intent_patterns`
  ADD PRIMARY KEY (`id_pattern`),
  ADD UNIQUE KEY `unique_pattern` (`pattern`,`intent`),
  ADD KEY `idx_intent` (`intent`);

--
-- Index pour la table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `idx_conversation` (`id_conversation`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_intent` (`intent`),
  ADD KEY `idx_chat_date` (`date_message`);

--
-- Index pour la table `chat_recommendations`
--
ALTER TABLE `chat_recommendations`
  ADD PRIMARY KEY (`id_recommendation`),
  ADD KEY `fk_recommendation_message` (`id_message`),
  ADD KEY `idx_produit` (`id_produit`),
  ADD KEY `idx_ajoute_panier` (`ajoute_panier`),
  ADD KEY `idx_acheté` (`acheté`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id_commande`),
  ADD KEY `fk_commande_produit` (`id_produit`),
  ADD KEY `idx_commande_utilisateur` (`id_utilisateur`),
  ADD KEY `idx_commande_statut` (`statut`),
  ADD KEY `idx_commande_date` (`date_commande`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id_produit`),
  ADD KEY `idx_produit_categorie` (`categorie`),
  ADD KEY `idx_produit_statut` (`statut`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id_avis` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `chat_analytics`
--
ALTER TABLE `chat_analytics`
  MODIFY `id_analytics` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id_conversation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pour la table `chat_intent_patterns`
--
ALTER TABLE `chat_intent_patterns`
  MODIFY `id_pattern` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT pour la table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT pour la table `chat_recommendations`
--
ALTER TABLE `chat_recommendations`
  MODIFY `id_recommendation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `id_commande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_chat_conversation` FOREIGN KEY (`id_conversation`) REFERENCES `chat_conversations` (`id_conversation`) ON DELETE CASCADE;

--
-- Contraintes pour la table `chat_recommendations`
--
ALTER TABLE `chat_recommendations`
  ADD CONSTRAINT `fk_recommendation_message` FOREIGN KEY (`id_message`) REFERENCES `chat_messages` (`id_message`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_recommendation_produit` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id_produit`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `fk_commande_produit` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id_produit`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
