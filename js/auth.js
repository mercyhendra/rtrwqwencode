/**
 * RT/RW Digital Management System
 * Authentication & Authorization Module
 */

// ========================================
// User Roles
// ========================================
const USER_ROLES = {
    WARGA: 'warga',
    RT: 'rt',
    RW: 'rw',
    ADMIN: 'admin'
};

// ========================================
// Session Management
// ========================================
class AuthManager {
    constructor() {
        this.currentUser = null;
        this.init();
    }

    init() {
        // Check if user is logged in
        const savedUser = localStorage.getItem('currentUser');
        if (savedUser) {
            this.currentUser = JSON.parse(savedUser);
        }
        
        // Initialize default users if not exists
        this.initDefaultUsers();
    }

    // Login with credentials
    login(email, password) {
        // Mock user database
        const users = this.getUsers();
        
        const user = users.find(u => u.email === email && u.password === password);
        
        if (user) {
            // Remove password from session
            const { password, ...userWithoutPassword } = user;
            this.currentUser = userWithoutPassword;
            localStorage.setItem('currentUser', JSON.stringify(userWithoutPassword));
            return { success: true, user: userWithoutPassword };
        }
        
        return { success: false, message: 'Email atau password salah' };
    }

    // Register new user
    register(userData) {
        const users = this.getUsers();
        
        // Check if email already exists
        if (users.find(u => u.email === userData.email)) {
            return { success: false, message: 'Email sudah terdaftar' };
        }
        
        // Check if KK already exists
        if (users.find(u => u.noKK === userData.noKK)) {
            return { success: false, message: 'Nomor KK sudah terdaftar' };
        }
        
        // Check if NIK already exists
        if (users.find(u => u.nik === userData.nik)) {
            return { success: false, message: 'NIK sudah terdaftar' };
        }
        
        const newUser = {
            id: Date.now().toString(),
            role: USER_ROLES.WARGA, // Default role is warga
            ...userData,
            createdAt: new Date().toISOString()
        };
        
        users.push(newUser);
        this.saveUsers(users);
        
        return { success: true, message: 'Registrasi berhasil' };
    }

    // Logout
    logout() {
        this.currentUser = null;
        localStorage.removeItem('currentUser');
    }

    // Check if user is logged in
    isLoggedIn() {
        return this.currentUser !== null;
    }

    // Check user role
    hasRole(roles) {
        if (!this.currentUser) return false;
        if (Array.isArray(roles)) {
            return roles.includes(this.currentUser.role);
        }
        return this.currentUser.role === roles;
    }

    // Check if user can edit/delete specific data
    canEdit(targetData) {
        if (!this.currentUser) return false;

        // Admin, RT, RW can edit all
        if (this.hasRole([USER_ROLES.ADMIN, USER_ROLES.RT, USER_ROLES.RW])) {
            return true;
        }

        // Warga can edit their own data
        if (this.hasRole(USER_ROLES.WARGA)) {
            return this.currentUser.id === targetData.id || 
                   this.currentUser.noKK === targetData.noKK;
        }

        return false;
    }

    // Check if user can view specific data
    canView(targetData) {
        if (!this.currentUser) return false;
        
        // Admin, RT, RW can view all
        if (this.hasRole([USER_ROLES.ADMIN, USER_ROLES.RT, USER_ROLES.RW])) {
            return true;
        }
        
        // Warga can only view their own family data
        if (this.hasRole(USER_ROLES.WARGA)) {
            return this.currentUser.noKK === targetData.noKK;
        }
        
        return false;
    }

    // Get current user
    getCurrentUser() {
        return this.currentUser;
    }

    // Get all users (for admin)
    getAllUsers() {
        let users = this.getUsers();
        
        // If no users in localStorage, load default users
        if (users.length === 0) {
            users = this.getDefaultUsers();
            this.saveUsers(users);
        }
        
        return users.map(({ password, ...user }) => user); // Exclude passwords
    }

    // Get users by role
    getUsersByRole(role) {
        const users = this.getUsers();
        return users.filter(u => u.role === role);
    }

    // Update user
    updateUser(userId, userData) {
        const users = this.getUsers();
        const index = users.findIndex(u => u.id === userId);
        
        if (index === -1) {
            return { success: false, message: 'User tidak ditemukan' };
        }
        
        // Check permission
        if (!this.canEdit(users[index])) {
            return { success: false, message: 'Tidak ada izin untuk mengubah data ini' };
        }
        
        users[index] = { ...users[index], ...userData };
        this.saveUsers(users);
        
        // Update current user session if updating self
        if (userId === this.currentUser.id) {
            const { password, ...updatedUser } = users[index];
            this.currentUser = updatedUser;
            localStorage.setItem('currentUser', JSON.stringify(updatedUser));
        }
        
        return { success: true, message: 'Data berhasil diupdate' };
    }

    // Delete user
    deleteUser(userId) {
        const users = this.getUsers();
        const user = users.find(u => u.id === userId);
        
        if (!user) {
            return { success: false, message: 'User tidak ditemukan' };
        }
        
        // Check permission
        if (!this.canEdit(user)) {
            return { success: false, message: 'Tidak ada izin untuk menghapus data ini' };
        }
        
        // Prevent self-deletion
        if (userId === this.currentUser.id) {
            return { success: false, message: 'Tidak dapat menghapus akun sendiri' };
        }
        
        const filteredUsers = users.filter(u => u.id !== userId);
        this.saveUsers(filteredUsers);
        
        return { success: true, message: 'User berhasil dihapus' };
    }

    // Helper: Get users from localStorage
    getUsers() {
        const users = localStorage.getItem('users');
        if (users) {
            try {
                return JSON.parse(users);
            } catch (e) {
                console.error('Error parsing users from localStorage:', e);
                return this.getDefaultUsers();
            }
        }
        // Return empty array if no users, don't auto-create default users
        return [];
    }

