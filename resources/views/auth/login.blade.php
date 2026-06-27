<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <title>Login - Product API Manager</title>
    
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <!-- Auth configuration scripts -->
    <script src="{{ asset('js/config.js') }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script src="{{ asset('js/api.js') }}"></script>
</head>
<body class="d-flex flex-column auth-page-container">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="#" class="navbar-brand navbar-brand-autodark">
                    <span class="fs-1 fw-bold text-primary"><i class="ti ti-package-import me-2"></i>Product API</span>
                </a>
            </div>
            
            <div class="card card-md card-animate">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Login to your account</h2>
                    
                    <!-- Alert Banners -->
                    <div id="alert-container"></div>

                    <form id="login-form" method="POST" autocomplete="off" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input type="email" id="email" class="form-control" placeholder="your@email.com" autocomplete="off" required>
                            <div class="invalid-feedback" id="email-error">Please enter a valid email address.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group input-group-flat">
                                <input type="password" id="password" class="form-control" placeholder="Your password" autocomplete="off" required>
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" id="toggle-password" title="Show password" data-bs-toggle="tooltip">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </span>
                                <div class="invalid-feedback" id="password-error">Please enter your password.</div>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="submit" id="submit-btn" class="btn btn-primary w-100">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status" id="submit-spinner"></span>
                                Sign in
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('toggle-password');
            const submitBtn = document.getElementById('submit-btn');
            const submitSpinner = document.getElementById('submit-spinner');
            const alertContainer = document.getElementById('alert-container');

            // Handle session expired redirection message
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('error') === 'session_expired') {
                showAlert('Your session has expired. Please sign in again.', 'warning');
            }

            // Password visibility toggle
            togglePassword.addEventListener('click', (e) => {
                e.preventDefault();
                const icon = togglePassword.querySelector('i');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.replace('ti-eye', 'ti-eye-off');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.replace('ti-eye-off', 'ti-eye');
                }
            });

            // Helper to render alerts
            function showAlert(message, type = 'danger') {
                alertContainer.innerHTML = `
                    <div class="alert alert-${type} alert-dismissible" role="alert">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-${type === 'success' ? 'circle-check' : (type === 'warning' ? 'alert-triangle' : 'alert-circle')} alert-icon me-2"></i>
                            </div>
                            <div>${message}</div>
                        </div>
                        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                    </div>
                `;
            }

            // Form Submit Logic
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                alertContainer.innerHTML = '';
                emailInput.classList.remove('is-invalid');
                passwordInput.classList.remove('is-invalid');
                
                let hasError = false;

                if (!emailInput.value.trim()) {
                    emailInput.classList.add('is-invalid');
                    document.getElementById('email-error').textContent = 'Email address is required.';
                    hasError = true;
                }
                
                if (!passwordInput.value) {
                    passwordInput.classList.add('is-invalid');
                    document.getElementById('password-error').textContent = 'Password is required.';
                    hasError = true;
                }

                if (hasError) return;

                // Toggle loading state
                submitBtn.disabled = true;
                submitSpinner.classList.remove('d-none');

                try {
                    const response = await apiRequest('/login', {
                        method: 'POST',
                        body: {
                            email: emailInput.value.trim(),
                            password: passwordInput.value
                        }
                    });

                    if (response.success && response.data) {
                        const token = response.data.access_token;
                        const user = response.data.user;
                        
                        auth.saveSession(token, user);
                        
                        showAlert('Login successful! Redirecting...', 'success');
                        
                        setTimeout(() => {
                            window.location.href = '/dashboard';
                        }, 1000);
                    } else {
                        showAlert(response.message || 'Login failed', 'danger');
                        submitBtn.disabled = false;
                        submitSpinner.classList.add('d-none');
                    }
                } catch (error) {
                    submitBtn.disabled = false;
                    submitSpinner.classList.add('d-none');

                    if (error.data && error.data.data) {
                        const validationErrors = error.data.data;
                        if (validationErrors.email) {
                            emailInput.classList.add('is-invalid');
                            document.getElementById('email-error').textContent = validationErrors.email.join(' ');
                        }
                        if (validationErrors.password) {
                            passwordInput.classList.add('is-invalid');
                            document.getElementById('password-error').textContent = validationErrors.password.join(' ');
                        }
                        showAlert('Please fix the validation errors.', 'danger');
                    } else {
                        showAlert(error.message || 'An error occurred during login. Please try again.', 'danger');
                    }
                }
            });
        });
    </script>
</body>
</html>
