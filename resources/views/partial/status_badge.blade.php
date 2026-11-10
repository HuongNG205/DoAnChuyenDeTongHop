{{-- Tham số: $status (new | in_progress | resolved | closed) --}}
<span class="badge badge-{{ $status }}">{{ config('helpdesk.status.' . $status) }}</span>
