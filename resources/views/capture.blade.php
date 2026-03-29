<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>New Capture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body    { background:#0f172a; color:#f1f5f9; padding:16px; font-family:sans-serif; }
        #preview { width:100%; border-radius:12px; border:2px solid #10b981; display:none; margin-bottom:12px; }
        .status  { font-size:0.85rem; color:#94a3b8; margin-bottom:12px; }
    </style>
</head>
<body>

    <div class="d-flex align-items-center mb-3 gap-2">
        <a href="/" class="btn btn-sm btn-outline-secondary">← Back</a>
        <h5 class="fw-bold mb-0">📷 New Capture</h5>
    </div>

    <p class="status" id="status">Ready.</p>

    <img id="preview" alt="Captured photo">

    <div class="d-grid gap-2">
        <button class="btn btn-primary btn-lg"       id="btnPhoto">📸 Take Photo</button>
        <button class="btn btn-outline-light btn-lg" id="btnGallery">🖼 Pick from Gallery</button>
    </div>

    <div class="mt-3" id="saveSection" style="display:none">
        <button class="btn btn-success w-100 btn-lg" id="btnSave">💾 Save Photo</button>
    </div>

    <div class="mt-2" id="savedMsg" style="display:none">
        <small class="text-success">✅ Saved: <span id="savedPath"></span></small>
    </div>

@use(Native\Mobile\Events\Camera\PhotoTaken)
@use(Native\Mobile\Events\Camera\PhotoCancelled)
@use(Native\Mobile\Events\Gallery\MediaSelected)

<script>
    const status      = document.getElementById('status');
    const preview     = document.getElementById('preview');
    const savedMsg    = document.getElementById('savedMsg');
    const savedPath   = document.getElementById('savedPath');
    const saveSection = document.getElementById('saveSection');
    const btnSave     = document.getElementById('btnSave');

    let pendingPath = null;

    // Preview the photo via server proxy (file:// blocked for cache paths in WebView)
    function showPreview(path) {
        pendingPath    = path;
        status.textContent = 'Preview loaded — tap Save to keep.';

        preview.src            = '/camera/preview?path=' + encodeURIComponent(path);
        preview.style.display  = 'block';
        saveSection.style.display = 'block';
        savedMsg.style.display = 'none';
    }

    function setupListeners() {
        Native.on(@js(PhotoTaken::class), (payload) => {
            try {
                status.textContent = 'Photo taken: ' + JSON.stringify(payload);
                if (payload.path) showPreview(payload.path);
            } catch(e) { status.textContent = '❌ ' + e.message; }
        });

        Native.on(@js(PhotoCancelled::class), () => {
            status.textContent = 'Cancelled.';
        });

        Native.on(@js(MediaSelected::class), (payload) => {
            try {
                status.textContent = 'Media selected: ' + JSON.stringify(payload);
                if (payload.files && payload.files.length > 0) {
                    const file = payload.files[0];
                    showPreview(typeof file === 'string' ? file : file.path);
                }
            } catch(e) { status.textContent = '❌ ' + e.message; }
        });

        status.textContent = 'Native ready ✅';
    }

    function initNative() {
        if (typeof Native === 'undefined') { setTimeout(initNative, 100); return; }
        setupListeners();
    }
    initNative();

    document.getElementById('btnPhoto').addEventListener('click', () => {
        status.textContent = 'Opening camera...';
        fetch('/camera/photo').catch(e => { status.textContent = '❌ ' + e.message; });
    });

    document.getElementById('btnGallery').addEventListener('click', () => {
        status.textContent = 'Opening gallery...';
        fetch('/camera/gallery').catch(e => { status.textContent = '❌ ' + e.message; });
    });

    btnSave.addEventListener('click', () => {
        if (!pendingPath) return;
        btnSave.disabled    = true;
        btnSave.textContent = 'Saving...';

        fetch('/camera/save?path=' + encodeURIComponent(pendingPath))
        .then(r => r.json())
        .then(data => {
            if (data.url) {
                savedPath.textContent     = data.url;
                savedMsg.style.display    = 'block';
                saveSection.style.display = 'none';
                status.textContent        = '✅ Saved!';
                pendingPath               = null;
            } else {
                status.textContent  = '❌ ' + JSON.stringify(data);
                btnSave.disabled    = false;
                btnSave.textContent = '💾 Save Photo';
            }
        })
        .catch(e => {
            status.textContent  = '❌ ' + e.message;
            btnSave.disabled    = false;
            btnSave.textContent = '💾 Save Photo';
        });
    });
</script>

</body>
</html>
