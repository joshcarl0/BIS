document.addEventListener('DOMContentLoaded', function() {
    
    // Password visibility toggle for any password-container
    document.querySelectorAll('.password-container').forEach(container => {
        const input = container.querySelector('input[type="password"], input');
        const toggleBtn = container.querySelector('.password-toggle');
        if (!input || !toggleBtn) return;

        const icon = toggleBtn.querySelector('i');
        toggleBtn.addEventListener('click', (e) => {
            // Prevent accidental form submission and make debugging easier
            e.preventDefault();
            e.stopPropagation();
            try {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                if (icon) {
                    icon.classList.toggle('bi-eye-slash');
                    icon.classList.toggle('bi-eye');
                }
                // Debug log (visible in browser console)
                console.debug('Password toggle:', input.id || input.name || '(no id)', 'now type=', input.type);
            } catch (err) {
                console.error('Password toggle error:', err);
            }
        });
    });

    // Login form validation
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in both username and password.');
            }
        });
    }

    // Registration form validation (if present)
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const fullname = document.getElementById('fullname').value.trim();
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;

            if (!fullname || !username || !email || !password || !confirm) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }

            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match.');
                return;
            }
        });
    }
});