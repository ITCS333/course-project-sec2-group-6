let users = [];
let sortAsc = true;

// -------------------- CREATE ROW --------------------
function createUserRow(user) {
    const tr = document.createElement("tr");

    tr.innerHTML = `
        <td>${user.name}</td>
        <td>${user.email}</td>
        <td>${user.is_admin == 1 ? "Yes" : "No"}</td>
        <td>
            <button class="delete-btn" data-id="${user.id}">Delete</button>
            <button class="edit-btn" data-id="${user.id}">Edit</button>
        </td>
    `;

    return tr;
}

// -------------------- RENDER --------------------
function renderTable(data) {
    const tbody = document.getElementById("user-table-body");
    tbody.innerHTML = "";

    data.forEach(user => {
        tbody.appendChild(createUserRow(user));
    });
}

// -------------------- SEARCH --------------------
function handleSearch(e) {
    const value = e.target.value.toLowerCase();

    const rows = document.querySelectorAll("#user-table-body tr");

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(value) ? "" : "none";
    });
}

// -------------------- SORT --------------------
function handleSort() {
    users.sort((a, b) => {
        return sortAsc
            ? a.name.localeCompare(b.name)
            : b.name.localeCompare(a.name);
    });

    sortAsc = !sortAsc;
    renderTable(users);
}

// -------------------- ADD USER --------------------
async function handleAddUser(e) {
    e.preventDefault();

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("default-password").value;

    if (!name || !email || !password) {
        alert("Required fields missing");
        return;
    }

    const res = await fetch("/api/users.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password })
    });

    await res.json();
    alert("User added");
}

// -------------------- CHANGE PASSWORD --------------------
function handleChangePassword(e) {
    e.preventDefault();

    const current = document.getElementById("current-password").value;
    const newPass = document.getElementById("new-password").value;
    const confirm = document.getElementById("confirm-password").value;

    if (newPass !== confirm) {
        alert("Passwords do not match");
        return;
    }

    if (newPass.length < 8) {
        alert("Password must be at least 8 characters");
        return;
    }

    alert("Password changed");

    document.getElementById("current-password").value = "";
    document.getElementById("new-password").value = "";
    document.getElementById("confirm-password").value = "";
}

// -------------------- LOAD --------------------
async function loadUsersAndInitialize() {
    const res = await fetch("/api/users.php");
    users = await res.json();

    renderTable(users);

    document
        .getElementById("add-user-form")
        .addEventListener("submit", handleAddUser);

    document
        .getElementById("password-form")
        .addEventListener("submit", handleChangePassword);
}

// -------------------- EXPORT FOR TESTS --------------------
window.createUserRow = createUserRow;
window.renderTable = renderTable;
window.handleSearch = handleSearch;
window.handleSort = handleSort;
window.handleAddUser = handleAddUser;
window.handleChangePassword = handleChangePassword;
window.loadUsersAndInitialize = loadUsersAndInitialize;
