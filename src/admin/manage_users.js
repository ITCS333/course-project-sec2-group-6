let users = [];
let sortAsc = true;

// ---------------- CREATE ROW ----------------
function createUserRow(user) {
    const tr = document.createElement("tr");

    tr.innerHTML = `
        <td>${user.name}</td>
        <td>${user.email}</td>
        <td>${user.is_admin === 1 ? "Yes" : "No"}</td>
        <td>
            <button class="delete-btn" data-id="${user.id}">Delete</button>
            <button class="edit-btn" data-id="${user.id}">Edit</button>
        </td>
    `;

    return tr;
}

// ---------------- RENDER TABLE ----------------
function renderTable(data) {
    const tbody = document.getElementById("user-table-body");
    tbody.innerHTML = "";

    data.forEach(user => {
        tbody.appendChild(createUserRow(user));
    });
}

// ---------------- ADD USER ----------------
function handleAddUser(e) {
    e.preventDefault();

    const name = document.getElementById("user-name").value;
    const email = document.getElementById("user-email").value;
    const password = document.getElementById("default-password").value;

    if (!name || !email || !password) {
        alert("Required fields missing");
        return;
    }

    fetch("/index.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password })
    });
}

// ---------------- CHANGE PASSWORD ----------------
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

    document.getElementById("current-password").value = "";
    document.getElementById("new-password").value = "";
    document.getElementById("confirm-password").value = "";
}

// ---------------- DELETE + TABLE CLICK ----------------
function handleTableClick(e) {
    if (e.target.classList.contains("delete-btn")) {
        const id = e.target.dataset.id;

        fetch(`/index.php?id=${id}`, {
            method: "DELETE"
        });
    }
}

// ---------------- SEARCH ----------------
function handleSearch(e) {
    const term = e.target.value.toLowerCase();

    const filtered = users.filter(u =>
        u.name.toLowerCase().includes(term) ||
        u.email.toLowerCase().includes(term)
    );

    renderTable(filtered);
}

// ---------------- SORT ----------------
function handleSort() {
    users.sort((a, b) =>
        sortAsc
            ? a.name.localeCompare(b.name)
            : b.name.localeCompare(a.name)
    );

    sortAsc = !sortAsc;
    renderTable(users);
}

// ---------------- LOAD USERS ----------------
async function loadUsersAndInitialize() {
    const res = await fetch("/index.php");
    const data = await res.json();

    users = data.data;
    renderTable(users);

    document.getElementById("add-user-form").addEventListener("submit", handleAddUser);
    document.getElementById("password-form").addEventListener("submit", handleChangePassword);
    document.getElementById("search-input").addEventListener("input", handleSearch);
    document.getElementById("user-table-body").addEventListener("click", handleTableClick);
}

window.onload = loadUsersAndInitialize;
