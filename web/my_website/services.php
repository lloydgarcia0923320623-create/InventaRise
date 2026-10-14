<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>InventaRise Dashboard</title>
  <link rel="stylesheet" href="/css/services.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="navbar">
    <div class="nav_left">
      <img src="images/Logo.png" alt="Logo" class="logo_images" />
      <h1 class="nav_title">InventaRise</h1>
    </div>
    <div class="nav_right">
      <button onclick="location.href='home.php'" class="Home_btn">Home</button>
      <button onclick="location.href='LogIn.php?logout=1'" class="logout_btn">Logout</button>
    </div>
  </div>

  <div class="sidebar">
    <h2 class="sidebar_title">Main Menu</h2>
    <div class="sidebar_buttons">
      <button class="side_btn" data-page="services_content.php">Inventory Management</button>
      <button class="side_btn" data-page="supplier_management.php">Manage Suppliers</button>
      <button class="side_btn" data-page="report_analytics_content.php">Report Analytics (Table)</button>
      <button class="side_btn" data-page="track_delivery.php">Track Delivery</button>
      <div class="dropdown_section">
        <button class="side_btn dropdown_toggle">Delivery ▼</button>
        <div class="dropdown_content">
          <button class="sub_btn" data-page="schedule_delivery_form.php">Schedule Delivery</button>
          <button class="sub_btn" data-page="report_delivery_status.php">Report Delivery Status</button>
        </div>
      </div>
    </div>
  </div>

  <div class="main_content">
    <h2>Welcome back, <span class="username_highlight">User!</span></h2>
    <p>Access your management tools using the menu on the left.</p>

    <div style="display:flex; justify-content:flex-end; margin: 10px 0 18px;">
      <a href="helpers/export_inventory_csv.php" style="display:inline-block; background:#00c2ff; color:#051722; text-decoration:none; padding:10px 16px; border-radius:10px; font-weight:700;">Export Inventory CSV</a>
    </div>

    <script>
      const params = new URLSearchParams(window.location.search);
      if (params.get('view') === 'analytics') {
        window.addEventListener('DOMContentLoaded', () => {
          setTimeout(() => {
            const analyticsBtn = document.querySelector('[data-page="report_analytics_content.php"]');
            if (analyticsBtn) analyticsBtn.click();
          }, 120);
        });
      }
    </script>

    <div class="dashboard_cards">
      <div class="card">
        <h3>Total Items</h3>
        <p id="stat-total-items">0</p>
      </div>
      <div class="card">
        <h3>Low Stock</h3>
        <p id="stat-low-stock">0</p>
      </div>
      <div class="card">
        <h3>Pending Orders</h3>
        <p id="stat-pending-orders">0</p>
      </div>
    </div>


    <div class="Tables">
      <div id="delivered-items-container"></div>
      <div id="lowstock">
        <h4>Low Stock Items</h4>
      </div>
      <div class="table"></div>
    </div>
  </div>

  <div id="cancel_notification" class="cancel_notification"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const mainContent = document.querySelector(".main_content");
  const cancelNotification = document.getElementById("cancel_notification");

  // ========== UTILITIES ==========
    const escapeHtml = text => {
      const div = document.createElement("div");
      div.textContent = text;
      return div.innerHTML;
    };

    const showCancelNotification = (message, type = "success") => {
      cancelNotification.textContent = message;
      cancelNotification.className = `cancel_notification show ${type}`;
      setTimeout(() => cancelNotification.classList.remove("show"), 2500);
    };

  // ========== CANCEL DELIVERY ==========
      const attachCancelButtons = () => {
        document.querySelectorAll(".cancel_btn").forEach(btn => {
          btn.addEventListener("click", () => {
            const id = btn.closest("tr")?.dataset.id;
            if (!id || !confirm("Are you sure you want to cancel this delivery?")) return;

            const formData = new FormData();
            formData.append("delivery_id", id);

            fetch("helpers/delivery_cancel.php", { method: "POST", body: formData })
              .then(res => res.json())
              .then(data => {
                showCancelNotification(data.message, data.status === "success" ? "success" : "error");
                if (data.status === "success") btn.closest("tr").remove();
              })
              .catch(() => showCancelNotification("⚠️ Failed to cancel delivery.", "error"));
          });
        });
      };

  // ========== PAGE LOADER ==========
      const loadPageWithFade = url => {
        mainContent.classList.add("fade-out");
        setTimeout(() => {
          fetch(url, { cache: "no-cache" })
            .then(res => res.ok ? res.text() : Promise.reject(`Failed to load ${url}`))
            .then(html => {
              mainContent.innerHTML = html;

              mainContent.querySelectorAll("script").forEach(script => {
                const replacement = document.createElement("script");
                Array.from(script.attributes).forEach(attr => {
                  replacement.setAttribute(attr.name, attr.value);
                });
                replacement.textContent = script.textContent;
                script.replaceWith(replacement);
              });

              mainContent.classList.remove("fade-out");
              mainContent.classList.add("fade-in");
              setTimeout(() => mainContent.classList.remove("fade-in"), 500);

              if (url.includes("report_delivery_status.php")) attachDeliveryStatusHandler();
              if (url.includes("services_content.php")) loadInventoryStats();
              if (url.includes("track_delivery.php")) attachCancelButtons();
            })
            .catch(err => mainContent.innerHTML = `<p style='color:red;'>❌ ${err}</p>`);
        }, 400);
      };

  // ========== DELIVERY FORM HANDLER ==========
      const attachDeliveryFormHandler = () => {
        const form = mainContent.querySelector("#deliveryForm");
        const messageBox = mainContent.querySelector("#responseMessage");
        if (!form) return;

        form.addEventListener("submit", e => {
          e.preventDefault();
          fetch("schedule_delivery_form.php", { method: "POST", body: new FormData(form) })
            .then(res => res.json())
            .then(data => {
              messageBox.textContent = data.message;
              messageBox.className = `response_message ${data.status}`;
              if (data.status === "success") form.reset();
            });
        });
      };

  // ========== DELIVERY STATUS HANDLER ==========
      const attachDeliveryStatusHandler = () => {
        const form = mainContent.querySelector("#updateStatusForm");
        const messageBox = mainContent.querySelector("#responseMessage");
        const table = mainContent.querySelector("#deliveryTable");
        if (!form) return;

        form.addEventListener("submit", e => {
          e.preventDefault();
          fetch("report_delivery_status.php", { method: "POST", body: new FormData(form) })
            .then(res => res.json())
            .then(data => {
              messageBox.textContent = data.message;
              messageBox.className = `response_message ${data.status}`;
              if (data.status === "success") {
                const id = form.delivery_id.value.trim();
                const row = table?.querySelector(`tr[data-id='${id}']`);
                if (row) {
                  row.cells[5].textContent = form.status.options[form.status.selectedIndex].text;
                  row.style.backgroundColor = "#d4edda";
                  setTimeout(() => row.style.backgroundColor = "", 800);
                }
                form.reset();
                loadInventoryStats();
              }
            });
        });

        table?.querySelectorAll("tbody tr").forEach(row => {
          row.addEventListener("click", () => {
            form.delivery_id.value = row.dataset.id;
            [...form.status.options].forEach(opt => {
              if (opt.text === row.cells[5].textContent.trim()) form.status.value = opt.value;
            });
          });
        });
      };

  // ========== INVENTORY STATS ==========
        const loadInventoryStats = () => {
          fetch("fetcher.php?rand=" + Date.now())
            .then(res => res.json())
            .then(data => {
              if (!data || data.status !== "success") return;
              const stats = data.data || {};

              document.getElementById("stat-total-items").textContent = stats.total_items ?? 0;
              document.getElementById("stat-low-stock").textContent = stats.low_stock ?? 0;
              document.getElementById("stat-pending-orders").textContent = stats.pending_orders ?? 0;

              const container = document.getElementById("delivered-items-container");
              if (!container) return;
              container.innerHTML = "";

              // Delivered Items
                  const deliveredTitle = document.createElement("div");
                  deliveredTitle.style.display = "flex";
                  deliveredTitle.style.alignItems = "center";
                  deliveredTitle.style.justifyContent = "space-between";
                  
                  deliveredTitle.innerHTML = `
                    <h4>Delivered Items</h4>
                  `;
                  container.appendChild(deliveredTitle);

              // Delivered Items
              (stats.delivered_list || []).forEach(item => {
                const box = document.createElement("div");
                box.className = "delivered-item-box";
                box.innerHTML = `
                  <div class="delivered-item-header">
                    <h4>${escapeHtml(item.item)}</h4>
                    <div class="delivered-controls">
                      <span class="delivered-qty">Qty: ${item.quantity}</span>
                      <button class="add-to-inventory-btn" data-item="${escapeHtml(item.item)}" data-qty="${item.quantity}">➕ Add to Inventory</button>
                    </div>
                  </div>`;
                container.appendChild(box);
              });

              attachAddToInventoryButtons();

              // Inventory Items
                  const inventoryTitle = document.createElement("div");
                  inventoryTitle.style.display = "flex";
                  inventoryTitle.style.alignItems = "center";
                  inventoryTitle.style.justifyContent = "space-between";
                  inventoryTitle.style.margin = "40px 0 10px 0";
                  inventoryTitle.innerHTML = `
                    <h4>Inventory Items</h4>
                  `;
                  container.appendChild(inventoryTitle);

              (stats.inventory_list || []).forEach(item => {
                const box = document.createElement("div");
                box.className = "delivered-item-box";
                box.innerHTML = `
                  <div class="delivered-item-header">
                    <h4>${escapeHtml(item.item)}</h4>
                    <div class="delivered-controls">
                      <span class="delivered-qty">Qty: ${item.total_quantity}</span>
                      <input type="number" class="update-input" placeholder="New qty" min="1">
                      <button class="update-btn" data-item="${escapeHtml(item.item)}">Update</button>
                      <button class="delete-btn" data-item="${escapeHtml(item.item)}"
                        style="background:#dc3545;color:#fff;border:none;padding:5px 10px;border-radius:5px;margin-left:8px;cursor:pointer;">🗑</button>
                    </div>
                  </div>`;
                container.appendChild(box);
              });
              attachQuantityUpdateButtons();
              attachDeleteButtons();
              moveLowStockOnLoad();
              updateLowStockCard();
            });
        };

    // ========== ADD TO INVENTORY ==========
          const attachAddToInventoryButtons = () => {
            document.querySelectorAll(".add-to-inventory-btn").forEach(btn => {
              btn.addEventListener("click", () => {
                const itemName = (btn.dataset.item || "").trim();
                const quantity = parseInt(btn.dataset.qty, 10) || 0;

                if (!itemName || quantity <= 0) {
                  showCancelNotification("⚠️ Invalid item or quantity.", "error");
                  return;
                }

                if (!confirm(`Add "${itemName}" (${quantity}) to inventory?`)) return;

                btn.disabled = true;
                btn.textContent = "Adding...";

                const formData = new FormData();
                formData.append("item", itemName);
                formData.append("quantity", quantity);

                fetch("helpers/add_to_inventory.php", { method: "POST", body: formData })
                  .then(res => res.json())
                  .then(data => {
                    showCancelNotification(data.message, data.status === "success" ? "success" : "error");

                    if (data.status === "success") {

                      const box = btn.closest(".delivered-item-box");
                      box.style.transition = "opacity 0.3s ease";
                      box.style.opacity = "0";

                      setTimeout(() => {
                        box.remove();
                        loadInventoryStats();
                      }, 300);
                    } else {
                      btn.disabled = false;
                      btn.textContent = "➕ Add to Inventory";
                    }
                  })
                  .catch(() => {
                    showCancelNotification("⚠️ Failed to add to inventory.", "error");
                    btn.disabled = false;
                    btn.textContent = "➕ Add to Inventory";
                  });
              });
            });
          };

  // ========== UPDATE QUANTITY ==========
      const attachQuantityUpdateButtons = () => {
        document.querySelectorAll(".update-btn").forEach(btn => {
          btn.addEventListener("click", () => {
            const itemName = btn.dataset.item;
            const box = btn.closest(".delivered-item-box");
            const input = box.querySelector(".update-input");
            const qtySpan = box.querySelector(".delivered-qty");
            const newQty = parseInt(input.value.trim(), 10);
            if (isNaN(newQty) || newQty <= 0) return showCancelNotification("⚠️ Invalid quantity value", "error");

            const formData = new FormData();
            formData.append("item", itemName);
            formData.append("quantity", newQty);

            fetch("helpers/update_quantity.php", { method: "POST", body: formData })
              .then(res => res.json())
              .then(data => {
                showCancelNotification(data.message, data.status === "success" ? "success" : "error");
                if (data.status === "success") {
                  qtySpan.textContent = `Qty: ${newQty}`;
                  box.style.backgroundColor = "#28a74533";
                  setTimeout(() => box.style.backgroundColor = "", 1000);
                  input.value = "";
                  handleLowStockItem(box, newQty, itemName);
                }
              })
              .catch(() => showCancelNotification("⚠️ Failed to update quantity", "error"));
          });
        });
      };

  // ========== LOW STOCK HANDLING ==========
          const handleLowStockItem = (box, qty, itemName) => {
            const lowStockDiv = document.getElementById("lowstock");
            const inventoryContainer = document.getElementById("delivered-items-container");

            if (qty <= 10) {
              if (!lowStockDiv.contains(box)) {
                lowStockDiv.appendChild(box);
              }

              box.querySelector(".update-input")?.remove();
              box.querySelector(".update-btn")?.remove();

              if (!box.querySelector(".reorder-btn")) {
                const reorderBtn = document.createElement("button");
                reorderBtn.className = "reorder-btn";
                reorderBtn.textContent = "Reorder";
                reorderBtn.style.marginLeft = "10px";
                reorderBtn.addEventListener("click", () => reorderItem(itemName));
                box.querySelector(".delivered-controls").appendChild(reorderBtn);
              }
              if (!box.querySelector(".delete-btn")) {
                const deleteBtn = document.createElement("button");
                deleteBtn.className = "delete-btn";
                deleteBtn.textContent = "🗑";
                deleteBtn.style.cssText = "background:#dc3545;color:#fff;border:none;padding:5px 10px;border-radius:5px;margin-left:8px;cursor:pointer;";
                deleteBtn.dataset.item = itemName;
                box.querySelector(".delivered-controls").appendChild(deleteBtn);
              }

            } else {

              if (!inventoryContainer.contains(box)) {
                inventoryContainer.appendChild(box);
              }

              if (!box.querySelector(".update-input")) {
                const input = document.createElement("input");
                input.type = "number";
                input.className = "update-input";
                input.placeholder = "New qty";
                input.min = "1";
                box.querySelector(".delivered-controls").insertBefore(input, box.querySelector(".delivered-qty").nextSibling);
              }

              if (!box.querySelector(".update-btn")) {
                const updateBtn = document.createElement("button");
                updateBtn.className = "update-btn";
                updateBtn.dataset.item = itemName;
                updateBtn.textContent = "Update";
                box.querySelector(".delivered-controls").appendChild(updateBtn);
                attachQuantityUpdateButtons();
              }

              box.querySelector(".reorder-btn")?.remove();
            }

            updateLowStockCard();
          };

  // ========== MOVE LOW STOCK ON LOAD ==========
      const moveLowStockOnLoad = () => {
        const inventoryContainer = document.getElementById("delivered-items-container");
        const lowStockDiv = document.getElementById("lowstock");
        if (!inventoryContainer || !lowStockDiv) return;

        inventoryContainer.querySelectorAll(".delivered-item-box").forEach(box => {
          const qty = parseInt(box.querySelector(".delivered-qty")?.textContent.replace(/\D/g, ""), 10);
          if (qty <= 10) {
            lowStockDiv.appendChild(box);
            box.querySelector(".update-input")?.remove();
            box.querySelector(".update-btn")?.remove();
            if (!box.querySelector(".reorder-btn")) {
              const reorderBtn = document.createElement("button");
              reorderBtn.className = "reorder-btn";
              reorderBtn.textContent = "Reorder";
              reorderBtn.style.marginLeft = "10px";
              const itemName = box.querySelector("h4").textContent;
              reorderBtn.addEventListener("click", () => reorderItem(itemName));
              box.querySelector(".delivered-controls").appendChild(reorderBtn);
            }
          }
        });
      };
  
  // ========== UPDATE LOW STOCK CARD ==========
          const updateLowStockCard = () => {
            const lowStockDiv = document.getElementById("lowstock");
            const card = document.getElementById("stat-low-stock");
            if (!lowStockDiv || !card) return;
            card.textContent = lowStockDiv.querySelectorAll(".delivered-item-box").length;
          };
  // ========== DELETE ITEM BUTTON ==========
      const attachDeleteButtons = () => {
        document.querySelectorAll(".delete-btn").forEach(btn => {
          btn.addEventListener("click", () => {
            const itemName = btn.dataset.item;
            if (!itemName) return;

            if (!confirm(`⚠️ Delete "${itemName}" from database?`)) return;

            btn.disabled = true;
            btn.textContent = "Deleting...";

            const formData = new FormData();
            formData.append("item", itemName);

            fetch("helpers/delete_item.php", { method: "POST", body: formData })
              .then(res => res.json())
              .then(data => {
                showCancelNotification(data.message, data.status);
                if (data.status === "success") {
                  const box = btn.closest(".delivered-item-box");
                  box.style.transition = "opacity 0.3s ease";
                  box.style.opacity = "0";
                  setTimeout(() => {
                    box.remove();
                    updateLowStockCard();
                  }, 300);
                } else {
                  btn.disabled = false;
                  btn.textContent = "🗑";
                }
              })
              .catch(() => {
                showCancelNotification("⚠️ Failed to delete item.", "error");
                btn.disabled = false;
                btn.textContent = "🗑";
              });
          });
        });
      };

  // reorderItem ==========
      const reorderItem = async (itemName) => {
        try {
          const res = await fetch(`helpers/get_recent_deliveries.php?item=${encodeURIComponent(itemName)}`);
          const json = await res.json();
          if (json.status !== "success") {
            showCancelNotification(json.message || "⚠️ Failed to fetch deliveries", "error");
            return;
          }
          const deliveries = json.data;
          loadPageWithFade("schedule_delivery_form.php");
          setTimeout(() => {
            const form = document.querySelector("#deliveryForm");
            if (!form) return;
            const delivery = deliveries[0];
            form.client_name.value = delivery.client_name || '';
            form.address.value = delivery.address || '';
            form.item.value = delivery.item || '';
            form.quantity.value = delivery.quantity || '';
            form.delivery_date.value = delivery.delivery_date || '';
            form.status.value = delivery.status || 1;
            form.contact.value = delivery.contact || '';
            showCancelNotification(`📝 Auto-filled form for "${itemName}" based on recent deliveries`, "success");
          }, 500);
        } catch (err) {
          console.error(err);
          showCancelNotification("⚠️ Error auto-filling delivery form.", "error");
        }
      };

  // ========== SIDEBAR EVENTS ==========
      document.addEventListener("click", e => {
        const pageBtn = e.target.closest("[data-page]");
        if (pageBtn) {
          e.preventDefault();
          loadPageWithFade(pageBtn.getAttribute("data-page"));
          return;
        }
        const dropdownBtn = e.target.closest(".dropdown_toggle");
        if (dropdownBtn) dropdownBtn.nextElementSibling?.classList.toggle("show");
      });

      loadInventoryStats();

  // ========== ANALYTICS CHARTS ==========
      async function initReportAnalyticsCharts() {
        const cards = document.querySelectorAll(".chart_card");
        cards.forEach((card, i) => setTimeout(() => card.classList.add("show"), i * 200));

        try {
          const res = await fetch("report_analytics.php?rand=" + Date.now());
          const json = await res.json();
          if (json.status !== "success") throw new Error(json.message);
          const { low_stock, monthly_deliveries, revenue_overview } = json.data;

          // Monthly Deliveries Chart
          const deliveryCtx = document.getElementById("analyticsDeliveryChart");
          if (deliveryCtx)
            new Chart(deliveryCtx, {
              type: "line",
              data: {
                labels: monthly_deliveries.map(m => m.month),
                datasets: [{
                  label: "Deliveries Completed",
                  data: monthly_deliveries.map(m => m.deliveries),
                  borderColor: "#00d4ff",
                  backgroundColor: "rgba(0,212,255,0.3)",
                  tension: 0.3,
                  fill: true
                }]
              },
              options: { scales: { y: { beginAtZero: true } } }
            });

          // Low Stock Chart
          const stockCtx = document.getElementById("analyticsStockChart");
          if (stockCtx)
            new Chart(stockCtx, {
              type: "bar",
              data: {
                labels: low_stock.length ? low_stock.map(i => i.item) : ["No Low Stock Items"],
                datasets: [{
                  label: "Available Quantity",
                  data: low_stock.length ? low_stock.map(i => i.total_qty) : [0],
                  backgroundColor: low_stock.map(i => i.total_qty <= 5 ? "#d00000" : "#ffb703")
                }]
              },
              options: { scales: { y: { beginAtZero: true } } }
            });

          // Revenue Overview Chart
          const revenueCtx = document.getElementById("analyticsRevenueChart");
          if (revenueCtx)
            new Chart(revenueCtx, {
              type: "doughnut",
              data: {
                labels: revenue_overview.length ? revenue_overview.map(r => r.category) : ["No Data"],
                datasets: [{
                  data: revenue_overview.length ? revenue_overview.map(r => r.amount) : [0],
                  backgroundColor: ["#ff6384","#36a2eb","#ffce56","#4bc0c0","#9966ff","#ff9f40"]
                }]
              }
            });

        } catch (err) {
          console.error(err);
          showCancelNotification("⚠️ Failed to load analytics charts.", "error");
        }
      }
    });
</script>
</body>
</html>