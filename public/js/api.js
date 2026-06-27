// API client wrapper for Fetch requests

/**
 * Perform an authenticated API request
 * @param {string} endpoint - The API endpoint path (e.g. '/products' or '/login')
 * @param {Object} options - Standard fetch options
 * @returns {Promise<Object>} - Decoded JSON response
 */
async function apiRequest(endpoint, options = {}) {
    const url = `${API_BASE_URL.replace(/\/$/, '')}/${endpoint.replace(/^\//, '')}`;
    
    // Set headers
    const headers = {
        'Accept': 'application/json',
        ...options.headers
    };

    // If body is not FormData, default to application/json
    if (options.body && !(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        if (typeof options.body === 'object') {
            options.body = JSON.stringify(options.body);
        }
    }

    // Attach Bearer token if it exists
    const token = auth.getToken();
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const fetchOptions = {
        ...options,
        headers
    };

    try {
        const response = await fetch(url, fetchOptions);
        
        // Handle unauthorized status code
        if (response.status === 401) {
            auth.clearSession();
            
            const path = window.location.pathname;
            const segments = path.toLowerCase().split('/').filter(Boolean);
            const page = segments[segments.length - 1] || '';
            const isAuthPage = page === 'login' || page === 'login.html' || page === 'register' || page === 'register.html';
            
            if (!isAuthPage) {
                if (!path.includes('.html')) {
                    window.location.href = '/login?error=session_expired';
                } else {
                    window.location.href = 'login.html?error=session_expired';
                }
            }
            throw new Error('Session expired. Please log in again.');
        }

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            // Throw structured error to allow detail checking (validation errors)
            const error = new Error(data.message || `Request failed with status ${response.status}`);
            error.status = response.status;
            error.data = data; // Contains validation errors or backend message
            throw error;
        }

        return data;
    } catch (err) {
        console.error(`API Error on ${url}:`, err);
        throw err;
    }
}
