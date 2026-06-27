// Authentication Utilities and Page Guards

const auth = {
    // Retrieve token from localStorage
    getToken() {
        return localStorage.getItem('access_token');
    },

    // Retrieve user details from localStorage
    getUser() {
        const userStr = localStorage.getItem('user');
        try {
            return userStr ? JSON.parse(userStr) : null;
        } catch (e) {
            return null;
        }
    },

    // Save session credentials
    saveSession(token, user) {
        localStorage.setItem('access_token', token);
        localStorage.setItem('user', JSON.stringify(user));
    },

    // Clear session credentials (logout)
    clearSession() {
        localStorage.removeItem('access_token');
        localStorage.removeItem('user');
    },

    // Check if user is authenticated
    isAuthenticated() {
        return !!this.getToken();
    },

    // Handle routing guards
    checkAuth() {
        const path = window.location.pathname;
        const segments = path.toLowerCase().split('/').filter(Boolean);
        const page = segments[segments.length - 1] || '';
        
        // Define public/guest-only pages
        const isAuthPage = page === 'login' || page === 'login.html' || page === 'register' || page === 'register.html';
        
        if (this.isAuthenticated()) {
            // If logged in and trying to access login/register, redirect to dashboard
            if (isAuthPage || page === '') {
                if (!path.includes('.html')) {
                    window.location.href = '/dashboard';
                } else {
                    window.location.href = 'index.html';
                }
            }
        } else {
            // If NOT logged in and trying to access a protected page, redirect to login
            if (!isAuthPage) {
                if (!path.includes('.html')) {
                    window.location.href = '/login';
                } else {
                    window.location.href = 'login.html';
                }
            }
        }
    },

    // Call logout endpoint and clear local storage
    async logout() {
        try {
            if (typeof apiRequest === 'function') {
                await apiRequest('/logout', { method: 'POST' });
            }
        } catch (error) {
            console.error('Logout request failed:', error);
        } finally {
            this.clearSession();
            if (!window.location.pathname.includes('.html')) {
                window.location.href = '/login';
            } else {
                window.location.href = 'login.html';
            }
        }
    },

    // Update user info placeholder in the UI
    updateUI() {
        const user = this.getUser();
        if (user) {
            document.querySelectorAll('.user-name-placeholder').forEach(el => {
                el.textContent = user.name || 'User';
            });
            document.querySelectorAll('.user-email-placeholder').forEach(el => {
                el.textContent = user.email || '';
            });
            // Show initials for avatar placeholder
            const initials = user.name ? user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'U';
            document.querySelectorAll('.user-avatar-placeholder').forEach(el => {
                el.textContent = initials;
            });
        }
    }
};

// Execute page guard check immediately when script is parsed
auth.checkAuth();

// Run UI updates once DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    auth.updateUI();
    
    // Wire up logout button if it exists
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            auth.logout();
        });
    }
});
