<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Reporter Camera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body          { background:#0f172a; color:#f1f5f9; padding-bottom:90px; }
        .header       { background:#1e293b; padding:16px; border-bottom:1px solid #334155; position:sticky; top:0; z-index:10; }
        .photo-grid   { display:grid; grid-template-columns:1fr 1fr; gap:8px; padding:12px; }
        .photo-card   { position:relative; border-radius:10px; overflow:hidden; background:#1e293b; aspect-ratio:1; }
        .photo-card img      { width:100%; height:100%; object-fit:cover; display:block; }
        .photo-card .meta    { position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,.55); padding:4px 6px; font-size:0.65rem; color:#cbd5e1; }
        .empty-state  { text-align:center; padding:60px 24px; color:#475569; }
        .fab          { position:fixed; bottom:28px; right:24px; width:60px; height:60px; border-radius:50%; background:#3b82f6; border:none; color:#fff; font-size:1.6rem; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 20px rgba(59,130,246,.5); }
        .fab:active   { transform:scale(.93); }
    </style>
</head>
<body>

<div class="header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0">📷 Reporter Camera</h6>
            <small class="text-secondary">{{ $photos->count() }} photo{{ $photos->count() !== 1 ? 's' : '' }} saved</small>
        </div>
    </div>
</div>

@if($photos->isEmpty())
<div class="empty-state">
    <div style="font-size:3rem">📂</div>
    <p class="mt-3 mb-1 fw-semibold">No photos yet</p>
    <small>Tap + to capture your first photo</small>
</div>
@else
<div class="photo-grid">
    @foreach($photos as $photo)
    <div class="photo-card">
        <img src="/photo/{{ $photo->id }}" alt="photo"
             onerror="this.closest('.photo-card').style.background='#334155'">
        <div class="meta">{{ \Carbon\Carbon::parse($photo->taken_at)->format('M d, H:i') }}</div>
    </div>
    @endforeach
</div>
@endif

<a href="/capture" class="fab">＋</a>

</body>
</html>