    // Helper: Save users to localStorage
    saveUsers(users) {
        try {
            localStorage.setItem('users', JSON.stringify(users));
            console.log('Users saved to localStorage:', users);
        } catch (e) {
            console.error('Error saving users to localStorage:', e);
        }
    }

    // Helper: Get default users (for demo)
    getDefaultUsers() {
        const defaultUsers = [
            {
                id: '1',
                email: 'admin@rtrw.com',
                password: 'admin123',
                role: USER_ROLES.ADMIN,
                nama: 'Administrator',
                nik: '3171012345678999',
                noKK: '3171012345678999',
                rt: '001/001',
                rw: '001',
                noRumah: 'A-01',
                whatsapp: '081234567890',
                pekerjaan: 'Administrator Sistem',
                status: 'aktif',
                createdAt: new Date().toISOString()
            },
            {
                id: '2',
                email: 'rt@rtrw.com',
                password: 'rt123',
                role: USER_ROLES.RT,
                nama: 'Ketua RT 001',
                nik: '3171012345678998',
                noKK: '3171012345678998',
                rt: '001/001',
                rw: '001',
                noRumah: 'A-02',
                whatsapp: '081234567891',
                pekerjaan: 'Ketua RT',
                status: 'aktif',
                createdAt: new Date().toISOString()
            },
            {
                id: '3',
                email: 'warga@rtrw.com',
                password: 'warga123',
                role: USER_ROLES.WARGA,
                nama: 'Ahmad Santoso',
                nik: '3171012345678901',
                noKK: '3171012345678001',
                rt: '001/001',
                rw: '001',
                noRumah: 'A-12',
                whatsapp: '081234567890',
                pekerjaan: 'Wiraswasta',
                status: 'aktif',
                createdAt: new Date().toISOString()
            }
        ];
        
        return defaultUsers;
    }
    
    // Initialize default users if not exists
    initDefaultUsers() {
        const users = localStorage.getItem('users');
        if (!users) {
            const defaultUsers = this.getDefaultUsers();
            this.saveUsers(defaultUsers);
            console.log('Default users initialized');
        }
    }
}

// ========================================
// Global Auth Instance
// ========================================
const auth = new AuthManager();

// ========================================
// Protected Page Handler
// ========================================
function requireAuth(requiredRoles = null) {
    document.addEventListener('DOMContentLoaded', () => {
        if (!auth.isLoggedIn()) {
            window.location.href = 'login.html';
            return;
        }
        
        if (requiredRoles && !auth.hasRole(requiredRoles)) {
            // Redirect to appropriate page based on role
            if (auth.hasRole(USER_ROLES.WARGA)) {
                window.location.href = 'warga-saya.html';
            } else {
                window.location.href = 'dashboard.html';
            }
            return;
        }
        
        // Update UI based on user role
        updateUIForRole();
    });
}

// ========================================
// Update UI Based on Role
// ========================================
function updateUIForRole() {
    const user = auth.getCurrentUser();
    if (!user) return;
    
    // Update sidebar user info
    const userNameEl = document.querySelector('.sidebar-user-name');
    const userRoleEl = document.querySelector('.sidebar-user-role');
    
    if (userNameEl) {
        userNameEl.textContent = user.nama || user.email;
    }
    
    if (userRoleEl) {
        const roleNames = {
            [USER_ROLES.WARGA]: 'Warga',
            [USER_ROLES.RT]: 'Ketua RT',
            [USER_ROLES.RW]: 'Ketua RW',
            [USER_ROLES.ADMIN]: 'Administrator'
        };
        userRoleEl.textContent = roleNames[user.role] || 'Warga';
    }
    
    // Show/hide elements based on role
    const adminOnlyElements = document.querySelectorAll('[data-requires-role="admin"]');
    const rtOnlyElements = document.querySelectorAll('[data-requires-role="rt"]');
    const wargaOnlyElements = document.querySelectorAll('[data-requires-role="warga"]');
    const staffOnlyElements = document.querySelectorAll('[data-requires-role="staff"]');
    
    adminOnlyElements.forEach(el => {
        el.style.display = auth.hasRole(USER_ROLES.ADMIN) ? '' : 'none';
    });
    
    rtOnlyElements.forEach(el => {
        el.style.display = auth.hasRole([USER_ROLES.ADMIN, USER_ROLES.RT, USER_ROLES.RW]) ? '' : 'none';
    });
    
    staffOnlyElements.forEach(el => {
        el.style.display = auth.hasRole([USER_ROLES.ADMIN, USER_ROLES.RT, USER_ROLES.RW]) ? '' : 'none';
    });
    
    // Hide admin menu items for warga
    if (auth.hasRole(USER_ROLES.WARGA)) {
        const adminMenuItems = document.querySelectorAll('[data-menu-role="admin"]');
        adminMenuItems.forEach(el => el.style.display = 'none');
    }
}

// ========================================
// Filter Data Based on Role
// ========================================
function filterDataByRole(data) {
    const user = auth.getCurrentUser();
    if (!user) return [];
    
    // Admin, RT, RW can see all data
    if (auth.hasRole([USER_ROLES.ADMIN, USER_ROLES.RT, USER_ROLES.RW])) {
        return data;
    }
    
    // Warga can only see their own family data
    if (auth.hasRole(USER_ROLES.WARGA)) {
        return data.filter(item => item.noKK === user.noKK);
    }
    
    return data;
}

// ========================================
// Export for use in other pages
// ========================================
window.auth = auth;
window.requireAuth = requireAuth;
window.updateUIForRole = updateUIForRole;
window.filterDataByRole = filterDataByRole;
window.USER_ROLES = USER_ROLES;
