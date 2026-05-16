// Global variables
let users = [];
let fullUsersList = [];

// DOM elements
const changePasswordForm = document.getElementById('password-form');
const addUserForm = document.getElementById('add-user-form');
const userTableBody = document.getElementById('user-table-body');
const searchInput = document.getElementById('search-input');
const tableHeaders = document.querySelectorAll('#user-table thead th');

// API endpoints
const API_USERS = 'src/admin/users.php';
const API_AUTH = 'src/auth/auth.php';

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function renderTable(usersArray) {
    if (!userTableBody) return;
    userTableBody.innerHTML = '';
    usersArray.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${user.is_admin ? 'Admin' : 'Student'}</td>
            <td>
                <button class="edit-btn" data-id="${user.id}">Edit</button>
                <button class="delete-btn" data-id="${user.id}">Delete</button>
            </td>
        `;
        userTableBody.appendChild(row);
    });
}

function handleChangePassword(event) {
    event.preventDefault();
    const currentPwd = document.getElementById('current-password').value;
    const newPwd = document.getElementById('new-password').value;
    const confirmPwd = document.getElementById('confirm-password').value;

    if (!currentPwd || !newPwd || !confirmPwd) {
        alert('Please fill out all password fields.');
        return;
    }
    if (newPwd.length < 8) {
        alert('New password must be at least 8 characters.');
        return;
    }
    if (newPwd !== confirmPwd) {
        alert('New password and confirmation do not match.');
        return;
    }

    fetch(`${API_AUTH}?action=change_password`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ current_password: currentPwd, new_password: newPwd })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Password change failed');
        alert(data.message || 'Password changed successfully');
        document.getElementById('password-form').reset();
    })
    .catch(err => alert(err.message));
}

function handleAddUser(event) {
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
}

function handleTableClick(event) {
    const target = event.target;
    if (target.classList.contains('delete-btn')) {
        const userId = target.getAttribute('data-id');
        if (!confirm('Are you sure you want to delete this user?')) return;
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
        if (newName && newName.trim() !== '') {
            const newEmail = prompt('Edit email:', user.email);
            if (newEmail && newEmail.trim() !== '') {
                const newRole = confirm('Make admin? Click OK for Admin, Cancel for Student');
                const isAdmin = newRole ? 1 : 0;
                fetch(API_USERS, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: userId, name: newName.trim(), email: newEmail.trim(), is_admin: isAdmin })
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
}

function handleSearch(event) {
    const term = searchInput.value.toLowerCase().trim();
    if (term === '') {
        renderTable(fullUsersList);
    } else {
        const filtered = fullUsersList.filter(user =>
            user.name.toLowerCase().includes(term) ||
            user.email.toLowerCase().includes(term)
        );
        renderTable(filtered);
    }
}

let currentSortColumn = null;
let currentSortDir = 'asc';

function handleSort(event) {
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

    tableHeaders.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
    th.classList.add(currentSortDir === 'asc' ? 'sort-asc' : 'sort-desc');

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
}

async function loadUsersAndInitialize() {
    try {
        const response = await fetch(API_USERS);
        if (!response.ok) {
            const errData = await response.json();
            throw new Error(errData.message || 'Failed to load users');
        }
        const result = await response.json();
        users = result.data || [];
        fullUsersList = [...users];
        renderTable(users);

        if (changePasswordForm) {
            changePasswordForm.removeEventListener('submit', handleChangePassword);
            changePasswordForm.addEventListener('submit', handleChangePassword);
        }
        if (addUserForm) {
            addUserForm.removeEventListener('submit', handleAddUser);
            addUserForm.addEventListener('submit', handleAddUser);
        }
        if (userTableBody) {
            userTableBody.removeEventListener('click', handleTableClick);
            userTableBody.addEventListener('click', handleTableClick);
        }
        if (searchInput) {
            searchInput.removeEventListener('input', handleSearch);
            searchInput.addEventListener('input', handleSearch);
        }
        tableHeaders.forEach(th => {
            th.removeEventListener('click', handleSort);
            th.addEventListener('click', handleSort);
        });
    } catch (err) {
        console.error(err);
        alert('Error loading users: ' + err.message);
    }
}

document.addEventListener('DOMContentLoaded', loadUsersAndInitialize);
