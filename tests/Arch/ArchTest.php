<?php

declare(strict_types=1);

use PestAnnotator\Plugin;

arch('source files use strict types')
    ->expect('PestAnnotator')
    ->toUseStrictTypes();

arch('data objects are readonly')
    ->expect('PestAnnotator\Data')
    ->toBeReadonly();

arch('renderers are final')
    ->expect('PestAnnotator\Renderers')
    ->toBeFinal();

arch('support classes are final')
    ->expect('PestAnnotator\Support')
    ->toBeFinal();

arch('plugin is final')
    ->expect(Plugin::class)
    ->toBeFinal();

arch('no debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();
