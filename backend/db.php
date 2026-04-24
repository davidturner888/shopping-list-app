<?php
$db = new SQLite3(__DIR__ . '/db.sqlite');

if (!$db) {
    die("Connection failed");
}
?>