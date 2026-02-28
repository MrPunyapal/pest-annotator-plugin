<?php

declare(strict_types=1);

use PestAnnotator\Plugin;
use Symfony\Component\Console\Output\BufferedOutput;

it('detects --coverage flag in arguments', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $result = $plugin->handleArguments(['--coverage', '--filter=unit']);

    expect($result)->toBe(['--coverage', '--filter=unit']);
});

it('passes through arguments unchanged', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $args = ['--filter=unit', '--parallel'];
    $result = $plugin->handleArguments($args);

    expect($result)->toBe($args);
});

it('returns exit code unchanged when coverage is not enabled', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $plugin->handleArguments(['--filter=unit']);

    expect($plugin->addOutput(0))->toBe(0)
        ->and($plugin->addOutput(1))->toBe(1);
});

it('returns exit code unchanged when tests failed', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $plugin->handleArguments(['--coverage']);

    expect($plugin->addOutput(1))->toBe(1);
});
