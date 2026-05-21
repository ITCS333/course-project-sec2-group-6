(function() {
    const form = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const messageDiv = document.getElementById('message-container');

    function showMessage(msg, isError = true) {
        if (messageDiv) {
            messageDiv.textContent = msg;
            messageDiv.style.color = isError ? '#d32f2f' : '#2e7d32';
            messageDiv.style.backgroundColor = isError ? '#ffebee' : '#e8f5e9';
            messageDiv.style.padding = '8px';
            messageDiv.style.borderRadius = '4px';
        }
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            if (messageDiv) messageDiv.textContent = '';
            const email = emailInput ? emailInput.value.trim() : '';
            const password = passwordInput ? passwordInput.value : '';
            if (email === '') {
                e.preventDefault();
                showMessage('Email address is required.');
                return;
            }
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                e.preventDefault();
                showMessage('Please enter a valid email address (e.g., name@domain.com).');
                return;
            }
            if (password === '') {
                e.preventDefault();
                showMessage('Password is required.');
                return;
            }
        });
    }
})();
