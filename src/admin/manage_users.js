let users = [];

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

  data.forEach(user => {
    tbody.appendChild(createUserRow(user));
  });
}

function handleChangePassword(e) {
  e.preventDefault();
  alert("Password changed");
}

function handleAddUser(e) {
  e.preventDefault();
  alert("User added");
}

function handleTableClick(e) {
  if (e.target.classList.contains("delete-btn")) {
    fetch("/api/users/" + e.target.dataset.id, { method: "DELETE" });
  }
}

function handleSearch() {}

function handleSort() {}

async function loadUsersAndInitialize() {
  const res = await fetch("/api/users");
  users = await res.json();
  renderTable(users);
}
