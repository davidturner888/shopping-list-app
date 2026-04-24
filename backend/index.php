<?php
header('Content-Type: application/json');
require_once 'db.php';

// Create tables if they don't exist
$db->exec("
CREATE TABLE IF NOT EXISTS stores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    store_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    quantity INTEGER DEFAULT 1,
    checked INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
);
");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $db->query("SELECT * FROM stores");
    $stores = [];

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $stores[] = $row;
    }

    echo json_encode($stores);
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $name = $data['name'];

    $stmt = $db->prepare("INSERT INTO stores (name) VALUES (:name)");
    $stmt->bindValue(':name', $name);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Store added"]);
    } else {
        echo json_encode(["error" => "Store already exists"]);
    }
}

if ($method === 'DELETE') {
    parse_str($_SERVER['QUERY_STRING'], $query);
    $id = $query['id'];

    $stmt = $db->prepare("DELETE FROM stores WHERE id = :id");
    $stmt->bindValue(':id', $id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Store deleted"]);
    } else {
        echo json_encode(["error" => "Delete failed"]);
    }
}