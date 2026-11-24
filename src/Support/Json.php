<?php

namespace Mralston\Mug\Support;

use JsonException;

/**
 * Decodes MUG's JSON with every number kept as the decimal string it was written as.
 *
 * PHP's decoder turns a number with a fraction into a float, which cannot hold most prices exactly
 * (24.57 becomes 24.570000000000000284...). Quoting each number token before decoding keeps the
 * published digits, so rates can go straight into BcMath\Number or a decimal column. A number in
 * exponent form is written out in full, since BcMath does not read exponents.
 */
final class Json
{
    /**
     * @throws JsonException
     */
    public static function decode(string $json): mixed
    {
        return json_decode(self::quoteNumbers($json), true, 512, JSON_THROW_ON_ERROR);
    }

    private static function quoteNumbers(string $json): string
    {
        $result = '';
        $length = strlen($json);
        $position = 0;

        while ($position < $length) {
            $character = $json[$position];

            if ($character === '"') {
                $end = self::endOfString($json, $position);
                $result .= substr($json, $position, $end - $position);
                $position = $end;

                continue;
            }

            if ($character === '-' || ctype_digit($character)) {
                $span = strspn($json, '0123456789+-.eE', $position);
                $result .= '"'.self::plainDecimal(substr($json, $position, $span)).'"';
                $position += $span;

                continue;
            }

            $result .= $character;
            $position++;
        }

        return $result;
    }

    /**
     * The offset just after the closing quote of the string that opens at $start.
     */
    private static function endOfString(string $json, int $start): int
    {
        $length = strlen($json);
        $position = $start + 1;

        while ($position < $length) {
            if ($json[$position] === '\\') {
                $position += 2;

                continue;
            }

            if ($json[$position] === '"') {
                return $position + 1;
            }

            $position++;
        }

        return $length;
    }

    /**
     * Writes a JSON number without an exponent, keeping every digit: "1.5E-3" becomes "0.0015".
     */
    public static function plainDecimal(string $number): string
    {
        if (! preg_match('/^(-?)(\d+)(?:\.(\d+))?[eE]([+-]?\d+)$/', $number, $parts)) {
            return $number;
        }

        [, $sign, $whole, $fraction, $exponent] = $parts;
        $digits = $whole.$fraction;
        $point = strlen($whole) + (int) $exponent;

        if ($point <= 0) {
            $plain = '0.'.str_repeat('0', -$point).$digits;
        } elseif ($point >= strlen($digits)) {
            $plain = $digits.str_repeat('0', $point - strlen($digits));
        } else {
            $plain = substr($digits, 0, $point).'.'.substr($digits, $point);
        }

        $plain = ltrim($plain, '0');

        if ($plain === '' || $plain[0] === '.') {
            $plain = '0'.$plain;
        }

        return $sign.$plain;
    }
}
