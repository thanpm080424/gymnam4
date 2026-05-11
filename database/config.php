<?php
/**
 * Database Configuration - LEGACY SUPPORT
 * For backward compatibility, use config/config.php and includes/Database.php instead
 */

// Load central configuration
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Database.php';

// Legacy static method support (for old code compatibility)
if (!class_exists('\Database')) {
    class LegacyDatabase {
        private static $connection = null;

        public static function getConnection() {
            if (self::$connection === null) {
                self::$connection = new Database();
            }
            return self::$connection;
        }
    }
}
