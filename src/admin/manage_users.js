let users = [];
let sortAsc = true;

/* -----------------------------
   CREATE ROW
----------------------------- */
function createUserRow(user) {
  const tr = document.createElement("tr");

  const nameTd = document.createElement("td");
  nameTd.textContent = user.name;

  const emailTd = document.createElement("td");
  emailTd.textContent = user.email;

  const adminTd = document.createElement("td");
  adminTd.textContent = user.is_admin === 1 ? "Yes" : "No";

  const actionsTd = document.createElement("td");

  const delBtn = document.createElement("button");
  delBtn.className = "delete-btn";
  delBtn.dataset.id = user.id;
  delBtn.textContent = "Delete";

  const editBtn = document.createElement("button");
  editBtn.className = "edit-btn";
  editBtn.dataset.id = user.id;
  editBtn.textContent = "Edit";

  actionsTd.appendChild(delBtn);
  actionsTd.appendChild(editBtn);

  tr.appendChild(nameTd);
  tr.appendChild(emailTd);
  tr.appendChild(adminTd);
  tr.appendChild(actionsTd);

  return tr;
}

/* -----------------------------
   RENDER TABLE
----------------------------- */
function renderTable(data = []) {
  const tbody = document.getElementById("user-table-body");
  tbody.innerHTML = "";

  data.forEach(user => {
    tbody.appendChild(createUserRow(user));
  });
}

/* -----------------------------
   PASSWORD CHANGE
----------------------------- */
function handleChangePassword(event) {
  event.preventDefault();

  const current = document.getElementById("current-password").value;
  const newPass = document.getElementById("new-password").value;
  const confirm = document.getElementById("confirm-password").value;

  if (newPass.length < 8) {
    alert("New password must be at least 8 characters");
    return;
  }

  if (newPass !== confirm) {
    alert("New passwords do not match");
    return;
  }

  alert("Password changed");

  document.getElementById("current-password").value = "";
  document.getElementById("new-password").value = "";
  document.getElementById("confirm-password").value = "";
}

/* -----------------------------
   ADD USER
----------------------------- */
async function handleAddUser(event) {
  event.preventDefault();

  const name = document.getElementById("user-name").value;
  const email = document.getElementById("user-email").value;
  const password = document.getElementById("default-password").value;
  const isAdmin = document.getElementById("is-admin").value;

  if (!name || !email || !password) {
    alert("Required fields are missing");
    return;
  }

  await fetch("/api/users", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      name,
      email,
      password,
      is_admin: Number(isAdmin)
    })
  });

  alert("User added");
}

/* -----------------------------
   DELETE CLICK
----------------------------- */
function handleTableClick(event) {
  if (event.target.classList.contains("delete-btn")) {
    const id = event.target.dataset.id;

    fetch(`/api/users/${id}`, {
      method: "DELETE"
    });
  }
}

/* -----------------------------
   SEARCH
----------------------------- */
function handleSearch(event) {
  const term = event.target.value.toLowerCase();
  const rows = document.querySelectorAll("#user-table-body tr");

  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(term) ? "" : "none";
  });
}

/* -----------------------------
   SORT
----------------------------- */
function handleSort() {
  users.sort((a, b) => {
    return sortAsc
      ? a.name.localeCompare(b.name)
      : b.name.localeCompare(a.name);
  });

  sortAsc = !sortAsc;
  renderTable(users);
}

/* -----------------------------
   LOAD USERS
----------------------------- */
async function loadUsersAndInitialize() {
  const res = await fetch("/api/users");
  const data = await res.json();

  users = data.users || [];

  renderTable(users);

  document
    .getElementById("password-form")
    .addEventListener("submit", handleChangePassword);

  document
    .getElementById("add-user-form")
    .addEventListener("submit", handleAddUser);

  document
    .getElementById("search-input")
    .addEventListener("input", handleSearch);
}

/* -----------------------------
   INIT
----------------------------- */
document.addEventListener("DOMContentLoaded", loadUsersAndInitialize);
