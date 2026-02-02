// register.js

document.addEventListener('DOMContentLoaded', () => {

    // Password Toggle
    const togglePassword = document.getElementById('toggle-password');
    const password = document.getElementById('password');

    const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
    const confirmPassword = document.getElementById('confirm_password');

    togglePassword.addEventListener('click', () => {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        togglePassword.innerHTML = type === 'password' ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });

    toggleConfirmPassword.addEventListener('click', () => {
        const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPassword.setAttribute('type', type);
        toggleConfirmPassword.innerHTML = type === 'password' ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });

    // Registration Form Validation
    const registerForm = document.getElementById('registerForm');
    registerForm.addEventListener('submit', (e) => {
        const fullname = document.getElementById('fullname').value.trim();
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const pwd = password.value;
        const confirm = confirmPassword.value;

        if (!fullname || !username || !email || !pwd || !confirm) {
            alert('All fields are required');
            e.preventDefault();
            return;
        }

        if (pwd !== confirm) {
            alert('Passwords do not match');
            e.preventDefault();
            return;
        }

        if (pwd.length < 8) {
            alert('Password must be at least 8 characters');
            e.preventDefault();
            return;
        }

        if (!/[A-Z]/.test(pwd)) {
            alert('Password must contain at least one uppercase letter');
            e.preventDefault();
            return;
        }

        if (!/[0-9]/.test(pwd)) {
            alert('Password must contain at least one number');
            e.preventDefault();
            return;
        }

        if (!/[!@#$%^&*]/.test(pwd)) {
            alert('Password must contain at least one special character (!@#$%^&*)');
            e.preventDefault();
            return;
        }
    });

});
