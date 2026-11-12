{{-- Tham số: $status (new | in_progress | solved | closed) --}}
<span class="badge badge-{{ $status }}">{{ config('helpdesk.status.' . $status) }}</span>
