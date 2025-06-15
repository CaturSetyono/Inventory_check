
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const sidebarToggle = document.getElementById("sidebar-toggle");
  const sidebarToggleIcon = document.getElementById("sidebar-toggle-icon");
  const sidebarOverlay = document.getElementById("sidebar-overlay");
  const closeSidebarBtn = document.getElementById("close-sidebar-btn");
  const navLinks = sidebar.querySelectorAll(".nav-link");

  const profileButton = document.getElementById("profile-button");
  const profileDropdown = document.getElementById("profile-dropdown");

  const logoutModal = document.getElementById("logout-modal");
  const logoutModalContent = document.getElementById("logout-modal-content");
  const logoutTriggers = document.querySelectorAll(".logout-trigger");
  const cancelLogoutBtn = document.getElementById("cancel-logout-btn");

  const toggleMobileSidebar = (show) => {
    sidebar.classList.toggle("mobile-visible", show);
    sidebarOverlay.classList.toggle("hidden", !show);

    // Apply staggered animation
    if (show) {
      navLinks.forEach((link, index) => {
        link.style.transitionDelay = `${index * 50 + 100}ms`;
      });
    }
  };

  const toggleDesktopSidebar = () => {
    sidebar.classList.toggle("collapsed");
    sidebarToggleIcon.classList.toggle("rotated");
  };

  const handleSidebarToggle = () => {
    if (window.innerWidth < 768) {
      toggleMobileSidebar(true);
    } else {
      toggleDesktopSidebar();
    }
  };

  sidebarToggle.addEventListener("click", (e) => {
    e.stopPropagation();
    handleSidebarToggle();
  });

  const closeMobileSidebar = () => toggleMobileSidebar(false);
  sidebarOverlay.addEventListener("click", closeMobileSidebar);
  closeSidebarBtn.addEventListener("click", closeMobileSidebar);

  // Profile Dropdown Logic
  profileButton.addEventListener("click", (e) => {
    e.stopPropagation();
    profileDropdown.classList.toggle("hidden");
  });
  window.addEventListener("click", () =>
    profileDropdown.classList.add("hidden")
  );

  // Logout Modal Logic
  const toggleLogoutModal = (show) => {
    if (show) {
      logoutModal.classList.remove("hidden");
      setTimeout(
        () => logoutModalContent.classList.remove("scale-95", "opacity-0"),
        50
      );
    } else {
      logoutModalContent.classList.add("scale-95", "opacity-0");
      setTimeout(() => logoutModal.classList.add("hidden"), 200);
    }
  };

  logoutTriggers.forEach((trigger) => {
    trigger.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      profileDropdown.classList.add("hidden");
      toggleLogoutModal(true);
    });
  });

  cancelLogoutBtn.addEventListener("click", () => toggleLogoutModal(false));
  logoutModal.addEventListener("click", (e) => {
    if (e.target === logoutModal) toggleLogoutModal(false);
  });
});

document.addEventListener('DOMContentLoaded', () => {
    // ... (Kode sidebar, profil, logout, dan chart tidak berubah) ...

    // === KODE UNTUK MANAJEMEN STOK (EDIT & DELETE) ===
    const initStockManagement = () => {
        const stockTable = document.getElementById('stock-table');
        if (!stockTable) return;

        stockTable.addEventListener('click', (e) => {
            const cell = e.target.closest('.editable');
            if (cell && !cell.querySelector('input')) {
                // ... (logika untuk mengubah jadi input tidak berubah) ...

                const saveChanges = async () => {
                    const newValue = input.value.trim();
                    if (newValue === originalValue) {
                        cell.innerHTML = (field === 'harga_beli') ? new Intl.NumberFormat('id-ID').format(originalValue) : originalValue;
                        return;
                    }
                    
                    const id = cell.parentElement.dataset.id;
                    // PERUBAHAN DI SINI: URL dan body dari fetch
                    const response = await fetch('../controllers/stock_manager_controller.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'update', id, field, value: newValue })
                    });
                    const result = await response.json();
                    if (result.status === 'success') {
                        cell.innerHTML = (field === 'harga_beli') ? new Intl.NumberFormat('id-ID').format(newValue) : newValue;
                    } else {
                        alert('Gagal update: ' + result.message);
                        cell.innerHTML = (field === 'harga_beli') ? new Intl.NumberFormat('id-ID').format(originalValue) : originalValue;
                    }
                };
                
                // ... (event listener untuk input tidak berubah) ...
            }

            const deleteButton = e.target.closest('.delete-btn');
            if (deleteButton) {
                const id = deleteButton.dataset.id;
                const name = deleteButton.dataset.name;
                if (confirm(`Yakin mau menghapus barang "${name}"? Semua data transaksi terkait juga akan dihapus.`)) {
                    // PERUBAHAN DI SINI: URL dan body dari fetch
                    fetch('../controllers/stock_manager_controller.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', id })
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            deleteButton.closest('tr').remove();
                        } else {
                            alert('Gagal hapus: ' . result.message);
                        }
                    });
                }
            }
        });
    };

    initStockManagement();
});