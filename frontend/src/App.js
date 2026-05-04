import { useEffect, useState } from "react";

function App() {
  const [stores, setStores] = useState([]);
  const [selectedStore, setSelectedStore] = useState(null);
  const [items, setItems] = useState([]);
  const [newStore, setNewStore] = useState("");
  const [newItem, setNewItem] = useState("");

  const API = "http://localhost:5080/shopping-list-app/backend/index.php";

  // Load stores
  const loadStores = async () => {
    const res = await fetch(API);
    const data = await res.json();
    setStores(data);
  };

  // Load items
  const loadItems = async (storeId) => {
    const res = await fetch(`${API}?store_id=${storeId}`);
    const data = await res.json();
    setItems(data);
  };

  // Add store
  const addStore = async () => {
    await fetch(API, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name: newStore }),
    });
    setNewStore("");
    loadStores();
  };

  // Add item
  const addItem = async () => {
    await fetch(`${API}?store_id=${selectedStore}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name: newItem, quantity: 1 }),
    });
    setNewItem("");
    loadItems(selectedStore);
  };

  // Delete item
  const deleteItem = async (id) => {
    await fetch(`${API}?item_id=${id}`, { method: "DELETE" });
    loadItems(selectedStore);
  };

  // Toggle check
  const toggleItem = async (item) => {
    await fetch(`${API}?item_id=${item.id}`, {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ checked: item.checked ? 0 : 1 }),
    });
    loadItems(selectedStore);
  };

  useEffect(() => {
    loadStores();
  }, []);

  return (
    <div style={{ padding: "20px" }}>
      <h1>Shopping List App</h1>

      {/* Add Store */}
      <input
        placeholder="New store"
        value={newStore}
        onChange={(e) => setNewStore(e.target.value)}
      />
      <button onClick={addStore}>Add Store</button>

      {/* Store List */}
      <h2>Stores</h2>
      {stores.map((store) => (
        <div key={store.id}>
          <button
            onClick={() => {
              setSelectedStore(store.id);
              loadItems(store.id);
            }}
          >
            {store.name}
          </button>
        </div>
      ))}

      {/* Items */}
      {selectedStore && (
        <>
          <h2>Items</h2>

          <input
            placeholder="New item"
            value={newItem}
            onChange={(e) => setNewItem(e.target.value)}
          />
          <button onClick={addItem}>Add Item</button>

          {items.map((item) => (
            <div key={item.id}>
              <span
                onClick={() => toggleItem(item)}
                style={{
                  cursor: "pointer",
                  textDecoration: item.checked ? "line-through" : "none",
                }}
              >
                {item.name}
              </span>
              <button onClick={() => deleteItem(item.id)}>X</button>
            </div>
          ))}
        </>
      )}
    </div>
  );
}

export default App;