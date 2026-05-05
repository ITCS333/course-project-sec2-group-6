// Client-side validation for login form
// Define validation function in global scope
function validateLoginForm(event) {
    const form = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const messageContainer = document.getElementById('message-container');
    
    if (messageContainer) messageContainer.textContent = '';
    
    const email = emailInput ? emailInput.value.trim() : '';
    const password = passwordInput ? passwordInput.value : '';
    
    if (email === '') {
        event.preventDefault();
        if (messageContainer) {
            messageContainer.textContent = 'Email address is required.';
            messageContainer.style.color = '#d32f2f';
        }
        return false;
    }
    
    if (!/^\S+@\S+\.\S+$/.test(email)) {
        event.preventDefault();
        if (messageContainer) {
            messageContainer.textContent = 'Please enter a valid email address (e.g., name@domain.com).';
            messageContainer.style.color = '#d32f2f';
        }
        return false;
    }
    
    if (password === '') {
        event.preventDefault();
        if (messageContainer) {
            messageContainer.textContent = 'Password is required.';
            messageContainer.style.color = '#d32f2f';
        }
        return false;
    }
    
    return true;
}

// Attach event listener when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('login-form');
        if (form) {
            form.addEventListener('submit', validateLoginForm);
        }
    });
} else {
    const form = document.getElementById('login-form');
    if (form) {
        form.addEventListener('submit', validateLoginForm);
    }
}
});
