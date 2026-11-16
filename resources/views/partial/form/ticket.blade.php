{{--
  Dùng chung cho Tạo Ticket và Sửa Ticket.
  Tham số: $ticket (null khi tạo mới)
  Khi sửa: staff chỉ đổi được mức độ ưu tiên và trạng thái; tiêu đề / nội dung chỉ admin được sửa
  (TicketPolicy::updateContent, UpdateTicketRequest cũng từ chối ở server).
--}}
@php
    $editing = !empty($ticket);
    $isStaff = in_array(auth()->user()->role, ['staff', 'admin']);
    $canEditContent = ! $editing || auth()->user()->can('updateContent', $ticket);
@endphp
<form method="POST" action="{{ $editing ? route('tickets.update', $ticket) : route('tickets.store') }}" class="form-stack">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="form-row">
        <label for="title" class="label-md">Tiêu đề:</label>
        @if ($canEditContent)
            <input id="title" name="title" class="input input-title" required
                   value="{{ old('title', $ticket->title ?? '') }}">
        @else
            <input id="title" class="input input-title" value="{{ $ticket->title }}" readonly>
        @endif

        <label for="priority" class="label-md">Mức độ ưu tiên:</label>
        <select id="priority" name="priority" class="input input-auto" required>
            <option value="" disabled @selected(!old('priority', $ticket->priority ?? null))>Chọn</option>
            @foreach (config('helpdesk.priority') as $key => $label)
                <option value="{{ $key }}" @selected(old('priority', $ticket->priority ?? null) === $key)>{{ $label }}</option>
            @endforeach
        </select>

        @if ($editing && $isStaff)
            <label for="status" class="label-md">Trạng thái:</label>
            <select id="status" name="status" class="input input-auto">
                @foreach (config('helpdesk.status') as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $ticket->status) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="editor-row">
        <label>Nội dung:</label>
        <div class="editor-col">
            @if ($canEditContent)
                @include('partial.rich_editor', ['name' => 'message', 'value' => $ticket->message ?? ''])
            @else
                <div class="message message-first rich">{!! $ticket->message !!}</div>
            @endif
            <div class="center mt-4">
                <button class="btn">{{ $editing ? 'Cập nhật' : 'Gửi Ticket' }}</button>
            </div>
        </div>
    </div>
</form>
