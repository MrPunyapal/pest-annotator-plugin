<?php

declare(strict_types=1);

arch('source files use strict types')
    ->expect('PestCoverageAnnotator')
    ->toUseStrictTypes();

arch('data objects are readonly')
    ->expect('PestCoverageAnnotator\Data')
    ->toBeReadonly();

arch('parsers are final')
    ->expect('PestCoverageAnnotator\Parsers')
    ->toBeFinal();

arch('commands are final')
    ->expect('PestCoverageAnnotator\Commands')
    ->toBeFinal();

arch('no debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();
