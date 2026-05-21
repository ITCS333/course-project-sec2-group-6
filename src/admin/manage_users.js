async function loadUsers() {
    const res = await fetch("api/index.php");
    const data = await res.json();

    const tbody = document.getElementById("user-table-body");
    tbody.innerHTML = "";

    data.data.forEach(u => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${u.name}</td>
            <td>${u.email}</td>
            <td>edit | delete</td>
        `;
        tbody.appendChild(row);
    });
}

loadUsers();
