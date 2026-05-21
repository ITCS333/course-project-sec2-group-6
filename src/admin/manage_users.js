let users = [];
let sortAsc = true;

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

function renderTable(data) {
    const tbody = document.getElementById("user-table-body");
    tbody.innerHTML = "";

    data.forEach(u => {
        tbody.appendChild(createUserRow(u));
    });
}

function handleChangePassword(e) {
    e.preventDefault();

    const current = document.getElementById("current-password");
    const newPass = document.getElementById("new-password");
    const confirm = document.getElementById("confirm-password");

    if (newPass.value !== confirm.value) {
        alert("Passwords do not match");
        return;
    }

    if (newPass.value.length < 8) {
        alert("Password must be at least 8 characters");
        return;
    }

    current.value = "";
    newPass.value = "";
    confirm.value = "";

    alert("Password changed");
}

function handleAddUser(e) {
    e.preventDefault();

    const name = document.getElementById("user-name").value;
    const email = document.getElementById("user-email").value;
    const pass = document.getElementById("default-password").value;
    const admin = document.getElementById("is-admin").value;

    if (!name || !email || !pass) {
        alert("Required fields");
        return;
    }

    fetch("index.php", {
        method: "POST",
        body: JSON.stringify({name, email, password: pass, is_admin: admin})
    });

    alert("User added");
}

function handleSearch() {
    const q = document.getElementById("search-input").value.toLowerCase();

    const rows = document.querySelectorAll("#user-table-body tr");

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? "" : "none";
    });
}

function handleSort() {
    users.sort((a,b) => {
        return sortAsc
            ? a.name.localeCompare(b.name)
            : b.name.localeCompare(a.name);
    });

    sortAsc = !sortAsc;
    renderTable(users);
}

function handleTableClick(e) {
    if (e.target.classList.contains("delete-btn")) {
        fetch("index.php", {
            method: "DELETE"
        });
    }
}

function loadUsersAndInitialize() {
    fetch("index.php")
        .then(res => res.json())
        .then(data => {
            users = data.data || [];
            renderTable(users);
        });

    document.getElementById("password-form")
        .addEventListener("submit", handleChangePassword);

    document.getElementById("add-user-form")
        .addEventListener("submit", handleAddUser);

    document.getElementById("search-input")
        .addEventListener("input", handleSearch);
}

window.onload = loadUsersAndInitialize;
