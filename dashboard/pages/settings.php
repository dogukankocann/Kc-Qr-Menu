<?php
/**
 * Dashboard — Ayarlar Sayfası
 */
?>
<div class="dash-topbar">
    <h1>Ayarlar</h1>
</div>

<div style="max-width:600px">
    <div class="form-group">
        <label class="form-label">Restoran Adı</label>
        <input type="text" class="form-input" id="setName" value="">
    </div>

    <div class="form-group">
        <label class="form-label">Logo</label>
        <div id="logoUploadZone">
            <div class="image-upload-area" id="logoUploadArea">
                <div class="upload-icon">🖼️</div>
                <p class="upload-text">Logo seçin veya <strong>sürükleyin</strong></p>
                <input type="file" accept="image/*" onchange="handleLogoUpload(this)">
            </div>
        </div>
        <div id="logoPreviewWrap" style="display:none; margin-top:10px;">
            <div class="image-preview">
                <img id="logoPreviewImg" src="" alt="Logo">
                <button class="remove-btn" onclick="removeLogo()">✕</button>
            </div>
        </div>
        <input type="hidden" id="setLogoUrl">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Ana Renk</label>
            <input type="color" class="form-input" id="setPrimaryColor" value="#DC2626" style="height:44px;padding:4px">
        </div>
        <div class="form-group">
            <label class="form-label">Vurgu Rengi</label>
            <input type="color" class="form-input" id="setAccentColor" value="#F59E0B" style="height:44px;padding:4px">
        </div>
        <div class="form-group">
            <label class="form-label">Para Birimi</label>
            <input type="text" class="form-input" id="setCurrency" value="₺">
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Duyuru (menüde üstte görünür)</label>
        <input type="text" class="form-input" id="setAnnouncement" placeholder="Bugüne özel %20 indirim!">
    </div>

    <div class="form-group">
        <label class="form-label">Instagram Adresi (Link)</label>
        <input type="url" class="form-input" id="setInstagramUrl" placeholder="https://instagram.com/pashafastfood">
    </div>

    <div class="checkbox-row">
        <label class="checkbox-label">
            <input type="checkbox" id="setIsOpen" checked> Restoran Açık
        </label>
    </div>

    <button class="btn btn-primary" onclick="saveSettings()" style="margin-top:10px">💾 Kaydet</button>
</div>

<script>
async function loadSettings() {
    try {
        const s = await apiRequest('/settings.php');
        document.getElementById('setName').value = s.restaurant_name || '';
        document.getElementById('setPrimaryColor').value = s.primary_color || '#DC2626';
        document.getElementById('setAccentColor').value = s.accent_color || '#F59E0B';
        document.getElementById('setCurrency').value = s.currency || '₺';
        document.getElementById('setAnnouncement').value = s.announcement || '';
        document.getElementById('setInstagramUrl').value = s.instagram_url || '';
        document.getElementById('setIsOpen').checked = s.is_open;
        document.getElementById('setLogoUrl').value = s.logo_url || '';

        if (s.logo_url) {
            document.getElementById('logoPreviewImg').src = '/pashaqr-live' + s.logo_url;
            document.getElementById('logoPreviewWrap').style.display = 'block';
            document.getElementById('logoUploadArea').style.display = 'none';
        }
    } catch (e) {
        showToast('Ayarlar yüklenemedi', 'error');
    }
}

async function saveSettings() {
    const data = {
        restaurant_name: document.getElementById('setName').value,
        logo_url: document.getElementById('setLogoUrl').value || null,
        primary_color: document.getElementById('setPrimaryColor').value,
        accent_color: document.getElementById('setAccentColor').value,
        currency: document.getElementById('setCurrency').value,
        announcement: document.getElementById('setAnnouncement').value || null,
        instagram_url: document.getElementById('setInstagramUrl').value || null,
        is_open: document.getElementById('setIsOpen').checked,
    };

    try {
        await apiRequest('/settings.php', 'PUT', data);
        showToast('Ayarlar kaydedildi!');
    } catch (e) {
        showToast('Kaydetme hatası', 'error');
    }
}

async function handleLogoUpload(input) {
    const file = input.files[0];
    if (!file) return;
    try {
        const result = await uploadFile(file);
        if (result.error) {
            showToast(result.error, 'error');
            return;
        }
        document.getElementById('setLogoUrl').value = result.url;
        document.getElementById('logoPreviewImg').src = '/pashaqr-live' + result.url;
        document.getElementById('logoPreviewWrap').style.display = 'block';
        document.getElementById('logoUploadArea').style.display = 'none';
        showToast('Logo yüklendi!');
    } catch (e) {
        showToast('Logo yüklenemedi', 'error');
    }
}

function removeLogo() {
    document.getElementById('setLogoUrl').value = '';
    document.getElementById('logoPreviewWrap').style.display = 'none';
    document.getElementById('logoUploadArea').style.display = '';
}

loadSettings();
</script>
