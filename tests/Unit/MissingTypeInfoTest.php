<?php

declare(strict_types=1);

use PestAnnotator\Data\MissingTypeInfo;

it('formats label correctly', function (): void {
    $info = new MissingTypeInfo(
        kind: 'return',
        name: 'process',
        line: 15,
        context: 'process',
    );

    expect($info->label())->toBe('return process in process() L15');
});

it('formats param label correctly', function (): void {
    $info = new MissingTypeInfo(
        kind: 'param',
        name: '$amount',
        line: 20,
        context: 'charge',
    );

    expect($info->label())->toBe('param $amount in charge() L20');
});
