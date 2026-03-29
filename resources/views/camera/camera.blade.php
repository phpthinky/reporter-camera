<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Reporter Camera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#0f172a; color:#f1f5f9; padding:16px; font-family:sans-serif; }
        #preview { width:100%; border-radius:12px; border:2px solid #10b981; display:none; margin-bottom:12px; }
        .status { font-size:0.85rem; color:#94a3b8; margin-bottom:12px; }
    </style>
</head>
<body>

    <h5 class="fw-bold mb-1">📷 Reporter Camera</h5>
    <p class="status" id="status">Ready.</p>

    <img id="preview" alt="Captured photo">

    <div class="d-grid gap-2">
        <button class="btn btn-primary btn-lg" id="btnPhoto">📸 Take Photo</button>
        <button class="btn btn-outline-light btn-lg" id="btnGallery">🖼 Pick from Gallery</button>
        <a href="/photos" class="btn btn-outline-success btn-sm">🗂 View Saved Photos</a>
    </div>

    <div class="mt-3" id="savedMsg" style="display:none">
        <small class="text-success">✅ Path: <span id="savedPath"></span></small>
    </div>

@use(Native\Mobile\Events\Camera\PhotoTaken)
@use(Native\Mobile\Events\Camera\PhotoCancelled)
@use(Native\Mobile\Events\Gallery\MediaSelected)

<script>
    const status    = document.getElementById('status');
    const preview   = document.getElementById('preview');
    const savedMsg  = document.getElementById('savedMsg');
    const savedPath = document.getElementById('savedPath');

    let photoSaving = false;

    function showPhoto(path) {
        try {
            if (photoSaving) return;
            photoSaving = true;

            // Show preview immediately from DCIM path — no round-trip needed
            preview.src           = 'file://' + path;
            preview.style.display = 'block';
            status.textContent    = 'Saving...';

            fetch('/camera/save?path=' + encodeURIComponent(path))
            .then(r => r.json())
            .then(data => {
                if (data.url) {
                    savedPath.textContent  = path;
                    savedMsg.style.display = 'block';
                    status.textContent     = '✅ Saved!';
                } else {
                    status.textContent = '❌ ' + JSON.stringify(data);
                }
                photoSaving = false;
            })
            .catch(err => {
                status.textContent = '❌ ' + err.message;
                photoSaving = false;
            });
        } catch (err) {
            status.textContent = '❌ showPhoto: ' + err.message;
            photoSaving = false;
        }
    }

    function setupListeners() {
        Native.on(@js(PhotoTaken::class), (payload) => {
            try {
                status.textContent = 'PhotoTaken received...';
                if (payload && payload.path) showPhoto(payload.path);
            } catch (err) {
                status.textContent = '❌ PhotoTaken: ' + err.message;
            }
        });

        Native.on(@js(PhotoCancelled::class), () => {
            status.textContent = 'Photo cancelled.';
        });

        Native.on(@js(MediaSelected::class), (payload) => {
            try {
                status.textContent = 'Media selected...';
                if (payload && payload.files && payload.files.length > 0) {
                    const file = payload.files[0];
                    showPhoto(typeof file === 'string' ? file : file.path);
                }
            } catch (err) {
                status.textContent = '❌ MediaSelected: ' + err.message;
            }
        });

        status.textContent = 'Native ready ✅';
    }

    function initNative() {
        if (typeof Native === 'undefined') {
            setTimeout(initNative, 100);
            return;
        }
        setupListeners();
    }

    initNative();

    document.getElementById('btnPhoto').addEventListener('click', function () {
        status.textContent = 'Opening camera...';
        fetch('/camera/photo')
        .then(r => r.json())
        .then(() => { status.textContent = 'Camera opened, waiting...'; })
        .catch(err => { status.textContent = '❌ ' + err.message; });
    });

    document.getElementById('btnGallery').addEventListener('click', function () {
        status.textContent = 'Opening gallery...';
        fetch('/camera/gallery')
        .then(r => r.json())
        .then(() => { status.textContent = 'Gallery opened, waiting...'; })
        .catch(err => { status.textContent = '❌ ' + err.message; });
    });
</script>

</body>
</html>