<?php
/**
 * Dashboard — Ürünler Sayfası
 */
?>
<div class="dash-topbar">
    <h1>Ürünler</h1>
    <button class="btn btn-primary" onclick="openProductForm()">+ Yeni Ürün</button>
</div>

<!-- Ürünler Tablosu -->
<div class="data-table" id="productsTable">
    <table>
        <thead>
            <tr>
                <th>Görsel</th>
                <th>Ürün Adı</th>
                <th>Kategori</th>
                <th>Fiyat</th>
                <th>Müsait</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody id="productsTbody">
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">Yükleniyor...</td></tr>
        </tbody>
    </table>
</div>

<!-- Ürün Formu Modal -->
<div class="modal-backdrop" id="productFormModal">
    <div class="modal-box" style="max-width:800px">
        <div class="modal-box-header">
            <h3 id="formTitle">Yeni Ürün</h3>
            <button class="btn-icon" onclick="closeProductForm()">✕</button>
        </div>
        <div class="modal-box-body">
            <div style="display:grid;grid-template-columns:1fr 280px;gap:24px;">
                <!-- Form Sol -->
                <div>
                    <input type="hidden" id="editProductId">

                        <div class="form-group">
                            <label class="form-label">Ürün Adı <span class="required">*</span></label>
                            <input type="text" class="form-input" id="pName" placeholder="Ürün adı">
                        </div>

                    <div class="form-group">
                        <label class="form-label">Açıklama</label>
                        <textarea class="form-textarea" id="pDesc" placeholder="İştah açıcı bir açıklama..." rows="3"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Fiyat (₺) <span class="required">*</span></label>
                            <input type="number" class="form-input" id="pPrice" placeholder="0.00" step="0.01">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kategori <span class="required">*</span></label>
                            <select class="form-select" id="pCategory">
                                <option value="">Seçiniz</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kalori</label>
                            <input type="number" class="form-input" id="pCalories" placeholder="350">
                        </div>
                    </div>

                    <div class="checkbox-row">
                        <label class="checkbox-label">
                            <input type="checkbox" id="pAvailable" checked> Müsait
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" id="pFeatured"> Öne Çıkan
                        </label>
                    </div>

                    <!-- Görsel Yükleme -->
                    <div class="form-group">
                        <label class="form-label">Ürün Görseli</label>
                        <div id="imageUploadZone">
                            <div class="image-upload-area" id="uploadArea">
                                <div class="upload-icon">📷</div>
                                <p class="upload-text">Görseli sürükleyin veya <strong>tıklayarak seçin</strong></p>
                                <input type="file" accept="image/*" id="imageFileInput" onchange="handleImageUpload(this)">
                            </div>
                        </div>
                        <div id="imagePreviewWrap" style="display:none; margin-top:10px;">
                            <div class="image-preview">
                                <img id="imagePreviewImg" src="" alt="Önizleme">
                                <button class="remove-btn" onclick="removeImage()">✕</button>
                            </div>
                        </div>
                        <input type="hidden" id="pImageUrl">
                    </div>

                    <!-- Varyantlar -->
                    <div class="form-group">
                        <label class="form-label">Seçenekler (Varyantlar)</label>
                        <div id="variantsContainer"></div>
                        <button type="button" class="btn btn-sm" style="margin-top:8px;background:var(--border);color:var(--text);border:1px dashed var(--text-muted);border-radius:8px;padding:6px 14px;cursor:pointer;font-size:0.8rem;" onclick="addVariantRow()">
                            + Seçenek Ekle
                        </button>
                    </div>
                </div>

                <!-- Canlı Önizleme -->
                <div>
                    <label class="form-label" style="margin-bottom:12px;">Canlı Önizleme</label>
                    <div class="preview-card">
                        <div class="preview-image">
                            <div class="no-image" id="previewNoImage">🍔</div>
                            <img id="previewImage" src="" alt="Önizleme" style="display:none">
                        </div>
                        <div class="preview-body">
                            <div class="preview-name" id="previewName">Ürün Adı</div>
                            <div class="preview-price" id="previewPrice">0.00 ₺</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-box-footer">
            <button class="btn btn-secondary" onclick="closeProductForm()">İptal</button>
            <button class="btn btn-primary" onclick="saveProduct()">Kaydet</button>
        </div>
    </div>
</div>

<script>
let allCategories = [];
let allProducts = [];

