<?php

namespace Mralston\Mug\Support;

use Carbon\CarbonImmutable;
use Closure;

/**
 * Reads typed values out of a MUG response decoded by Json::decode(), where every number arrives as
 * a string. A missing key or a null gives null (or an empty list), since MUG omits and nulls fields
 * freely and its API description marks none of them as required.
 */
final class Values
{
    /**
     * The time zone MUG's date-times are read in when they carry no offset of their own: they are
     * UK tariff and industry dates.
     */
    public const string MUG_TIME_ZONE = 'Europe/London';

    /**
     * @param  array<string, mixed>  $data
     */
    public static function string(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return $value === null ? null : (string) $value;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function int(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function bool(array $data, string $key): ?bool
    {
        $value = $data[$key] ?? null;

        return is_bool($value) ? $value : null;
    }

    /**
     * A decimal number as the string MUG wrote it, never a float.
     *
     * @param  array<string, mixed>  $data
     */
    public static function decimal(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? Json::plainDecimal((string) $value) : null;
    }

    /**
     * A date-time in UTC. One without an offset is read as UK time.
     *
     * @param  array<string, mixed>  $data
     */
    public static function dateTime(array $data, string $key): ?CarbonImmutable
    {
        $value = $data[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return CarbonImmutable::parse($value, self::MUG_TIME_ZONE)->utc();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    public static function strings(array $data, string $key): array
    {
        return array_map(strval(...), self::list($data, $key));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    public static function ints(array $data, string $key): array
    {
        return array_map(intval(...), self::list($data, $key));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    public static function objects(array $data, string $key): array
    {
        return array_values(array_filter(self::list($data, $key), is_array(...)));
    }

    /**
     * @template T
     *
     * @param  array<string, mixed>  $data
     * @param  Closure(array<string, mixed>): T  $make
     * @return T|null
     */
    public static function object(array $data, string $key, Closure $make): mixed
    {
        $value = $data[$key] ?? null;

        return is_array($value) ? $make($value) : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<mixed>
     */
    private static function list(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        return is_array($value) ? array_values(array_filter($value, fn (mixed $item): bool => $item !== null)) : [];
    }
}
