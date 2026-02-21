<div class="dash-topbar" style="margin-bottom: 24px;">
    <h1>Personel Yönetimi</h1>
    <button class="btn btn-primary" onclick="openUserModal()">+ Yeni Personel / Garson</button>
</div>

<div class="dash-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Kullanıcı Adı</th>
                <th>Yetki (Rol)</th>
                <th>Kayıt Tarihi</th>
                <th style="width: 140px; text-align:right;">İşlemler</th>
            </tr>
        </thead>
        <tbody id="usersTableBody">
            <tr><td colspan="5" style="text-align:center;">Yükleniyor...</td></tr>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal-backdrop" id="userModal">
    <div class="modal-box" style="max-width: 400px;">
        <div class="modal-box-header">
            <h3 id="modalTitle">Personel Ekle</h3>
            <button class="btn-icon" onclick="closeUserModal()">✕</button>
        </div>
        <div class="modal-box-body">
            <form id="userForm">
                <input type="hidden" id="userId">
                <div class="form-group">
                    <label class="form-label">Kullanıcı Adı</label>
                    <input type="text" id="username" class="form-input" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">Şifre</label>
                    <input type="password" id="password" class="form-input" placeholder="Şifrenizi giriniz...">
                    <small id="passwordHint" style="color:var(--text-muted); display:none; margin-top:4px;">Değiştirmek istemiyorsanız boş bırakın</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Rol / Yetki</label>
                    <select id="role" class="form-select" required>
                        <option value="waiter">Garson (Garson Paneli Görebilir)</option>
                        <option value="admin">Yönetici/Admin (Tüm Sisteme Erişebilir)</option>
                    </select>
                </div>
                <!-- Warning Text -->
                <div style="background:rgba(239,68,68,0.1); padding:10px; border-radius:var(--radius-sm); margin-bottom:15px; font-size:0.85rem; color:var(--danger); border:1px solid rgba(239,68,68,0.2);">
                    Aynı isimle yeni bir sekme/tarayıcıdan giriş yapılabilir. Personel kendi şifresiyle oturum açmalı.
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Kaydet</button>
            </form>
        </div>
    </div>
</div>

<script>
async function loadUsers() {
    try {
        const users = await apiRequest('/users.php');
        const tbody = document.getElementById('usersTableBody');
        
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding: 20px;">Kullanıcı bulunamadı.</td></tr>';
            return;
        }

        tbody.innerHTML = users.map(u => {
            const roleBadge = u.role === 'admin' 
                ? '<span style="background:rgba(59,130,246,0.15); color:#3b82f6; padding:4px 10px; border-radius:20px; font-size:0.75rem; font-weight:700;">Admin</span>' 
                : '<span style="background:rgba(34,197,94,0.15); color:#22c55e; padding:4px 10px; border-radius:20px; font-size:0.75rem; font-weight:700;">Garson</span>';
            
            return `
            <tr>
                <td>#${u.id}</td>
                <td><strong>${u.username}</strong></td>
                <td>${roleBadge}</td>
                <td style="color:var(--text-muted); font-size:0.85rem;">${u.created_at}</td>
                <td style="text-align:right;">
                    <button class="btn btn-sm btn-secondary" onclick='editUser(${JSON.stringify(u)})'>✏️ Düzenle</button>
                    <button class="btn btn-sm" style="background:rgba(239,68,68,0.1); color:#ef4444;" onclick="deleteUser(${u.id})">🗑️</button>
                </td>
            </tr>`;
        }).join('');
    } catch(e) {
        showToast('Kullanıcılar yüklenemedi!', 'error');
    }
}

function openUserModal() {
    document.getElementById('modalTitle').textContent = 'Yeni Personel Ekle';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passwordHint').style.display = 'none';
    document.getElementById('userModal').classList.add('show');
}

function editUser(user) {
    document.getElementById('modalTitle').textContent = 'Personeli Düzenle';
    document.getElementById('userId').value = user.id;
    document.getElementById('username').value = user.username;
    
    const roleOpt = document.querySelector('#role option[value="'+user.role+'"]');
    if (roleOpt) roleOpt.selected = true;

    document.getElementById('password').value = '';
    document.getElementById('password').required = false; 
    document.getElementById('passwordHint').style.display = 'block';
    
    document.getElementById('userModal').classList.add('show');
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('show');
}

document.getElementById('userForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('userId').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;

    const data = { username, role };
    if (password) data.password = password;

    try {
        let res;
        if (id) {
            res = await apiRequest('/users.php?id=' + id, 'PATCH', data);
        } else {
            res = await apiRequest('/users.php', 'POST', data);
        }

        if (res.error) throw new Error(res.error);

        showToast('Personel kaydedildi!');
        closeUserModal();
        loadUsers();
    } catch (err) {
        showToast(err.message, 'error');
    }
});

async function deleteUser(id) {
    if(!confirm('Bu kullanıcıyı/garsonu gerçekten silmek istiyor musunuz?')) return;
    try {
        const res = await apiRequest('/users.php?id=' + id, 'DELETE');
        if (res.error) throw new Error(res.error);
        showToast('Personel silindi!');
        loadUsers();
    } catch(err) {
        showToast(err.message, 'error');
    }
}

// init
document.addEventListener('DOMContentLoaded', loadUsers);
</script>