// Verileri yükle
async function loadProducts() {
    try {
        const [products, categories] = await Promise.all([
            apiRequest('/products.php'),
            apiRequest('/categories.php'),
        ]);
        allProducts = products;
        allCategories = categories;
        renderProductsTable();
        fillCategorySelect();
    } catch (e) {
        showToast('Veriler yüklenemedi: ' + e.message, 'error');
    }
}

function fillCategorySelect() {
    const sel = document.getElementById('pCategory');
    sel.innerHTML = '<option value="">Seçiniz</option>';
    allCategories.forEach(c => {
        sel.innerHTML += '<option value="' + c.id + '">' + escapeHtml(c.name) + '</option>';
    });
}

function renderProductsTable() {
    const tbody = document.getElementById('productsTbody');

    if (allProducts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">Henüz ürün eklenmemiş.<br><br><button class="btn btn-primary btn-sm" onclick="openProductForm()">+ İlk Ürünü Ekle</button></td></tr>';
        return;
    }

    tbody.innerHTML = allProducts.map(p => {
        const imgHtml = p.image_url
            ? '<img class="thumb" src="/pashaqr-live' + escapeHtml(p.image_url) + '" alt="" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'" ><div class="thumb-placeholder" style="display:none">🍔</div>'
            : '<div class="thumb-placeholder">🍔</div>';

        return '<tr>' +
            '<td>' + imgHtml + '</td>' +
            '<td><strong>' + escapeHtml(p.name) + '</strong></td>' +
            '<td>' + (p.category ? escapeHtml(p.category.name) : '—') + '</td>' +
            '<td><strong>' + parseFloat(p.price).toFixed(2) + ' ₺</strong></td>' +
            '<td><label class="toggle"><input type="checkbox" ' + (p.is_available ? 'checked' : '') + ' onchange="toggleAvailability(' + p.id + ', this.checked)"><span class="slider"></span></label></td>' +
            '<td><div class="actions">' +
                '<button class="btn-icon" onclick="editProduct(' + p.id + ')" title="Düzenle">✏️</button>' +
                '<button class="btn-icon" onclick="deleteProduct(' + p.id + ')" title="Sil">🗑️</button>' +
            '</div></td>' +
        '</tr>';
    }).join('');
}

// Form aç/kapat
function openProductForm(product = null) {
    document.getElementById('productFormModal').classList.add('show');
    document.getElementById('formTitle').textContent = product ? 'Ürünü Düzenle' : 'Yeni Ürün';
    document.getElementById('editProductId').value = product ? product.id : '';

    // Varyantları temizle
    document.getElementById('variantsContainer').innerHTML = '';

    if (product) {
        document.getElementById('pName').value = product.name || '';
        document.getElementById('pDesc').value = product.description_tr || product.description || '';
        document.getElementById('pPrice').value = product.price || '';
        document.getElementById('pCategory').value = product.category_id || '';
        document.getElementById('pCalories').value = product.calories || '';
        document.getElementById('pAvailable').checked = product.is_available;
        document.getElementById('pFeatured').checked = product.is_featured;
        document.getElementById('pImageUrl').value = product.image_url || '';

        if (product.image_url) {
            showImagePreview('/pashaqr-live' + product.image_url);
        } else {
            hideImagePreview();
        }

        // Mevcut varyantları yükle
        if (product.variants && product.variants.length > 0) {
            product.variants.forEach(v => addVariantRow(v.name, v.price_delta));
        }
    } else {
        clearProductForm();
    }
    updatePreview();
}

function closeProductForm() {
    document.getElementById('productFormModal').classList.remove('show');
}

function clearProductForm() {
    document.getElementById('editProductId').value = '';
    document.getElementById('pName').value = '';
    document.getElementById('pDesc').value = '';
    document.getElementById('pPrice').value = '';
    document.getElementById('pCategory').value = '';
    document.getElementById('pCalories').value = '';
    document.getElementById('pAvailable').checked = true;
    document.getElementById('pFeatured').checked = false;
    document.getElementById('pImageUrl').value = '';
    document.getElementById('variantsContainer').innerHTML = '';
    hideImagePreview();
    updatePreview();
}

// Varyant satırı ekle
function addVariantRow(name = '', priceDelta = 0) {
    const container = document.getElementById('variantsContainer');
    const row = document.createElement('div');
    row.className = 'variant-row';
    row.innerHTML =
        '<input type="text" class="form-input v-name" placeholder="Seçenek adı (ör: Tagliatelle)" value="' + escapeHtml(name) + '" style="flex:1;">' +
        '<input type="number" class="form-input v-delta" placeholder="Fark (₺)" value="' + (parseFloat(priceDelta) || 0) + '" step="0.01" style="width:100px;">' +
        '<button type="button" class="btn-icon" onclick="this.parentElement.remove()" title="Sil" style="color:#ef4444;">✕</button>';
    container.appendChild(row);
}

