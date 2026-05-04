import { useEffect, useState } from "react";

function App() {
  const [stores, setStores] = useState([]);
  const [selectedStore, setSelectedStore] = useState(null);
  const [items, setItems] = useState([]);
  const [newStore, setNewStore] = useState("");
  const [newItem, setNewItem] = useState("");

  const API = "http://localhost:5080/shopping-list-app/backend/index.php";

  const loadStores = async () => {
    const res = await fetch(API);
    const data = await res.json();
    setStores(data);
  };

  const loadItems = async (storeId) => {
    const res = await fetch(`${API}?store_id=${storeId}`);
    const data = await res.json();
    setItems(data);
  };

  const addStore = async () => {
    await fetch(API, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name: newStore }),
    });
    setNewStore("");
    loadStores();
  };

  const addItem = async () => {
    await fetch(`${API}?store_id=${selectedStore}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name: newItem, quantity: 1 }),
    });
    setNewItem("");
    loadItems(selectedStore);
  };

  const deleteItem = async (id) => {
    await fetch(`${API}?item_id=${id}`, { method: "DELETE" });
    loadItems(selectedStore);
  };

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
    <div style={{ padding: "20px", fontFamily: "Arial" }}>
      <h1>Shopping List App</h1>

      <input
        placeholder="New store"
        value={newStore}
        onChange={(e) => setNewStore(e.target.value)}
      />
      <button onClick={addStore}>Add Store</button>

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
              <button onClick={() => deleteItem(item.id)}>Delete</button>
            </div>
          ))}
        </>
      )}
    </div>
  );
}

export default App;