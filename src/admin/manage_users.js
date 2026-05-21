function displayMessage(message, type) {
  const container = document.getElementById("message-container");
  container.textContent = message;
  container.className = type;
}

function isValidEmail(email) {
  return email.includes("@") && email.includes(".");
}

function isValidPassword(password) {
  return password.length >= 8;
}

function handleLogin(event) {
  event.preventDefault();

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  if (!isValidEmail(email)) {
    displayMessage("Invalid email", "error");
    return;
  }

  if (!isValidPassword(password)) {
    displayMessage("Password too short", "error");
    return;
  }

  displayMessage("Success", "success");
}

function setupLoginForm() {
  const form = document.getElementById("login-form");
  form.addEventListener("submit", handleLogin);
}

setupLoginForm();