// Varyantları formdan oku
function getVariantsFromForm() {
    const rows = document.querySelectorAll('#variantsContainer .variant-row');
    const variants = [];
    rows.forEach(row => {
        const name = row.querySelector('.v-name').value.trim();
        const delta = parseFloat(row.querySelector('.v-delta').value) || 0;
        if (name) variants.push({ name, price_delta: delta });
    });
    return variants;
}

// Görsel yükleme
async function handleImageUpload(input) {
    const file = input.files[0];
    if (!file) return;

    try {
        showToast('Görsel yükleniyor...', 'success');
        const result = await uploadFile(file);

        if (result.error) {
            showToast('Hata: ' + result.error, 'error');
            return;
        }

        document.getElementById('pImageUrl').value = result.url;
        showImagePreview('/pashaqr-live' + result.url);
        updatePreview();
        showToast('Görsel yüklendi!', 'success');
    } catch (e) {
        showToast('Görsel yüklenemedi: ' + e.message, 'error');
    }
}

function showImagePreview(src) {
    document.getElementById('imagePreviewImg').src = src;
    document.getElementById('imagePreviewWrap').style.display = 'block';
    document.getElementById('uploadArea').style.display = 'none';

    document.getElementById('previewImage').src = src;
    document.getElementById('previewImage').style.display = 'block';
    document.getElementById('previewNoImage').style.display = 'none';
}

function hideImagePreview() {
    document.getElementById('imagePreviewWrap').style.display = 'none';
    document.getElementById('uploadArea').style.display = '';
    document.getElementById('pImageUrl').value = '';
    document.getElementById('imageFileInput').value = '';

    document.getElementById('previewImage').style.display = 'none';
    document.getElementById('previewNoImage').style.display = 'flex';
}

function removeImage() {
    hideImagePreview();
    updatePreview();
}

// Canlı önizleme güncelle
function updatePreview() {
    const name = document.getElementById('pName').value || 'Ürün Adı';
    const price = document.getElementById('pPrice').value || '0.00';
    document.getElementById('previewName').textContent = name;
    document.getElementById('previewPrice').textContent = parseFloat(price || 0).toFixed(2) + ' ₺';
}

document.getElementById('pName').addEventListener('input', updatePreview);
document.getElementById('pPrice').addEventListener('input', updatePreview);

// Kaydet
async function saveProduct() {
    const id = document.getElementById('editProductId').value;
    const data = {
        name: document.getElementById('pName').value,
        name_tr: null,
        description_tr: document.getElementById('pDesc').value || null,
        price: parseFloat(document.getElementById('pPrice').value),
        category_id: parseInt(document.getElementById('pCategory').value) || null,
        calories: parseInt(document.getElementById('pCalories').value) || null,
        is_available: document.getElementById('pAvailable').checked ? 1 : 0,
        is_featured: document.getElementById('pFeatured').checked ? 1 : 0,
        image_url: document.getElementById('pImageUrl').value || null,
        tags: [],
        sort_order: 0,
        variants: getVariantsFromForm(),
    };

    if (!data.name || !data.price) {
        showToast('Ürün adı ve fiyat zorunludur!', 'error');
        return;
    }

    try {
        if (id) {
            await apiRequest('/product.php?id=' + id, 'PUT', data);
            showToast('Ürün güncellendi!');
        } else {
            await apiRequest('/products.php', 'POST', data);
            showToast('Ürün eklendi!');
        }
        closeProductForm();
        loadProducts();
    } catch (e) {
        showToast('Kaydetme hatası: ' + e.message, 'error');
    }
}

// Düzenle
function editProduct(id) {
    const p = allProducts.find(x => x.id === id);
    if (p) openProductForm(p);
}

// Sil
async function deleteProduct(id) {
    if (!confirm('Bu ürünü silmek istediğinize emin misiniz?')) return;
    try {
        await apiRequest('/product.php?id=' + id, 'DELETE');
        showToast('Ürün silindi!');
        loadProducts();
    } catch (e) {
        showToast('Silme hatası: ' + e.message, 'error');
    }
}

// Müsaitlik toggle
async function toggleAvailability(id, available) {
    try {
        await apiRequest('/product.php?id=' + id, 'PATCH', { is_available: available ? 1 : 0 });
        showToast(available ? 'Ürün müsait olarak işaretlendi' : 'Ürün müsait değil olarak işaretlendi');
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

// Başlat
loadProducts();
</script>
