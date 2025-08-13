-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 14 août 2025 à 00:10
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ticket_system`
--

-- --------------------------------------------------------

--
-- Structure de la table `commentaires`
--

CREATE TABLE `commentaires` (
  `id` int(11) NOT NULL,
  `id_post` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `commentaire` text NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commentaires`
--

INSERT INTO `commentaires` (`id`, `id_post`, `id_utilisateur`, `commentaire`, `date_creation`) VALUES
(1, 1, 2, 'hhhhh', '2025-08-09 17:58:54'),
(2, 3, 2, 'ghjhk', '2025-08-09 19:14:18'),
(3, 4, 2, 'FIOH', '2025-08-09 19:43:01'),
(4, 4, 2, 'JHJH', '2025-08-09 19:48:53'),
(5, 4, 2, 'JHJH', '2025-08-09 19:48:53'),
(6, 4, 2, 'VJVJHBHK', '2025-08-09 19:49:45'),
(7, 4, 2, 'VJVJHBHK', '2025-08-09 19:49:45'),
(8, 3, 2, 'bhdb', '2025-08-09 20:11:34'),
(9, 3, 2, 'bhdb', '2025-08-09 20:11:34'),
(10, 5, 2, 'hhhhhhhh', '2025-08-09 20:46:44'),
(14, 5, 14, 'GG', '2025-08-10 15:28:11'),
(15, 7, 2, 'GG', '2025-08-10 16:59:30'),
(17, 7, 14, 'GG', '2025-08-10 17:03:40'),
(18, 7, 14, 'CC', '2025-08-13 21:53:24');

-- --------------------------------------------------------

--
-- Structure de la table `media_posts`
--

CREATE TABLE `media_posts` (
  `id` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `text_content` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `video_embed` text DEFAULT NULL,
  `link_url` varchar(2048) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `media_posts`
--

INSERT INTO `media_posts` (`id`, `id_admin`, `titre`, `text_content`, `image_path`, `video_embed`, `link_url`, `date_creation`) VALUES
(1, 2, 'test', 'abaibibfzeb', NULL, NULL, NULL, '2025-08-09 17:58:23'),
(2, 2, 'img', 'uploads/68978c828b513-atcbsh.png', NULL, NULL, NULL, '2025-08-09 17:59:30'),
(3, 2, 'efb', 'jkrjk', NULL, NULL, NULL, '2025-08-09 18:46:35'),
(4, 2, 'JEF', 'HERK', 'uploads/post-6897a4b33ac5a-atcbsh.png', '', 'https://aistudio.google.com/prompts/19-SXsu_Vbxb1OqyGI6lt5shv-4D7L29F', '2025-08-09 19:42:43'),
(5, 2, 'fjz', 'rnern', NULL, '', '', '2025-08-09 20:46:26'),
(7, 2, 'WILLSON', 'TSWIRA DIAL WILLSON', 'uploads/post-6898cfde4f0bf-WLLSON.jpg', '', '', '2025-08-10 16:59:10');

-- --------------------------------------------------------

--
-- Structure de la table `pending_users`
--

CREATE TABLE `pending_users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pending_users`
--

INSERT INTO `pending_users` (`id`, `name`, `email`, `password_hash`, `token`, `created_at`, `verified`) VALUES
(5, 'casawy', 'adammoufrije0@gmail.com', '$2y$10$7EAzXFr.H5tQWZ6IXGd.1em6ATtXSFd2Hvl8YSJxGcTKtmtIKrgP2', 'ea4840818c8399847bda8a383cd958e0296cc0814d522b62c88ceda5ca5b6dcc', '2025-08-10 17:39:00', 0),
(7, 'MOUF', 'anassmouf@gmail.com', '$2y$10$oR1kBgejqb0UHANOhcJFS.8sJAqldnsWhg8NGchsSC4FzN0nDx2vG', 'b5576d1a01a43cdd420a78b3ec27f2a0499cd2c109b8abbd968b721c34cbd067', '2025-08-10 19:00:52', 0);

-- --------------------------------------------------------

--
-- Structure de la table `reactions`
--

CREATE TABLE `reactions` (
  `id` int(11) NOT NULL,
  `id_post` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `emoji` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reactions`
--

INSERT INTO `reactions` (`id`, `id_post`, `id_utilisateur`, `emoji`) VALUES
(1, 1, 2, '????'),
(8, 2, 2, '👏'),
(28, 4, 2, '😢'),
(29, 3, 2, '❤️'),
(30, 5, 2, '😂'),
(33, 5, 14, '😮'),
(34, 7, 2, '😂'),
(37, 7, 14, '😮');

-- --------------------------------------------------------

--
-- Structure de la table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `reponse_admin` text DEFAULT NULL,
  `statut` enum('Ouvert','En attente','Fermé') NOT NULL DEFAULT 'Ouvert',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tickets`
--

INSERT INTO `tickets` (`id`, `id_utilisateur`, `titre`, `message`, `reponse_admin`, `statut`, `date_creation`) VALUES
(10, 14, 'KJHSKDB', 'RGNEJQ', 'fix', 'Fermé', '2025-08-10 15:36:56');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `profile_pic` varchar(255) NOT NULL DEFAULT 'uploads/default_avatar.png',
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('utilisateur','admin') NOT NULL DEFAULT 'utilisateur',
  `profile_image` varchar(255) DEFAULT 'img/profil.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `username`, `profile_pic`, `email`, `mot_de_passe`, `role`, `profile_image`) VALUES
(2, 'testeur', 'uploads/default_avatar.png', 'admin@admin.com', '$2y$10$33r5jPbrxIOR6mD3T99xXeXpsxcy1u.JRi/IyViFbcML4WAOanzK2', 'admin', 'uploads/2_1754845214_IMG-MORE8.png'),
(14, 'ATC', 'uploads/default_avatar.png', 'atlanticmarocrp@gmail.com', '$2y$10$IOgRIoSFP/VQYXY.gwWm6utI8xykHSfrYV.n5l29UpCQDITdGT02u', 'utilisateur', 'uploads/14_1754839709_CHEAT.jpg');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_post` (`id_post`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `media_posts`
--
ALTER TABLE `media_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Index pour la table `pending_users`
--
ALTER TABLE `pending_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Index pour la table `reactions`
--
ALTER TABLE `reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reaction_unique` (`id_post`,`id_utilisateur`),
  ADD KEY `id_post` (`id_post`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commentaires`
--
ALTER TABLE `commentaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `media_posts`
--
ALTER TABLE `media_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `pending_users`
--
ALTER TABLE `pending_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `reactions`
--
ALTER TABLE `reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT pour la table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD CONSTRAINT `commentaires_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `media_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commentaires_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `media_posts`
--
ALTER TABLE `media_posts`
  ADD CONSTRAINT `media_posts_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reactions`
--
ALTER TABLE `reactions`
  ADD CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `media_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reactions_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
