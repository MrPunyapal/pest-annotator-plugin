<?php

declare(strict_types=1);

use PestAnnotator\Plugin;
use Symfony\Component\Console\Output\BufferedOutput;

it('detects --coverage and --annotate flags in arguments', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $result = $plugin->handleArguments(['--coverage', '--annotate', '--filter=unit']);

    expect($result)->toBe(['--coverage', '--filter=unit']);
});

it('detects --annotate-methods flag', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $result = $plugin->handleArguments(['--coverage', '--annotate-methods']);

    expect($result)->toBe(['--coverage']);
});

it('detects --annotate-covered flag', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $result = $plugin->handleArguments(['--coverage', '--annotate-covered']);

    expect($result)->toBe(['--coverage']);
});

it('passes through arguments unchanged without annotate flags', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $args = ['--filter=unit', '--parallel'];
    $result = $plugin->handleArguments($args);

    expect($result)->toBe($args);
});

it('returns exit code unchanged when annotate is not enabled', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $plugin->handleArguments(['--coverage']);

    expect($plugin->addOutput(0))->toBe(0);
});

it('returns exit code unchanged when coverage is not enabled', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $plugin->handleArguments(['--annotate']);

    expect($plugin->addOutput(0))->toBe(0);
});

it('returns exit code unchanged when tests failed', function (): void {
    $output = new BufferedOutput;
    $plugin = new Plugin($output);

    $plugin->handleArguments(['--coverage', '--annotate']);

    expect($plugin->addOutput(1))->toBe(1);
});
