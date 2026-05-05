let users = [];

const userTableBody = document.getElementById("user-table-body");
const addUserForm = document.getElementById("add-user-form");
const passwordForm = document.getElementById("password-form");
const searchInput = document.getElementById("search-input");
const tableHeaders = document.querySelectorAll("#user-table thead th");

function createUserRow(user) {
    const tr = document.createElement("tr");

    tr.innerHTML = `
        <td>${user.name}</td>
        <td>${user.email}</td>
        <td>${user.is_admin == 1 ? "Yes" : "No"}</td>
        <td>
            <button class="edit-btn" data-id="${user.id}">Edit</button>
            <button class="delete-btn" data-id="${user.id}">Delete</button>
        </td>
    `;

    return tr;
}

function renderTable(userArray) {
    userTableBody.innerHTML = "";
    userArray.forEach(user => {
        userTableBody.appendChild(createUserRow(user));
    });
}

async function handleChangePassword(event) {
    event.preventDefault();

    const current_password = document.getElementById("current-password").value;
    const new_password = document.getElementById("new-password").value;
    const confirm_password = document.getElementById("confirm-password").value;

    if (new_password !== confirm_password) {
        alert("Passwords do not match.");
        return;
    }

    if (new_password.length < 8) {
        alert("Password must be at least 8 characters.");
        return;
    }

    const res = await fetch("../api/index.php?action=change_password", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            id: 1,
            current_password,
            new_password
        })
    });

    const data = await res.json();

    if (data.success) {
        alert("Password updated successfully!");
        passwordForm.reset();
    } else {
        alert(data.message || "Error");
    }
}

async function handleAddUser(event) {
    event.preventDefault();

    const name = document.getElementById("user-name").value;
    const email = document.getElementById("user-email").value;
    const password = document.getElementById("default-password").value;
    const is_admin = document.getElementById("is-admin").value;

    if (!name || !email || !password) {
        alert("Please fill out all required fields.");
        return;
    }

    if (password.length < 8) {
        alert("Password must be at least 8 characters.");
        return;
    }

    const res = await fetch("../api/index.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password, is_admin })
    });

    const data = await res.json();

    if (res.status === 201 || data.success) {
        await loadUsersAndInitialize();
        addUserForm.reset();
    } else {
        alert(data.message || "Error");
    }
}

async function handleTableClick(event) {
    const id = event.target.dataset.id;

    if (event.target.classList.contains("delete-btn")) {
        const res = await fetch("../api/index.php?id=" + id, {
            method: "DELETE"
        });

        const data = await res.json();

        if (data.success) {
            users = users.filter(u => u.id != id);
            renderTable(users);
        } else {
            alert(data.message || "Error");
        }
    }

    if (event.target.classList.contains("edit-btn")) {
        alert("Edit clicked for user " + id);
    }
}

function handleSearch() {
    const term = searchInput.value.toLowerCase();

    if (!term) {
        renderTable(users);
        return;
    }

    const filtered = users.filter(u =>
        u.name.toLowerCase().includes(term) ||
        u.email.toLowerCase().includes(term)
    );

    renderTable(filtered);
}

function handleSort(event) {
    const index = event.currentTarget.cellIndex;

    let key = "";
    if (index === 0) key = "name";
    if (index === 1) key = "email";
    if (index === 2) key = "is_admin";

    let dir = event.currentTarget.dataset.sortDir || "asc";
    dir = dir === "asc" ? "desc" : "asc";
    event.currentTarget.dataset.sortDir = dir;

    users.sort((a, b) => {
        let valA = a[key];
        let valB = b[key];

        if (key === "is_admin") {
            valA = Number(valA);
            valB = Number(valB);
        } else {
            valA = valA.toString().toLowerCase();
            valB = valB.toString().toLowerCase();
            return dir === "asc"
                ? valA.localeCompare(valB)
                : valB.localeCompare(valA);
        }

        return dir === "asc" ? valA - valB : valB - valA;
    });

    renderTable(users);
}

async function loadUsersAndInitialize() {
    const res = await fetch("../api/index.php");

    if (!res.ok) {
        alert("Failed to load users");
        return;
    }

    const data = await res.json();

    if (data.success) {
        users = data.data;
        renderTable(users);
    }

    addUserForm.addEventListener("submit", handleAddUser);
    passwordForm.addEventListener("submit", handleChangePassword);
    userTableBody.addEventListener("click", handleTableClick);
    searchInput.addEventListener("input", handleSearch);

    tableHeaders.forEach(th => {
        th.addEventListener("click", handleSort);
    });
}

loadUsersAndInitialize();
