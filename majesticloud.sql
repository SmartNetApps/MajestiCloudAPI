-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 07 août 2023 à 22:17
-- Version du serveur : 8.0.33-0ubuntu0.22.04.4
-- Version de PHP : 8.1.2-1ubuntu2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `majesticloud`
--

-- --------------------------------------------------------

--
-- Structure de la table `browser_commits`
--

CREATE TABLE `browser_commits` (
  `uuid` varchar(36) NOT NULL,
  `user_uuid` varchar(36) NOT NULL,
  `saved_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `uuid` varchar(36) NOT NULL,
  `name` text NOT NULL,
  `logo_url` text NOT NULL,
  `author_name` text NOT NULL,
  `webpage` text NOT NULL,
  `description` text NOT NULL,
  `callback_url` text NOT NULL,
  `secret_key` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `client_has_admin`
--

CREATE TABLE `client_has_admin` (
  `client_uuid` varchar(36) NOT NULL,
  `user_uuid` varchar(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `client_has_permission`
--

CREATE TABLE `client_has_permission` (
  `client_uuid` varchar(36) NOT NULL,
  `permission_id` int NOT NULL,
  `can_read` tinyint(1) NOT NULL,
  `can_write` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `oauth_authorization`
--

CREATE TABLE `oauth_authorization` (
  `authorization_key` varchar(128) NOT NULL,
  `user_uuid` varchar(36) NOT NULL,
  `client_uuid` varchar(36) NOT NULL,
  `pkce_code_verifier` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `permission`
--

CREATE TABLE `permission` (
  `id` int NOT NULL,
  `scope` varchar(128) NOT NULL,
  `user_friendly_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `session`
--

CREATE TABLE `session` (
  `uuid` varchar(36) NOT NULL,
  `token` varchar(128) NOT NULL,
  `user_uuid` varchar(36) NOT NULL,
  `client_uuid` varchar(36) NOT NULL,
  `device_name` text NOT NULL,
  `last_activity_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity_ip` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `uuid` varchar(36) NOT NULL,
  `password_hash` text NOT NULL,
  `name` text NOT NULL,
  `profile_picture_path` text,
  `primary_email` text NOT NULL,
  `primary_email_validation_key` varchar(128) DEFAULT NULL,
  `recovery_email` text,
  `recovery_email_validation_key` varchar(128) DEFAULT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `to_be_deleted_after` datetime DEFAULT NULL,
  `deleted_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `browser_commits`
--
ALTER TABLE `browser_commits`
  ADD PRIMARY KEY (`uuid`,`user_uuid`),
  ADD KEY `FK_browsercommits_user` (`user_uuid`);

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`uuid`);

--
-- Index pour la table `client_has_admin`
--
ALTER TABLE `client_has_admin`
  ADD PRIMARY KEY (`client_uuid`,`user_uuid`),
  ADD KEY `user_uuid` (`user_uuid`);

--
-- Index pour la table `client_has_permission`
--
ALTER TABLE `client_has_permission`
  ADD PRIMARY KEY (`permission_id`,`client_uuid`),
  ADD KEY `client_uuid` (`client_uuid`);

--
-- Index pour la table `oauth_authorization`
--
ALTER TABLE `oauth_authorization`
  ADD PRIMARY KEY (`authorization_key`),
  ADD KEY `client_uuid` (`client_uuid`),
  ADD KEY `user_uuid` (`user_uuid`);

--
-- Index pour la table `permission`
--
ALTER TABLE `permission`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`uuid`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uuid`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `permission`
--
ALTER TABLE `permission`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `browser_commits`
--
ALTER TABLE `browser_commits`
  ADD CONSTRAINT `FK_browsercommits_user` FOREIGN KEY (`user_uuid`) REFERENCES `user` (`uuid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `client_has_admin`
--
ALTER TABLE `client_has_admin`
  ADD CONSTRAINT `client_has_admin_ibfk_1` FOREIGN KEY (`client_uuid`) REFERENCES `client` (`uuid`),
  ADD CONSTRAINT `client_has_admin_ibfk_2` FOREIGN KEY (`user_uuid`) REFERENCES `user` (`uuid`);

--
-- Contraintes pour la table `client_has_permission`
--
ALTER TABLE `client_has_permission`
  ADD CONSTRAINT `client_has_permission_ibfk_1` FOREIGN KEY (`client_uuid`) REFERENCES `client` (`uuid`),
  ADD CONSTRAINT `client_has_permission_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`);

--
-- Contraintes pour la table `oauth_authorization`
--
ALTER TABLE `oauth_authorization`
  ADD CONSTRAINT `oauth_authorization_ibfk_1` FOREIGN KEY (`client_uuid`) REFERENCES `client` (`uuid`),
  ADD CONSTRAINT `oauth_authorization_ibfk_2` FOREIGN KEY (`user_uuid`) REFERENCES `user` (`uuid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
