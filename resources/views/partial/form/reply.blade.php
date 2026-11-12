{{-- Trả lời ticket. Tham số: $ticket --}}
<form method="POST" action="{{ route('tickets.reply', $ticket) }}" class="editor-row">
    @csrf
    <label>Trả lời:</label>
    <div class="editor-col">
        @include('partial.rich_editor', ['name' => 'message', 'value' => ''])
        <div class="center mt-4">
            <button class="btn">Gửi trả lời</button>
        </div>
    </div>
</form>
