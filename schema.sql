-- =================================================================
-- Database Schema for WebAwesomeTodos
-- =================================================================
-- This file contains the complete database structure needed for
-- the WebAwesomeTodos application with tag support.
-- =================================================================

-- Create database (if needed)
CREATE DATABASE IF NOT EXISTS task_manager_wa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE task_manager_wa;

-- =================================================================
-- 1. Users Table
-- =================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- 2. Tasks Table
-- =================================================================
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `priority` enum('low', 'medium', 'high') DEFAULT 'medium',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` int UNSIGNED NOT NULL,
  -- Phase 3: Enhanced User Experience Features
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether this is a recurring task template',
  `recurrence_pattern` varchar(20) DEFAULT NULL COMMENT 'Recurrence pattern: daily, weekly, monthly, yearly',
  `recurrence_interval` int DEFAULT 1 COMMENT 'Interval for recurrence',
  `recurrence_end_date` date DEFAULT NULL COMMENT 'Optional end date for recurrence',
  `next_due_date` date DEFAULT NULL COMMENT 'Next due date for recurring task generation',
  `sort_order` int NOT NULL DEFAULT 0 COMMENT 'Custom sort order for drag-and-drop reordering',
  `parent_task_id` int UNSIGNED DEFAULT NULL COMMENT 'For task dependencies',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `due_date` (`due_date`),
  KEY `priority` (`priority`),
  KEY `is_recurring` (`is_recurring`),
  KEY `next_due_date` (`next_due_date`),
  KEY `sort_order` (`sort_order`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_parent_fk` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- 6. Full-Text Search Index (Phase 3)
-- =================================================================
-- Adds full-text search capability for faster text searches
-- =================================================================
CREATE FULLTEXT INDEX `task_search` (`title`, `description`);

-- =================================================================
-- 3. Tags Table
-- =================================================================
-- Stores unique tag names per user. Each user can create their own
-- set of tags that can be attached to tasks.
-- =================================================================
CREATE TABLE IF NOT EXISTS `tags` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_tag_unique` (`user_id`, `name`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- 4. Task_Tags Junction Table (Many-to-Many)
-- =================================================================
-- Links tasks to tags, allowing each task to have multiple tags
-- and each tag to be used by multiple tasks.
-- =================================================================
CREATE TABLE IF NOT EXISTS `task_tags` (
  `task_id` int UNSIGNED NOT NULL,
  `tag_id` int UNSIGNED NOT NULL,
  PRIMARY KEY (`task_id`, `tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `task_tags_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- =================================================================
-- 6. Full-Text Search Index (Phase 3)
-- =================================================================
-- Adds full-text search capability for faster text searches
-- =================================================================
CREATE FULLTEXT INDEX `task_search` (`title`, `description`);