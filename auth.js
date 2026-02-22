// =============================================
//    TRUCK KOI — AUTH FORM VALIDATION
// =============================================

document.addEventListener('DOMContentLoaded', function () {

    // Login Form Validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;

            // Basic validation
            if (!validatePhone(phone)) {
                showError('ফোন নম্বর সঠিক নয়');
                return;
            }

            if (password.length < 6) {
                showError('পাসওয়ার্ড কমপক্ষে ৬ অক্ষর হতে হবে');
                return;
            }

            // If validation passes, submit form
            // For now, redirect to dashboard (in production, this would authenticate)
            window.location.href = 'dashboard.html';
        });
    }

    // Registration Form Validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const name = document.getElementById('name').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.querySelector('input[name="terms"]').checked;

            // Validation
            if (name.trim().length < 3) {
                showError('নাম কমপক্ষে ৩ অক্ষর হতে হবে');
                return;
            }

            if (!validatePhone(phone)) {
                showError('ফোন নম্বর সঠিক নয়');
                return;
            }

            if (password.length < 6) {
                showError('পাসওয়ার্ড কমপক্ষে ৬ অক্ষর হতে হবে');
                return;
            }

            if (password !== confirmPassword) {
                showError('পাসওয়ার্ড মিলছে না');
                return;
            }

            if (!terms) {
                showError('শর্তাবলী সম্মত হতে হবে');
                return;
            }

            // If validation passes
            showSuccess('নিবন্ধন সফল হয়েছে!');
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1500);
        });
    }

    // Phone number validation
    function validatePhone(phone) {
        // Bangladesh phone number validation (01XXXXXXXXX)
        const phoneRegex = /^01[0-9]{9}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    }

    // Toggle Password Visibility
    window.togglePassword = function (inputId, iconEl) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            iconEl.classList.remove('fa-eye');
            iconEl.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            iconEl.classList.remove('fa-eye-slash');
            iconEl.classList.add('fa-eye');
        }
    };

    // Clear all forms on load (User requirement: box never full with any name/phone)
    window.onload = function () {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.reset();
            const inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"])');
            inputs.forEach(input => {
                input.value = '';
            });
        });
    };

    // Show error message
    function showError(message) {
        // Remove existing alerts
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        const alert = document.createElement('div');
        alert.className = 'alert alert-error';
        alert.textContent = message;

        const form = document.querySelector('.auth-form') || document.querySelector('form');
        if (form) {
            form.insertBefore(alert, form.firstChild);
        }

        setTimeout(() => {
            alert.remove();
        }, 4000);
    }

    // Show success message
    function showSuccess(message) {
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        const alert = document.createElement('div');
        alert.className = 'alert alert-success';
        alert.textContent = message;

        const form = document.querySelector('.auth-form') || document.querySelector('form');
        if (form) {
            form.insertBefore(alert, form.firstChild);
        }

        setTimeout(() => {
            alert.remove();
        }, 4000);
    }

    // Add alert styles dynamically
    const style = document.createElement('style');
    style.textContent = `
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }
        
        .alert-error {
            background: #FFEBEE;
            color: #C62828;
            border: 1px solid #EF5350;
        }
        
        .alert-success {
            background: #E8F5E9;
            color: #2E7D32;
            border: 1px solid #66BB6A;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
});
