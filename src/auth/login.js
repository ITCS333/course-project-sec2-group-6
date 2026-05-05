/*
  Requirement: Add client-side validation to the login form.

  Instructions:
  1. This file is already linked to your HTML via a <script> tag with the 'defer' attribute
     at the bottom of the <body> in login.html.
  
  2. In your login.html, a <div id="message-container"> has been added *after* the </fieldset>
     but *before* the </form> closing tag. This div will be used to display success or error messages.
  
  3. Implement the JavaScript functionality as described in the TODO comments.
*/

// --- Element Selections ---
// We can safely select elements here because 'defer' guarantees
// the HTML document is parsed before this script runs.

// Select the login form by its id "login-form"
const loginForm = document.getElementById('login-form');

// Select the email input element by its ID
const emailInput = document.getElementById('email');

// Select the password input element by its ID
const passwordInput = document.getElementById('password');

// Select the message container element by its ID
const messageContainer = document.getElementById('message-container');

// Helper function to display messages
function showMessage(message, isError = true) {
    if (messageContainer) {
        messageContainer.textContent = message;
        messageContainer.style.color = isError ? '#d32f2f' : '#2e7d32';
        messageContainer.style.fontSize = '0.9rem';
        messageContainer.style.marginTop = '0.5rem';
    }
}

// Add submit event listener to the form
if (loginForm) {
    loginForm.addEventListener('submit', function(event) {
        // Clear any previous message
        if (messageContainer) messageContainer.textContent = '';

        // Get trimmed values
        const email = emailInput ? emailInput.value.trim() : '';
        const password = passwordInput ? passwordInput.value : '';

        // Validation flags
        let isValid = true;
        let errorMessage = '';

        // Email validation: not empty and basic email format
        if (email === '') {
            isValid = false;
            errorMessage = 'Email address is required.';
        } else if (!/^\S+@\S+\.\S+$/.test(email)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address (e.g., name@domain.com).';
        }
        // Password validation: not empty
        else if (password === '') {
            isValid = false;
            errorMessage = 'Password is required.';
        }

        // If invalid, prevent form submission and show error
        if (!isValid) {
            event.preventDefault();
            showMessage(errorMessage, true);
        } else {
            // Optional success message (will be replaced after actual submission)
            showMessage('Logging in...', false);
            // The form will now submit to the server for actual authentication
        }
    });
}
