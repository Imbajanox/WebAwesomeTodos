-- =================================================================
-- Phase 3 Migration: Enhanced User Experience
-- =================================================================
-- This migration adds support for recurring tasks, custom sort order,
-- and smart filtering features to the WebAwesomeTodos application.
-- =================================================================

USE task_manager_wa;

-- =================================================================
-- 1. Add recurring tasks support to tasks table
-- =================================================================
ALTER TABLE `tasks`
ADD COLUMN `is_recurring` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether this is a recurring task template',
ADD COLUMN `recurrence_pattern` varchar(20) DEFAULT NULL COMMENT 'Recurrence pattern: daily, weekly, monthly, yearly',
ADD COLUMN `recurrence_interval` int DEFAULT 1 COMMENT 'Interval for recurrence (e.g., every 2 weeks)',
ADD COLUMN `recurrence_end_date` date DEFAULT NULL COMMENT 'Optional end date for recurrence',
ADD COLUMN `next_due_date` date DEFAULT NULL COMMENT 'Next due date for recurring task generation',
ADD COLUMN `sort_order` int NOT NULL DEFAULT 0 COMMENT 'Custom sort order for drag-and-drop reordering',
ADD COLUMN `parent_task_id` int UNSIGNED DEFAULT NULL COMMENT 'For task dependencies, links to parent task';

-- Add indexes for better performance on new columns
ALTER TABLE `tasks`
ADD KEY `is_recurring` (`is_recurring`),
ADD KEY `next_due_date` (`next_due_date`),
ADD KEY `sort_order` (`sort_order`),
ADD KEY `parent_task_id` (`parent_task_id`);

-- Add foreign key constraint for parent task relationship
ALTER TABLE `tasks`
ADD CONSTRAINT `tasks_parent_fk` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL;

-- =================================================================
-- 2. Add smart filter presets table
-- =================================================================
CREATE TABLE IF NOT EXISTS `filter_presets` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Preset name (e.g., "Today\'s Tasks", "High Priority")',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether this is a default preset',
  `filters` json DEFAULT NULL COMMENT 'Serialized filter configuration',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_preset_unique` (`user_id`, `name`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `filter_presets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- 3. Add full-text search index for task titles and descriptions
-- =================================================================
-- Note: Full-text search requires InnoDB and MySQL 5.6+ or MariaDB 10.0+
ALTER TABLE `tasks`
ADD FULLTEXT INDEX `task_search` (`title`, `description`);

-- =================================================================
-- 4. Insert default filter presets for all existing users
-- =================================================================
INSERT INTO `filter_presets` (`user_id`, `name`, `is_default`, `filters`)
SELECT
    u.id,
    'Today\'s Tasks',
    1,
    JSON_OBJECT(
        'due_date_filter', 'today',
        'status_filter', 'open',
        'priority_filter', 'all',
        'tag_filter', 'all'
    )
FROM `users` u
WHERE NOT EXISTS (
    SELECT 1 FROM `filter_presets` fp
    WHERE fp.user_id = u.id AND fp.name = 'Today\'s Tasks'
);

INSERT INTO `filter_presets` (`user_id`, `name`, `is_default`, `filters`)
SELECT
    u.id,
    'High Priority',
    1,
    JSON_OBJECT(
        'due_date_filter', 'all',
        'status_filter', 'open',
        'priority_filter', 'high',
        'tag_filter', 'all'
    )
FROM `users` u
WHERE NOT EXISTS (
    SELECT 1 FROM `filter_presets` fp
    WHERE fp.user_id = u.id AND fp.name = 'High Priority'
);

INSERT INTO `filter_presets` (`user_id`, `name`, `is_default`, `filters`)
SELECT
    u.id,
    'This Week',
    1,
    JSON_OBJECT(
        'due_date_filter', 'week',
        'status_filter', 'open',
        'priority_filter', 'all',
        'tag_filter', 'all'
    )
FROM `users` u
WHERE NOT EXISTS (
    SELECT 1 FROM `filter_presets` fp
    WHERE fp.user_id = u.id AND fp.name = 'This Week'
);

-- =================================================================
-- 5. Update existing tasks with sort order based on creation date
-- =================================================================
SET @row_number = 0;
UPDATE `tasks`
SET `sort_order` = (@row_number := @row_number + 1)
WHERE `is_completed` = 0
ORDER BY `created_at` ASC;

-- Update completed tasks with higher sort order values
SET @row_number = (SELECT MAX(sort_order) FROM `tasks` WHERE `is_completed` = 0);
UPDATE `tasks`
SET `sort_order` = (@row_number := @row_number + 1)
WHERE `is_completed` = 1
ORDER BY `created_at` ASC;

-- =================================================================
-- Migration Complete!
-- =================================================================
-- The database now supports:
-- - Recurring tasks with flexible patterns
-- - Drag-and-drop reordering via sort_order
-- - Task dependencies via parent_task_id
-- - Smart filter presets
-- - Full-text search capabilities
-- =================================================================