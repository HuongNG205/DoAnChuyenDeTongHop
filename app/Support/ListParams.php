<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tiện ích cho các trang danh sách: đọc an toàn tham số sort / filter trên query string.
 * Mọi giá trị người dùng gửi lên đều được đối chiếu với danh sách cho phép (allowlist).
 */
final class ListParams
{
    public static function dir(?string $dir): string
    {
        return strtolower((string) $dir) === 'desc' ? 'desc' : 'asc';
    }

    /** Trả về giá trị nếu nằm trong danh sách cho phép, ngược lại null (kể cả khi bị gửi dạng mảng). */
    public static function in(mixed $value, array $allowed): ?string
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' && in_array($value, array_map('strval', $allowed), true) ? $value : null;
    }

    /** Ngày hợp lệ dạng Y-m-d (giá trị của input type="date"), ngược lại null. */
    public static function date(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $date = \DateTime::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }

    /**
     * Sắp xếp theo thứ tự trong config (vd trạng thái new -> in_progress -> solved -> closed)
     * thay vì theo bảng chữ cái. Giá trị lấy từ config nên tin cậy được.
     */
    public static function rank(string $column, array $values): Closure
    {
        $cases = collect(array_values($values))
            ->map(fn ($v, $i) => "WHEN '" . str_replace("'", "''", (string) $v) . "' THEN {$i}")
            ->implode(' ');

        return fn (Builder $q, string $dir) => $q->orderByRaw("CASE {$column} {$cases} ELSE 999 END {$dir}");
    }

    /**
     * Áp dụng sắp xếp.
     *
     * @param  array<string, string|Closure>  $map  khóa sort => tên cột, hoặc closure(Builder, $dir)
     * Khóa không hợp lệ (hoặc không có) thì dùng sắp xếp mặc định. Luôn thêm id giảm dần để phân trang ổn định.
     */
    public static function sort(Builder $query, ?string $key, ?string $dir, array $map, string $defaultKey, string $defaultDir): Builder
    {
        $valid = is_string($key) && array_key_exists($key, $map);
        $key = $valid ? $key : $defaultKey;
        $dir = $valid ? self::dir($dir) : $defaultDir;

        $column = $map[$key];
        $column instanceof Closure ? $column($query, $dir) : $query->orderBy($column, $dir);

        return $query->orderByDesc($query->getModel()->getQualifiedKeyName());
    }
}
