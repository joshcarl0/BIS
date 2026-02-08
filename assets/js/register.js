document.addEventListener('DOMContentLoaded', () => {

  const togglePassword = document.getElementById('toggle-password');
  const password = document.getElementById('password');

  const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
  const confirmPassword = document.getElementById('confirm_password');

  if (togglePassword && password) {
    togglePassword.addEventListener('click', () => {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      togglePassword.innerHTML = type === 'password'
        ? '<i class="bi bi-eye-slash"></i>'
        : '<i class="bi bi-eye"></i>';
    });
  }

  if (toggleConfirmPassword && confirmPassword) {
    toggleConfirmPassword.addEventListener('click', () => {
      const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
      confirmPassword.setAttribute('type', type);
      toggleConfirmPassword.innerHTML = type === 'password'
        ? '<i class="bi bi-eye-slash"></i>'
        : '<i class="bi bi-eye"></i>';
    });
  }

  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', (e) => {
      const fullname = document.getElementById('fullname')?.value.trim();
      const username = document.getElementById('username')?.value.trim();
      const email = document.getElementById('email')?.value.trim();
      const pwd = password?.value;
      const confirm = confirmPassword?.value;

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
      if (pwd.length < 8 || !/[A-Z]/.test(pwd) || !/[0-9]/.test(pwd) || !/[!@#$%^&*]/.test(pwd)) {
        alert('Password must be 8+ chars, with uppercase, number, and special character (!@#$%^&*)');
        e.preventDefault();
        return;
      }
    });
  }

});
