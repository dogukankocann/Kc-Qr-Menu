<?php
/**
 * Dashboard — Kategoriler Sayfası
 */
?>
<div class="dash-topbar">
    <h1>Kategoriler</h1>
    <button class="btn btn-primary" onclick="openCategoryForm()">+ Yeni Kategori</button>
</div>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>İkon</th>
                <th>Ad</th>
                <th>Slug</th>
                <th>Sıra</th>
                <th>Aktif</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody id="categoriesTbody">
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">Yükleniyor...</td></tr>
        </tbody>
    </table>
</div>

<!-- Kategori Formu Modal -->
<div class="modal-backdrop" id="categoryFormModal">
    <div class="modal-box" style="max-width:480px">
        <div class="modal-box-header">
            <h3 id="catFormTitle">Yeni Kategori</h3>
            <button class="btn-icon" onclick="closeCategoryForm()">✕</button>
        </div>
        <div class="modal-box-body">
            <input type="hidden" id="editCatId">
            <div class="form-group">
                <label class="form-label">Kategori Adı <span class="required">*</span></label>
                <input type="text" class="form-input" id="catName" placeholder="Örn: Burgerler" oninput="autoSlug()">
            </div>
            <div class="form-group">
                <label class="form-label">Slug <span class="required">*</span></label>
                <input type="text" class="form-input" id="catSlug" placeholder="burgerler">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">İkon (emoji)</label>
                    <input type="text" class="form-input" id="catIcon" placeholder="🍔">
                </div>
                <div class="form-group">
                    <label class="form-label">Sıra Numarası</label>
                    <input type="number" class="form-input" id="catOrder" value="0">
                </div>
            </div>
            <div class="checkbox-row">
                <label class="checkbox-label">
                    <input type="checkbox" id="catActive" checked> Aktif
                </label>
            </div>
        </div>
        <div class="modal-box-footer">
            <button class="btn btn-secondary" onclick="closeCategoryForm()">İptal</button>
            <button class="btn btn-primary" onclick="saveCategory()">Kaydet</button>
        </div>
    </div>
</div>

<script>
let categories = [];

async function loadCategories() {
    try {
        categories = await apiRequest('/categories.php');
        renderCategories();
    } catch (e) {
        showToast('Kategoriler yüklenemedi', 'error');
    }
}

function renderCategories() {
    const tbody = document.getElementById('categoriesTbody');
    if (categories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">Henüz kategori yok</td></tr>';
        return;
    }

    tbody.innerHTML = categories.map(c => {
        return '<tr>' +
            '<td style="font-size:1.5rem">' + (c.icon || '📁') + '</td>' +
            '<td><strong>' + escapeHtml(c.name) + '</strong></td>' +
            '<td style="color:var(--text-muted)">' + escapeHtml(c.slug) + '</td>' +
            '<td>' + c.sort_order + '</td>' +
            '<td><label class="toggle"><input type="checkbox" ' + (c.is_active == 1 ? 'checked' : '') + ' onchange="toggleCategoryActive(' + c.id + ', this.checked, \'' + escapeHtml(c.name) + '\', \'' + escapeHtml(c.slug) + '\')"><span class="slider"></span></label></td>' +
            '<td><div class="actions">' +
                '<button class="btn-icon" onclick="editCategory(' + c.id + ')" title="Düzenle">✏️</button>' +
                '<button class="btn-icon" onclick="deleteCategory(' + c.id + ')" title="Sil">🗑️</button>' +
            '</div></td>' +
        '</tr>';
    }).join('');
}

function openCategoryForm(cat = null) {
    document.getElementById('categoryFormModal').classList.add('show');
    document.getElementById('catFormTitle').textContent = cat ? 'Kategoriyi Düzenle' : 'Yeni Kategori';
    document.getElementById('editCatId').value = cat ? cat.id : '';

    if (cat) {
        document.getElementById('catName').value = cat.name || '';
        document.getElementById('catSlug').value = cat.slug || '';
        document.getElementById('catIcon').value = cat.icon || '';
        document.getElementById('catOrder').value = cat.sort_order || 0;
        document.getElementById('catActive').checked = cat.is_active == 1;
    } else {
        document.getElementById('catName').value = '';
        document.getElementById('catSlug').value = '';
        document.getElementById('catIcon').value = '';
        document.getElementById('catOrder').value = 0;
        document.getElementById('catActive').checked = true;
    }
}

function closeCategoryForm() {
    document.getElementById('categoryFormModal').classList.remove('show');
}

function autoSlug() {
    const name = document.getElementById('catName').value;
    const slug = name.toLowerCase()
        .replace(/ç/g, 'c').replace(/ğ/g, 'g').replace(/ı/g, 'i')
        .replace(/ö/g, 'o').replace(/ş/g, 's').replace(/ü/g, 'u')
        .replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    document.getElementById('catSlug').value = slug;
}

async function saveCategory() {
    const id = document.getElementById('editCatId').value;
    const data = {
        name: document.getElementById('catName').value,
        slug: document.getElementById('catSlug').value,
        icon: document.getElementById('catIcon').value || null,
        sort_order: parseInt(document.getElementById('catOrder').value) || 0,
        is_active: document.getElementById('catActive').checked ? 1 : 0,
    };

    if (!data.name || !data.slug) {
        showToast('Ad ve slug zorunludur!', 'error');
        return;
    }

    try {
        if (id) {
            await apiRequest('/category.php?id=' + id, 'PUT', data);
            showToast('Kategori güncellendi!');
        } else {
            await apiRequest('/categories.php', 'POST', data);
            showToast('Kategori eklendi!');
        }
        closeCategoryForm();
        loadCategories();
    } catch (e) {
        showToast('Kaydetme hatası', 'error');
    }
}

function editCategory(id) {
    const c = categories.find(x => x.id == id);
    if (c) openCategoryForm(c);
}

async function deleteCategory(id) {
    if (!confirm('Bu kategoriyi silmek istediğinize emin misiniz?')) return;
    try {
        await apiRequest('/category.php?id=' + id, 'DELETE');
        showToast('Kategori silindi!');
        loadCategories();
    } catch (e) {
        showToast('Silme hatası', 'error');
    }
}

async function toggleCategoryActive(id, active, name, slug) {
    try {
        await apiRequest('/category.php?id=' + id, 'PUT', { name, slug, is_active: active ? 1 : 0 });
        showToast(active ? 'Kategori aktif' : 'Kategori pasif');
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

loadCategories();
</script>
