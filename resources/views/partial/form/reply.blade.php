{{-- Trả lời ticket. Tham số: $ticket --}}
<form method="POST" action="{{ route('tickets.reply', $ticket) }}" class="flex flex-col gap-2 lg:flex-row">
    @csrf
    <label class="text-lg lg:w-20">Trả lời:</label>
    <div class="max-w-3xl flex-1">
        @include('partial.rich_editor', ['name' => 'message', 'value' => ''])
        <div class="mt-4 text-center">
            <button class="btn">Gửi trả lời</button>
        </div>
    </div>
</form>
