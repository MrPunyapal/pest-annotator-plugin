<?php

declare(strict_types=1);

use PestAnnotator\Support\ArgumentParser;

it('parses --coverage and --annotate flags', function (): void {
    $parser = new ArgumentParser;
    $result = $parser->parse(['--coverage', '--annotate', '--filter=unit']);

    expect($result)->toBe(['--coverage', '--filter=unit'])
        ->and($parser->isCoverageEnabled())->toBeTrue()
        ->and($parser->isAnnotateEnabled())->toBeTrue();
});

it('parses --annotate-methods flag', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--coverage', '--annotate-methods']);

    expect($parser->isAnnotateEnabled())->toBeTrue()
        ->and($parser->shouldShowMethods())->toBeTrue();
});

it('parses --annotate-covered flag', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--coverage', '--annotate-covered']);

    expect($parser->isAnnotateEnabled())->toBeTrue()
        ->and($parser->shouldShowCovered())->toBeTrue();
});

it('parses --annotate-types flag', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--coverage', '--annotate-types']);

    expect($parser->isAnnotateEnabled())->toBeTrue()
        ->and($parser->shouldShowTypes())->toBeTrue();
});

it('parses --annotate-complexity flag', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--coverage', '--annotate-complexity']);

    expect($parser->isAnnotateEnabled())->toBeTrue()
        ->and($parser->shouldShowComplexity())->toBeTrue();
});

it('parses --annotate-mutations flag', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--coverage', '--annotate-mutations']);

    expect($parser->isAnnotateEnabled())->toBeTrue()
        ->and($parser->shouldShowMutations())->toBeTrue();
});

it('parses --annotate-diff flag', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--coverage', '--annotate-diff']);

    expect($parser->isAnnotateEnabled())->toBeTrue()
        ->and($parser->shouldShowDiff())->toBeTrue();
});

it('parses --annotate-save-baseline flag', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--coverage', '--annotate-save-baseline']);

    expect($parser->isAnnotateEnabled())->toBeTrue()
        ->and($parser->shouldSaveBaseline())->toBeTrue();
});

it('parses --annotate-min value flag', function (): void {
    $parser = new ArgumentParser;
    $result = $parser->parse(['--coverage', '--annotate-min=80']);

    expect($result)->toBe(['--coverage'])
        ->and($parser->isAnnotateEnabled())->toBeTrue()
        ->and($parser->getMinCoverage())->toBe(80);
});

it('parses --annotate-namespace value flag', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--coverage', '--annotate-namespace=App\\Services']);

    expect($parser->getNamespaceFilter())->toBe('App\\Services');
});

it('parses --annotate-exclude value flag', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--coverage', '--annotate-exclude=App\\Models']);

    expect($parser->getNamespaceExclude())->toBe('App\\Models');
});

it('parses --annotate-format value flag', function (): void {
    $parser = new ArgumentParser;
    $result = $parser->parse(['--coverage', '--annotate-format=json']);

    expect($result)->toBe(['--coverage'])
        ->and($parser->getExportFormat())->toBe('json');
});

it('parses --annotate-output value flag', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--coverage', '--annotate-output=report.json']);

    expect($parser->getExportOutput())->toBe('report.json');
});

it('keeps --coverage in arguments', function (): void {
    $parser = new ArgumentParser;
    $result = $parser->parse(['--coverage', '--annotate']);

    expect($result)->toContain('--coverage');
});

it('returns defaults when no flags provided', function (): void {
    $parser = new ArgumentParser;
    $parser->parse(['--filter=unit']);

    expect($parser->isCoverageEnabled())->toBeFalse()
        ->and($parser->isAnnotateEnabled())->toBeFalse()
        ->and($parser->shouldShowMethods())->toBeFalse()
        ->and($parser->shouldShowCovered())->toBeFalse()
        ->and($parser->shouldShowTypes())->toBeFalse()
        ->and($parser->shouldShowComplexity())->toBeFalse()
        ->and($parser->shouldShowMutations())->toBeFalse()
        ->and($parser->shouldShowDiff())->toBeFalse()
        ->and($parser->shouldSaveBaseline())->toBeFalse()
        ->and($parser->getMinCoverage())->toBeNull()
        ->and($parser->getNamespaceFilter())->toBeNull()
        ->and($parser->getNamespaceExclude())->toBeNull()
        ->and($parser->getExportFormat())->toBeNull()
        ->and($parser->getExportOutput())->toBeNull();
});
