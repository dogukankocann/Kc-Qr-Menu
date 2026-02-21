<?php
/**
 * Dashboard — Masalar ve QR Kodlar
 */
?>
<div class="dash-topbar">
    <h1>Masalar</h1>
    <button class="btn btn-primary" onclick="openTableForm()">+ Yeni Masa</button>
</div>

<div class="tables-grid" id="tablesGrid">
    <!-- JS ile doldurulacak -->
</div>

<!-- Masa Formu Modal -->
<div class="modal-backdrop" id="tableFormModal">
    <div class="modal-box" style="max-width:400px">
        <div class="modal-box-header">
            <h3 id="tableFormTitle">Yeni Masa</h3>
            <button class="btn-icon" onclick="closeTableForm()">✕</button>
        </div>
        <div class="modal-box-body">
            <input type="hidden" id="editTableId">
            <div class="form-group">
                <label class="form-label">Masa Numarası <span class="required">*</span></label>
                <input type="number" class="form-input" id="tNumber" placeholder="1">
            </div>
            <div class="form-group">
                <label class="form-label">Masa Adı (opsiyonel)</label>
                <input type="text" class="form-input" id="tName" placeholder="VIP 1, Bahçe 3...">
            </div>
        </div>
        <div class="modal-box-footer">
            <button class="btn btn-secondary" onclick="closeTableForm()">İptal</button>
            <button class="btn btn-primary" onclick="saveTable()">Kaydet</button>
        </div>
    </div>
</div>

<style>
.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    padding: 20px 0;
}

.table-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: var(--transition);
    position: relative;
}

.table-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
}

.table-card .table-number {
    font-size: 2rem;
    font-weight: 800;
    font-family: var(--font-display);
    color: var(--primary);
}

.table-card .table-name {
    color: var(--text-muted);
    font-size: 0.85rem;
    margin-top: 4px;
}

.table-card .table-actions {
    display: flex;
    gap: 6px;
    justify-content: center;
    margin-top: 14px;
}

.table-card .table-actions .btn {
    padding: 6px 12px;
    font-size: 0.75rem;
}

.table-card.inactive {
    opacity: 0.45;
}

.empty-tables {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted);
}
.empty-tables .emoji { font-size: 3rem; margin-bottom: 10px; }
</style>



<script>
let tables = [];

async function loadTables() {
    try {
        tables = await apiRequest('/tables.php');
        renderTables();
    } catch (e) {
        showToast('Masalar yüklenemedi', 'error');
    }
}

function renderTables() {
    const grid = document.getElementById('tablesGrid');

    if (tables.length === 0) {
        grid.innerHTML = '<div class="empty-tables"><div class="emoji">🪑</div><h3>Henüz masa yok</h3><p>Yeni bir masa ekleyin</p><br><button class="btn btn-primary" onclick="openTableForm()">+ İlk Masayı Ekle</button></div>';
        return;
    }

    grid.innerHTML = tables.map(t => {
        const isActive = t.is_active == 1;
        return '<div class="table-card ' + (isActive ? '' : 'inactive') + '">' +
            '<div class="table-number">Masa ' + t.table_number + '</div>' +
            (t.name ? '<div class="table-name">' + escapeHtml(t.name) + '</div>' : '') +
            '<div style="margin-top:8px"><label class="toggle" style="margin:0 auto"><input type="checkbox" ' + (isActive ? 'checked' : '') + ' onchange="toggleTable(' + t.id + ', this.checked)"><span class="slider"></span></label></div>' +
            '<div class="table-actions">' +
                '<button class="btn btn-secondary" onclick="editTable(' + t.id + ')">✏️</button>' +
                '<button class="btn btn-secondary" onclick="deleteTable(' + t.id + ')" style="color:var(--danger)">🗑️</button>' +
            '</div>' +
        '</div>';
    }).join('');
}

// Form
function openTableForm(table = null) {
    document.getElementById('tableFormModal').classList.add('show');
    document.getElementById('tableFormTitle').textContent = table ? 'Masayı Düzenle' : 'Yeni Masa';
    document.getElementById('editTableId').value = table ? table.id : '';
    document.getElementById('tNumber').value = table ? table.table_number : '';
    document.getElementById('tName').value = table ? (table.name || '') : '';
}

function closeTableForm() {
    document.getElementById('tableFormModal').classList.remove('show');
}

async function saveTable() {
    const id = document.getElementById('editTableId').value;
    const data = {
        table_number: parseInt(document.getElementById('tNumber').value),
        name: document.getElementById('tName').value || null,
    };

    if (!data.table_number) {
        showToast('Masa numarası zorunludur!', 'error');
        return;
    }

    try {
        if (id) {
            await apiRequest('/table.php?id=' + id, 'PUT', data);
            showToast('Masa güncellendi!');
        } else {
            await apiRequest('/tables.php', 'POST', data);
            showToast('Masa eklendi!');
        }
        closeTableForm();
        loadTables();
    } catch (e) {
        showToast('Kaydetme hatası', 'error');
    }
}

function editTable(id) {
    const t = tables.find(x => x.id == id);
    if (t) openTableForm(t);
}

async function deleteTable(id) {
    if (!confirm('Bu masayı silmek istediğinize emin misiniz?')) return;
    try {
        await apiRequest('/table.php?id=' + id, 'DELETE');
        showToast('Masa silindi!');
        loadTables();
    } catch (e) {
        showToast('Silme hatası', 'error');
    }
}

async function toggleTable(id, active) {
    try {
        await apiRequest('/table.php?id=' + id, 'PATCH', { is_active: active ? 1 : 0 });
        showToast(active ? 'Masa aktif' : 'Masa pasif');
        loadTables();
    } catch (e) {
        showToast('Güncelleme hatası', 'error');
    }
}



function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

loadTables();
</script>
