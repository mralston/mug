<?php

use Mralston\Mug\Support\Json;

it('keeps every number as the decimal string it was written as', function () {
    $decoded = Json::decode('{"rate": 24.57, "count": 3, "negative": -0.125, "list": [1.10, 2]}');

    expect($decoded)->toBe([
        'rate' => '24.57',
        'count' => '3',
        'negative' => '-0.125',
        'list' => ['1.10', '2'],
    ]);
});

it('leaves numbers inside strings, escaped quotes, booleans and nulls alone', function () {
    $decoded = Json::decode('{"text": "costs 24.57 \"p\" 12", "flag": true, "off": false, "nothing": null}');

    expect($decoded)->toBe([
        'text' => 'costs 24.57 "p" 12',
        'flag' => true,
        'off' => false,
        'nothing' => null,
    ]);
});

it('writes exponent numbers out in full', function (string $written, string $plain) {
    expect(Json::plainDecimal($written))->toBe($plain);
})->with([
    ['2.73E1', '27.3'],
    ['1.5e-3', '0.0015'],
    ['-4E2', '-400'],
    ['1.25E+2', '125'],
    ['5E0', '5'],
    ['24.57', '24.57'],
]);

it('throws on a body that is not JSON', function () {
    Json::decode('<html>Access denied</html>');
})->throws(JsonException::class);
