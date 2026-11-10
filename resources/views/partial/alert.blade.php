@if (session('status'))
    <div class="mb-4 rounded-lg bg-success/30 px-4 py-2">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-700 bg-red-50 px-4 py-2 text-red-700">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif
