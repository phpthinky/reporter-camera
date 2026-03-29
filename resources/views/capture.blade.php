<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>New Capture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body            { background:#0f172a; color:#f1f5f9; min-height:100vh; display:flex; flex-direction:column; }
        .header         { background:#1e293b; padding:14px 16px; border-bottom:1px solid #334155; display:flex; align-items:center; gap:12px; }
        .back-btn       { background:none; border:none; color:#94a3b8; font-size:1.3rem; padding:0; line-height:1; }
        .preview-area   { flex:1; display:flex; align-items:center; justify-content:center; padding:20px; min-height:260px; }
        .preview-area img       { max-width:100%; max-height:340px; border-radius:12px; object-fit:contain; border:2px solid #334155; }
        .preview-placeholder    { text-align:center; color:#475569; }
        .actions        { padding:16px; display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .save-bar       { padding:16px; border-top:1px solid #1e293b; }
        .status         { font-size:0.8rem; color:#64748b; text-align:center; padding:0 16px 10px; min-height:22px; }
    </style>
</head>
<body>

<div class="header">
    <button class="back-btn" onclick="window.location='/'">&#8592;</button>
    <span class="fw-semibold">New Capture</span>
</div>

<div class="preview-area" id="previewArea">
    <div class="preview-placeholder" id="placeholder">
        <div style="font-size:3rem">🖼</div>
        <p class="mt-2 mb-0">Pick or take a photo to preview</p>
    </div>
    <img id="preview" style="display:none" alt="Selected photo">
</div>

<p class="status" id="status"></p>

<div class="actions">
    <button class="btn btn-outline-light" id="btnGallery">🖼 Pick from Gallery</button>
    <button class="btn btn-outline-light" id="btnPhoto">📸 Take Photo</button>
</div>

<div class="save-bar" id="saveBar" style="display:none">
    <button class="btn btn-primary w-100 btn-lg" id="btnSave">💾 Save Photo</button>
</div>

@use(Native\Mobile\Events\Camera\PhotoTaken)
@use(Native\Mobile\Events\Camera\PhotoCancelled)
@use(Native\Mobile\Events\Gallery\MediaSelected)

<script>
    const status      = document.getElementById('status');
    const preview     = document.getElementById('preview');
    const placeholder = document.getElementById('placeholder');
    const saveBar     = document.getElementById('saveBar');
    const btnSave     = document.getElementById('btnSave');

    let selectedPath  = null;

    function showPreview(path) {
        try {
            selectedPath = path;
            status.textContent = '📂 ' + path;

            preview.src = '/camera/preview?path=' + encodeURIComponent(path);
            preview.onload  = () => {
                preview.style.display     = 'block';
                placeholder.style.display = 'none';
                saveBar.style.display     = 'block';
                status.textContent        = 'Ready to save';
            };
            preview.onerror = () => {
                status.textContent = '❌ Could not load preview — path: ' + path;
            };
        } catch (err) {
            status.textContent = '❌ ' + err.message;
        }
    }

    function setupListeners() {
        Native.on(@js(PhotoTaken::class), (payload) => {
            try {
                if (payload && payload.path) showPreview(payload.path);
            } catch (err) {
                status.textContent = '❌ PhotoTaken: ' + err.message;
            }
        });

        Native.on(@js(PhotoCancelled::class), () => {
            status.textContent = 'Cancelled.';
        });

        Native.on(@js(MediaSelected::class), (payload) => {
            try {
                if (payload && payload.files && payload.files.length > 0) {
                    const file = payload.files[0];
                    showPreview(typeof file === 'string' ? file : file.path);
                }
            } catch (err) {
                status.textContent = '❌ MediaSelected: ' + err.message;
            }
        });
    }

    function initNative() {
        if (typeof Native === 'undefined') { setTimeout(initNative, 100); return; }
        setupListeners();
    }
    initNative();

    document.getElementById('btnGallery').addEventListener('click', () => {
        status.textContent = 'Opening gallery...';
        fetch('/camera/gallery').catch(err => { status.textContent = '❌ ' + err.message; });
    });

    document.getElementById('btnPhoto').addEventListener('click', () => {
        status.textContent = 'Opening camera...';
        fetch('/camera/photo').catch(err => { status.textContent = '❌ ' + err.message; });
    });

    btnSave.addEventListener('click', () => {
        if (!selectedPath) return;

        btnSave.disabled      = true;
        btnSave.textContent   = 'Saving...';
        status.textContent    = '';

        fetch('/camera/save?path=' + encodeURIComponent(selectedPath))
        .then(r => r.json())
        .then(data => {
            if (data.url) {
                window.location = '/';
            } else {
                status.textContent  = '❌ ' + (data.error || JSON.stringify(data));
                btnSave.disabled    = false;
                btnSave.textContent = '💾 Save Photo';
            }
        })
        .catch(err => {
            status.textContent  = '❌ ' + err.message;
            btnSave.disabled    = false;
            btnSave.textContent = '💾 Save Photo';
        });
    });
</script>

</body>
</html>
