<?php
require 'config/config.php';
require 'includes/Database.php';

try {
    $db = new Database();
    $users = $db->select("SELECT email, vai_tro FROM nguoi_dung LIMIT 10");
    echo "--- USERS ---\n";
    foreach ($users as $user) {
        echo "Email: {$user['email']} | Role: {$user['vai_tro']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
