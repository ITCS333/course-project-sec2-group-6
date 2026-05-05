// --- Element Selections ---
const loginForm = document.getElementById("login-form");
const emailInput = document.getElementById("email");
const passwordInput = document.getElementById("password");
const messageContainer = document.getElementById("message-container");

// --- Helper: show message ---
function showMessage(message, isSuccess = false) {
    messageContainer.textContent = message;
    messageContainer.style.color = isSuccess ? "green" : "red";
}

// --- Handle Login ---
async function handleLogin(event) {
    event.preventDefault();

    const email = emailInput.value.trim();
    const password = passwordInput.value;

    // --- Client-side validation ---
    if (!email || !password) {
        showMessage("Please fill in all fields");
        return;
    }

    if (!email.includes("@")) {
        showMessage("Invalid email format");
        return;
    }

    if (password.length < 8) {
        showMessage("Password must be at least 8 characters");
        return;
    }

    try {
        const res = await fetch("../api/index.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ email, password })
        });

        const data = await res.json();

        if (data.success) {
            showMessage("Login successful", true);
            // ممكن تحويل الصفحة لاحقاً
            // window.location.href = "dashboard.html";
        } else {
            showMessage(data.message || "Login failed");
        }

    } catch (error) {
        showMessage("Server error");
    }
}

// --- Event Listener ---
loginForm.addEventListener("submit", handleLogin);
