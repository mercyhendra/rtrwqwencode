/**
 * RT/RW Digital Management System
 * Database API Client - MariaDB Backend
 */

// ========================================
// API Configuration
// ========================================
const API_BASE_URL = 'api';

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
// Database API Client
// ========================================
class DatabaseAPI {
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
    }

    // ========================================
    // API Helper Methods
    // ========================================
    async apiRequest(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(`${API_BASE_URL}/${endpoint}`, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || 'Request failed');
            }
            
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // ========================================
    // Authentication Methods
    // ========================================
    async register(userData) {
        try {
            const result = await this.apiRequest('users.php?action=register', 'POST', userData);
            return result;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }

    async login(email, password) {
        try {
            const result = await this.apiRequest('users.php?action=login', 'POST', { email, password });
            
            if (result.success) {
                // Store user in session (without password)
                const { password, ...userWithoutPassword } = result.user;
                this.currentUser = userWithoutPassword;
                localStorage.setItem('currentUser', JSON.stringify(userWithoutPassword));
            }
            
            return result;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }

    logout() {
        this.currentUser = null;
        localStorage.removeItem('currentUser');
    }

    isLoggedIn() {
        return this.currentUser !== null;
    }

    getCurrentUser() {
        return this.currentUser;
    }

    hasRole(roles) {
        if (!this.currentUser) return false;
        if (Array.isArray(roles)) {
            return roles.includes(this.currentUser.role);
        }
        return this.currentUser.role === roles;
    }

    // ========================================
    // User Management Methods
    // ========================================
    async getAllUsers() {
        try {
            const result = await this.apiRequest('users.php?action=get_all');
            if (result.success) {
                return result.data;
            }
            return [];
        } catch (error) {
            console.error('Error fetching users:', error);
            return [];
        }
    }

    async getUserById(id) {
        try {
            const result = await this.apiRequest(`users.php?action=get_one&id=${id}`);
            if (result.success) {
                return result.data;
            }
            return null;
        } catch (error) {
            console.error('Error fetching user:', error);
            return null;
        }
    }

    async updateUser(userId, userData) {
        try {
            const result = await this.apiRequest('users.php?action=update', 'PUT', {
                id: userId,
                ...userData
            });
            return result;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }

    async deleteUser(userId) {
        try {
            const result = await this.apiRequest(`users.php?action=delete&id=${userId}`, 'DELETE');
            return result;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }

    // ========================================
    // Permission Methods
    // ========================================
    canEdit(targetData) {
        if (!this.currentUser) return false;
        
        // Admin, RT, RW can edit all
        if (this.hasRole([USER_ROLES.ADMIN, USER_ROLES.RT, USER_ROLES.RW])) {
            return true;
        }
        
        // Warga can edit their own data
        if (this.hasRole(USER_ROLES.WARGA)) {
            return this.currentUser.id === targetData.id || 
                   this.currentUser.no_kk === targetData.no_kk;
        }
        
        return false;
    }

    canView(targetData) {
        if (!this.currentUser) return false;
        
        // Admin, RT, RW can view all
        if (this.hasRole([USER_ROLES.ADMIN, USER_ROLES.RT, USER_ROLES.RW])) {
            return true;
        }
        
        // Warga can only view their own family data
        if (this.hasRole(USER_ROLES.WARGA)) {
            return this.currentUser.no_kk === targetData.no_kk;
        }
        
        return false;
    }
}

// ========================================
// Global Database Instance
// ========================================
const db = new DatabaseAPI();

// Alias for backward compatibility
const auth = db;

// ========================================
// Protected Page Handler
// ========================================
function requireAuth(requiredRoles = null) {
    document.addEventListener('DOMContentLoaded', async () => {
        if (!db.isLoggedIn()) {
            window.location.href = 'login.html';
            return;
        }
        
        if (requiredRoles && !db.hasRole(requiredRoles)) {
            // Redirect to appropriate page based on role
            if (db.hasRole(USER_ROLES.WARGA)) {
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
    const user = db.getCurrentUser();
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
    const staffOnlyElements = document.querySelectorAll('[data-requires-role="staff"]');
    
    adminOnlyElements.forEach(el => {
        el.style.display = db.hasRole(USER_ROLES.ADMIN) ? '' : 'none';
    });
    
    rtOnlyElements.forEach(el => {
        el.style.display = db.hasRole([USER_ROLES.ADMIN, USER_ROLES.RT, USER_ROLES.RW]) ? '' : 'none';
    });
    
    staffOnlyElements.forEach(el => {
        el.style.display = db.hasRole([USER_ROLES.ADMIN, USER_ROLES.RT, USER_ROLES.RW]) ? '' : 'none';
    });
    
    // Hide admin menu items for warga
    if (db.hasRole(USER_ROLES.WARGA)) {
        const adminMenuItems = document.querySelectorAll('[data-menu-role="admin"]');
        adminMenuItems.forEach(el => el.style.display = 'none');
    }
}

// ========================================
// Filter Data Based on Role
// ========================================
function filterDataByRole(data) {
    const user = db.getCurrentUser();
    if (!user) return [];
    
    // Admin, RT, RW can see all data
    if (db.hasRole([USER_ROLES.ADMIN, USER_ROLES.RT, USER_ROLES.RW])) {
        return data;
    }
    
    // Warga can only see their own family data
    if (db.hasRole(USER_ROLES.WARGA)) {
        return data.filter(item => item.no_kk === user.no_kk);
    }
    
    return data;
}

// ========================================
// Export for use in other pages
// ========================================
window.db = db;
window.auth = db;
window.requireAuth = requireAuth;
window.updateUIForRole = updateUIForRole;
window.filterDataByRole = filterDataByRole;
window.USER_ROLES = USER_ROLES;

console.log('📊 Database API initialized (MariaDB Backend)');
