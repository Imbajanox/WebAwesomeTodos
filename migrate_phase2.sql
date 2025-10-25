-- =================================================================
-- Migration Script for Phase 2 - Core UX Improvements
-- =================================================================
-- This script adds description, due_date, and priority fields to
-- existing tasks tables. Run this if you already have the database
-- created from the previous version.
-- =================================================================

USE task_manager_wa;

-- Add description field
ALTER TABLE `tasks` 
ADD COLUMN `description` text DEFAULT NULL AFTER `title`;

-- Add due_date field with index
ALTER TABLE `tasks` 
ADD COLUMN `due_date` date DEFAULT NULL AFTER `description`,
ADD KEY `due_date` (`due_date`);

-- Add priority field with index
ALTER TABLE `tasks` 
ADD COLUMN `priority` enum('low', 'medium', 'high') DEFAULT 'medium' AFTER `due_date`,
ADD KEY `priority` (`priority`);
