<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Photos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#0f172a; color:#f1f5f9; padding:16px; }
        .photo-card img { width:100%; border-radius:8px; }
    </style>
</head>
<body>

    <h5 class="fw-bold mb-3">🗂 Saved Photos ({{ $photos->count() }})</h5>

    @forelse($photos as $photo)
    <div class="photo-card mb-3 p-2 border border-secondary rounded">
        <img src="/photo/{{ $photo->id }}" alt="photo">
        <small class="text-muted d-block mt-1">
            📅 {{ $photo->taken_at }}<br>
            📁 {{ basename($photo->path) }}
        </small>
    </div>
    @empty
    <p class="text-muted">No photos yet.</p>
    @endforelse

    <a href="/" class="btn btn-outline-light btn-sm mt-2">← Back to Camera</a>

</body>
</html>