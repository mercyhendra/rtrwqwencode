/**
 * RT/RW Digital Management System
 * Main JavaScript File
 */

// ========================================
// Modal Functions
// ========================================

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// ========================================
// Sidebar Toggle (Mobile)
// ========================================

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
    
    if (overlay) {
        overlay.classList.toggle('active');
    }
    
    // Prevent body scroll when sidebar is open on mobile
    if (window.innerWidth <= 1024) {
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }
}

const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', toggleSidebar);
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 1024 && sidebar && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
        sidebar.classList.remove('active');
        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Close sidebar when clicking on a menu item (mobile)
document.querySelectorAll('.sidebar-menu-item').forEach(item => {
    item.addEventListener('click', () => {
        if (window.innerWidth <= 1024) {
            toggleSidebar();
        }
    });
});

// Handle window resize
let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        if (window.innerWidth > 1024 && sidebar) {
            sidebar.classList.remove('active');
            if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }, 250);
});

// ========================================
// Active Menu Item
// ========================================

document.querySelectorAll('.sidebar-menu-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.sidebar-menu-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
    });
});

// ========================================
// Form Submissions
// ========================================

// Login Form
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        // Simple validation
        if (email && password) {
            // Simulate login - redirect to dashboard
            alert('Login berhasil! Mengalihkan ke dashboard...');
            window.location.href = 'dashboard.html';
        }
    });
}

// Register Form
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const noKK = document.getElementById('noKK').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Validate KK format
        if (noKK.length !== 16 || !/^\d+$/.test(noKK)) {
            alert('Nomor KK harus 16 digit angka!');
            return;
        }
        
        // Check if KK already exists (mock validation)
        const existingKK = localStorage.getItem('registeredKK');
        const kkList = existingKK ? JSON.parse(existingKK) : [];
        
        if (kkList.includes(noKK)) {
            alert('Nomor KK ini sudah terdaftar! Setiap keluarga hanya boleh mendaftar 1 kali.');
            return;
        }
        
        if (password !== confirmPassword) {
            alert('Password tidak cocok!');
            return;
        }
        
        if (password.length < 8) {
            alert('Password minimal 8 karakter!');
            return;
        }
        
        // Save KK to localStorage (mock)
        kkList.push(noKK);
        localStorage.setItem('registeredKK', JSON.stringify(kkList));
        
        // Simulate registration
        alert('Registrasi berhasil! Silakan login dengan akun Anda.');
        window.location.href = 'login.html';
    });
}

// Add Warga Form
const addWargaForm = document.getElementById('addWargaForm');
if (addWargaForm) {
    addWargaForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const noKK = addWargaForm.querySelector('input[pattern="[0-9]{16}"]').value;
        
        // Validate KK format
        if (noKK.length !== 16) {
            alert('Nomor KK harus 16 digit angka!');
            return;
        }
        
        // Check if KK already exists (mock validation)
        const existingKK = localStorage.getItem('registeredKK');
        const kkList = existingKK ? JSON.parse(existingKK) : [];
        
        if (kkList.includes(noKK)) {
            alert('Nomor KK ini sudah terdaftar! Setiap keluarga hanya boleh memiliki 1 data KK.');
            return;
        }
        
        // Save KK
        kkList.push(noKK);
        localStorage.setItem('registeredKK', JSON.stringify(kkList));
        
        alert('Data warga berhasil ditambahkan!');
        closeModal('addWargaModal');
        // In a real app, you would submit the data to a server
    });
}

// Edit Warga Form
const editWargaForm = document.getElementById('editWargaForm');
if (editWargaForm) {
    editWargaForm.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Data warga berhasil diupdate!');
        closeModal('editWargaModal');
    });
}

// Add Pengumuman Form
const addPengumumanForm = document.getElementById('addPengumumanForm');
if (addPengumumanForm) {
    addPengumumanForm.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Pengumuman berhasil diterbitkan!');
        closeModal('addPengumumanModal');
    });
}

// Edit Pengumuman Form
const editPengumumanForm = document.getElementById('editPengumumanForm');
if (editPengumumanForm) {
    editPengumumanForm.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Pengumuman berhasil diupdate!');
        closeModal('editPengumumanModal');
    });
}

// Request Surat Form
const requestSuratForm = document.getElementById('requestSuratForm');
if (requestSuratForm) {
    requestSuratForm.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Permohonan surat berhasil diajukan! Silakan tunggu verifikasi.');
        closeModal('requestSuratModal');
    });
}

// ========================================
// Utility Functions
// ========================================

function confirmDelete() {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        alert('Data berhasil dihapus!');
        // In a real app, you would delete the data from the server
    }
}

function markAllAsRead() {
    document.querySelectorAll('.activity-item').forEach(item => {
        item.style.background = 'none';
        const badge = item.querySelector('.badge');
        if (badge) {
            badge.remove();
        }
    });
    document.querySelector('.notification-badge').style.display = 'none';
    alert('Semua notifikasi telah ditandai sebagai dibaca.');
}

// ========================================
// Search Functionality
// ========================================

const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const tableRows = document.querySelectorAll('#wargaTableBody tr');
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}

// ========================================
// Filter Functionality
// ========================================

const filterRT = document.getElementById('filterRT');
if (filterRT) {
    filterRT.addEventListener('change', (e) => {
        const filterValue = e.target.value;
        const tableRows = document.querySelectorAll('#wargaTableBody tr');
        
        tableRows.forEach(row => {
            const rtCell = row.cells[3]?.textContent.trim();
            if (filterValue === '' || rtCell === filterValue) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
}

// ========================================
// Dashboard Stats Animation
// ========================================

function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value.toLocaleString();
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Animate stats on page load
document.addEventListener('DOMContentLoaded', () => {
    const statsElements = document.querySelectorAll('.stat-card-content h3');
    statsElements.forEach(el => {
        const text = el.textContent;
        const numericValue = text.replace(/[^0-9]/g, '');
        if (numericValue) {
            const value = parseInt(numericValue);
            if (!isNaN(value)) {
                el.textContent = '0';
                setTimeout(() => {
                    animateValue(el, 0, value, 1000);
                }, 500);
            }
        }
    });
});

// ========================================
// Notification Counter
// ========================================

function updateNotificationCount(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

// ========================================
// Toast Notification (Optional)
// ========================================

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease;
    `;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add CSS animations for toast
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ========================================
// Console Welcome Message
// ========================================

console.log(`
╔════════════════════════════════════════╗
║     RT/RW Digital Management System    ║
║           Welcome to the app!          ║
╚════════════════════════════════════════╝
`);
