<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
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

/*
========================
GET ITEMS FOR STORE
========================
*/
if ($method === 'GET' && isset($_GET['store_id'])) {
    $store_id = $_GET['store_id'];

    $stmt = $db->prepare("SELECT * FROM items WHERE store_id = :store_id");
    $stmt->bindValue(':store_id', $store_id);

    $result = $stmt->execute();
    $items = [];

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $items[] = $row;
    }

    echo json_encode($items);
}

/*
========================
GET ALL STORES
========================
*/
elseif ($method === 'GET') {
    $result = $db->query("SELECT * FROM stores");
    $stores = [];

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $stores[] = $row;
    }

    echo json_encode($stores);
}

/*
========================
ADD ITEM TO STORE
========================
*/
elseif ($method === 'POST' && isset($_GET['store_id'])) {
    $store_id = $_GET['store_id'];
    $data = json_decode(file_get_contents("php://input"), true);

    $name = $data['name'];
    $quantity = $data['quantity'] ?? 1;

    $stmt = $db->prepare("
        INSERT INTO items (store_id, name, quantity)
        VALUES (:store_id, :name, :quantity)
    ");

    $stmt->bindValue(':store_id', $store_id);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':quantity', $quantity);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Item added"]);
    } else {
        echo json_encode(["error" => "Failed to add item"]);
    }
}

/*
========================
CREATE STORE
========================
*/
elseif ($method === 'POST') {
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


// DELETE item
elseif ($method === 'DELETE' && isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];

    $stmt = $db->prepare("DELETE FROM items WHERE id = :id");
    $stmt->bindValue(':id', $item_id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Item deleted"]);
    } else {
        echo json_encode(["error" => "Delete failed"]);
    }
}


// DELETE store
elseif ($method === 'DELETE' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $db->prepare("DELETE FROM stores WHERE id = :id");
    $stmt->bindValue(':id', $id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Store deleted"]);
    } else {
        echo json_encode(["error" => "Delete failed"]);
    }
}

// UPDATE item (check/uncheck or edit)
elseif ($method === 'PUT' && isset($_GET['item_id'])) {
    $item_id = $_GET['item_id'];
    $data = json_decode(file_get_contents("php://input"), true);

    $checked = $data['checked'] ?? 0;
    $name = $data['name'] ?? null;
    $quantity = $data['quantity'] ?? null;

    $stmt = $db->prepare("
        UPDATE items 
        SET checked = :checked,
            name = COALESCE(:name, name),
            quantity = COALESCE(:quantity, quantity)
        WHERE id = :id
    ");

    $stmt->bindValue(':checked', $checked);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':quantity', $quantity);
    $stmt->bindValue(':id', $item_id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Item updated"]);
    } else {
        echo json_encode(["error" => "Update failed"]);
    }
}