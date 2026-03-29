@extends('layouts.app')

@section('content')
<div class="flex flex-col min-h-screen bg-gray-950">

    {{-- Header --}}
    <div class="px-4 py-5 bg-gray-900 border-b border-gray-800">
        <h1 class="text-xl font-bold text-white">📸 Reporter Camera</h1>
        <p class="text-xs text-gray-400 mt-1">
            {{ $photos->count() }} photo(s) captured
        </p>
    </div>

    {{-- Last photo preview --}}
    <div class="flex-1 flex items-center justify-center p-6">
        @php $latestPhoto = Cache::get('latest_photo'); @endphp

        @if($latestPhoto)
            <img src="{{ $latestPhoto }}" ...>
        @else
            <p>No photo yet</p>
        @endif
       
    </div>

    {{-- Capture button --}}
    <div class="p-8 flex flex-col items-center gap-4">
           <button type="button" onclick="capturePhoto()"
                class="w-24 h-24 rounded-full bg-white border-4 border-gray-500
                       flex items-center justify-center shadow-2xl
                       active:scale-95 transition-transform duration-100">
                <div class="w-16 h-16 rounded-full bg-gray-300"></div>
            </button>
        <p class="text-xs text-gray-600">Tap to capture with watermark</p>
    </div>

</div>
@endsection


<script>
function capturePhoto() {
    fetch('{{ route('camera.capture') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    });
}
</script>