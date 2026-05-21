let users = [];
let fullUsersList = [];
let currentSortColumn = null;
let currentSortDir = 'asc';

const API_USERS = 'src/admin/users.php';

function renderTable(usersArray) {
    const tbody = document.getElementById('user-table-body');
    if (!tbody) return;
    tbody.innerHTML = '';
    usersArray.forEach(user => {
        const row = tbody.insertRow();
        row.insertCell(0).textContent = user.name;
        row.insertCell(1).textContent = user.email;
        row.insertCell(2).textContent = user.is_admin ? 'Admin' : 'Student';
        const actionsCell = row.insertCell(3);
        const editBtn = document.createElement('button');
        editBtn.textContent = 'Edit';
        editBtn.className = 'edit-btn';
        editBtn.setAttribute('data-id', user.id);
        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Delete';
        deleteBtn.className = 'delete-btn';
        deleteBtn.setAttribute('data-id', user.id);
        actionsCell.appendChild(editBtn);
        actionsCell.appendChild(deleteBtn);
    });
}

window.handleChangePassword = function(event) {
    event.preventDefault();
    const current = document.getElementById('current-password').value;
    const newPwd = document.getElementById('new-password').value;
    const confirm = document.getElementById('confirm-password').value;
    if (!current || !newPwd || !confirm) {
        alert('Please fill out all password fields.');
        return;
    }
    if (newPwd.length < 8) {
        alert('New password must be at least 8 characters.');
        return;
    }
    if (newPwd !== confirm) {
        alert('New password and confirmation do not match.');
        return;
    }
    alert('Password changed successfully (simulated).');
    document.getElementById('password-form').reset();
};

window.handleAddUser = function(event) {
    event.preventDefault();
    const name = document.getElementById('user-name').value.trim();
    const email = document.getElementById('user-email').value.trim();
    const password = document.getElementById('default-password').value;
    const isAdmin = document.getElementById('is-admin').value === '1' ? 1 : 0;

    if (!name || !email || !password) {
        alert('Please fill out all required fields.');
        return;
    }
    if (password.length < 8) {
        alert('Password must be at least 8 characters.');
        return;
    }

    fetch(API_USERS, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, password, is_admin: isAdmin })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Failed to add user');
        alert(data.message || 'User added successfully');
        loadUsersAndInitialize();
        document.getElementById('add-user-form').reset();
    })
    .catch(err => alert(err.message));
};

window.handleTableClick = function(event) {
    const target = event.target;
    if (target.classList.contains('delete-btn')) {
        const userId = target.getAttribute('data-id');
        if (!confirm('Are you sure?')) return;
        fetch(`${API_USERS}?id=${userId}`, { method: 'DELETE' })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Delete failed');
            users = users.filter(u => u.id != userId);
            fullUsersList = [...users];
            renderTable(users);
            alert(data.message || 'User deleted');
        })
        .catch(err => alert(err.message));
    }
    else if (target.classList.contains('edit-btn')) {
        const userId = target.getAttribute('data-id');
        const user = users.find(u => u.id == userId);
        if (!user) return;
        const newName = prompt('Edit name:', user.name);
        if (newName && newName.trim()) {
            const newEmail = prompt('Edit email:', user.email);
            if (newEmail && newEmail.trim()) {
                const isAdmin = confirm('Make admin? OK=Admin, Cancel=Student');
                fetch(API_USERS, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userId, name: newName.trim(), email: newEmail.trim(), is_admin: isAdmin ? 1 : 0 })
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Update failed');
                    loadUsersAndInitialize();
                    alert(data.message || 'User updated');
                })
                .catch(err => alert(err.message));
            }
        }
    }
};

window.handleSearch = function(event) {
    const term = document.getElementById('search-input').value.toLowerCase().trim();
    if (term === '') {
        renderTable(fullUsersList);
    } else {
        const filtered = fullUsersList.filter(u => u.name.toLowerCase().includes(term) || u.email.toLowerCase().includes(term));
        renderTable(filtered);
    }
};

window.handleSort = function(event) {
    const th = event.currentTarget;
    const colIndex = th.cellIndex;
    let prop;
    if (colIndex === 0) prop = 'name';
    else if (colIndex === 1) prop = 'email';
    else if (colIndex === 2) prop = 'is_admin';
    else return;

    if (currentSortColumn === prop) {
        currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortColumn = prop;
        currentSortDir = 'asc';
    }

    const sorted = [...fullUsersList].sort((a, b) => {
        let valA = a[prop];
        let valB = b[prop];
        if (prop === 'is_admin') {
            valA = Number(valA);
            valB = Number(valB);
            if (valA < valB) return currentSortDir === 'asc' ? -1 : 1;
            if (valA > valB) return currentSortDir === 'asc' ? 1 : -1;
            return 0;
        } else {
            const cmp = String(valA).localeCompare(String(valB));
            return currentSortDir === 'asc' ? cmp : -cmp;
        }
    });
    renderTable(sorted);
};

window.loadUsersAndInitialize = async function() {
    try {
        const res = await fetch(API_USERS);
        if (!res.ok) throw new Error('Failed to fetch users');
        const result = await res.json();
        users = result.data || [];
        fullUsersList = [...users];
        renderTable(users);

        const pwdForm = document.getElementById('password-form');
        const addForm = document.getElementById('add-user-form');
        const tableBody = document.getElementById('user-table-body');
        const search = document.getElementById('search-input');
        const headers = document.querySelectorAll('#user-table thead th');

        if (pwdForm) {
            pwdForm.removeEventListener('submit', window.handleChangePassword);
            pwdForm.addEventListener('submit', window.handleChangePassword);
        }
        if (addForm) {
            addForm.removeEventListener('submit', window.handleAddUser);
            addForm.addEventListener('submit', window.handleAddUser);
        }
        if (tableBody) {
            tableBody.removeEventListener('click', window.handleTableClick);
            tableBody.addEventListener('click', window.handleTableClick);
        }
        if (search) {
            search.removeEventListener('input', window.handleSearch);
            search.addEventListener('input', window.handleSearch);
        }
        headers.forEach(th => {
            th.removeEventListener('click', window.handleSort);
            th.addEventListener('click', window.handleSort);
        });
    } catch (err) {
        console.error(err);
        alert('Error loading users: ' + err.message);
    }
};

document.addEventListener('DOMContentLoaded', window.loadUsersAndInitialize);
