let users = [];

function handleChangePassword(event) {
    event.preventDefault();

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

function handleAddUser(event) {
    event.preventDefault();

    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const isAdmin = document.getElementById("is_admin").value;

    if (!name || !email || !password) {
        alert("Required fields are missing");
        return;
    }

    fetch("/api/users", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password, is_admin: isAdmin })
    });

    alert("User added");
}

function handleSearch() {
    const search = document.getElementById("search").value.toLowerCase();
    const rows = document.querySelectorAll("#user-table-body tr");

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? "" : "none";
    });
}

function handleSort() {
    users.sort((a, b) => a.name.localeCompare(b.name));
    renderTable(users);
}

function renderTable(data) {
    const tbody = document.getElementById("user-table-body");
    tbody.innerHTML = "";

    data.forEach(user => {
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

        tbody.appendChild(tr);
    });
}

function loadUsersAndInitialize() {
    fetch("/api/users")
        .then(res => res.json())
        .then(data => {
            users = Array.isArray(data) ? data : data.data;

            renderTable(users);

            document.getElementById("add-user-form")
                .addEventListener("submit", handleAddUser);

            document.getElementById("password-form")
                .addEventListener("submit", handleChangePassword);
        });
}
