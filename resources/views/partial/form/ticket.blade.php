{{--
  Dùng chung cho Tạo Ticket và Sửa Ticket.
  Tham số: $ticket (null khi tạo mới)
--}}
@php
    $editing = !empty($ticket);
    $isStaff = in_array(auth()->user()->role, ['staff', 'admin']);
@endphp
<form method="POST" action="{{ $editing ? route('tickets.update', $ticket) : route('tickets.store') }}" class="space-y-5">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="flex flex-wrap items-center gap-4">
        <label for="title" class="text-lg">Tiêu đề:</label>
        <input id="title" name="title" class="input max-w-md flex-1" required
               value="{{ old('title', $ticket->title ?? '') }}">

        <label for="priority" class="text-lg">Mức độ ưu tiên:</label>
        <select id="priority" name="priority" class="input w-auto" required>
            <option value="" disabled @selected(!old('priority', $ticket->priority ?? null))>Chọn</option>
            @foreach (config('helpdesk.priority') as $key => $label)
                <option value="{{ $key }}" @selected(old('priority', $ticket->priority ?? null) === $key)>{{ $label }}</option>
            @endforeach
        </select>

        @if ($editing && $isStaff)
            <label for="status" class="text-lg">Trạng thái:</label>
            <select id="status" name="status" class="input w-auto">
                @foreach (config('helpdesk.status') as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $ticket->status) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        @endif
    </div>

    <div class="flex flex-col gap-2 lg:flex-row">
        <label class="text-lg lg:w-24">Nội dung:</label>
        <div class="max-w-3xl flex-1">
            @include('partial.rich_editor', ['name' => 'message', 'value' => $ticket->message ?? ''])
            <div class="mt-4 text-center">
                <button class="btn">{{ $editing ? 'Cập nhật' : 'Gửi Ticket' }}</button>
            </div>
        </div>
    </div>
</form>
