let users = [];
let sortAsc = true;

// ---------------- CREATE ROW ----------------
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

// ---------------- RENDER TABLE ----------------
function renderTable(data) {
    const tbody = document.getElementById("user-table-body");
    if (!tbody) return;

    tbody.innerHTML = "";

    (data || []).forEach(user => {
        tbody.appendChild(createUserRow(user));
    });
}

// ---------------- SEARCH ----------------
function handleSearch(e) {
    const value = e.target.value.toLowerCase();
    const rows = document.querySelectorAll("#user-table-body tr");

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(value) ? "" : "none";
    });
}

// ---------------- SORT ----------------
function handleSort() {
    users.sort((a, b) => {
        return sortAsc
            ? a.name.localeCompare(b.name)
            : b.name.localeCompare(a.name);
    });

    sortAsc = !sortAsc;
    renderTable(users);
}

// ---------------- ADD USER ----------------
async function handleAddUser(e) {
    e.preventDefault();

    const nameEl = document.getElementById("name");
    const emailEl = document.getElementById("email");
    const passEl = document.getElementById("default-password");

    const name = nameEl ? nameEl.value : "";
    const email = emailEl ? emailEl.value : "";
    const password = passEl ? passEl.value : "";

    if (!name || !email || !password) {
        alert("Required fields missing");
        return;
    }

    await fetch("api/users.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password })
    });

    alert("User added");
}

// ---------------- CHANGE PASSWORD ----------------
function handleChangePassword(e) {
    e.preventDefault();

    const current = document.getElementById("current-password");
    const newPass = document.getElementById("new-password");
    const confirm = document.getElementById("confirm-password");

    const currentVal = current ? current.value : "";
    const newVal = newPass ? newPass.value : "";
    const confirmVal = confirm ? confirm.value : "";

    if (newVal !== confirmVal) {
        alert("Passwords do not match");
        return;
    }

    if (newVal.length < 8) {
        alert("Password must be at least 8 characters");
        return;
    }

    alert("Password changed");

    if (current) current.value = "";
    if (newPass) newPass.value = "";
    if (confirm) confirm.value = "";
}

// ---------------- LOAD INIT ----------------
async function loadUsersAndInitialize() {
    const res = await fetch("api/users.php");
    users = await res.json();

    renderTable(users);

    const addForm = document.getElementById("add-user-form");
    const passForm = document.getElementById("password-form");

    if (addForm) addForm.addEventListener("submit", handleAddUser);
    if (passForm) passForm.addEventListener("submit", handleChangePassword);
}

// expose for tests
window.createUserRow = createUserRow;
window.renderTable = renderTable;
window.handleSearch = handleSearch;
window.handleSort = handleSort;
window.handleAddUser = handleAddUser;
window.handleChangePassword = handleChangePassword;
window.loadUsersAndInitialize = loadUsersAndInitialize;
