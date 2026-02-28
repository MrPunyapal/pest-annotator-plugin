<?php

declare(strict_types=1);

use PestAnnotator\Data\MethodComplexity;

it('calculates risk score based on complexity and coverage', function (): void {
    $method = new MethodComplexity(
        name: 'process',
        cyclomaticComplexity: 10,
        coveragePercentage: 0.0,
    );

    expect($method->riskScore())->toBe(10.0)
        ->and($method->isHighRisk())->toBeTrue();
});

it('reports zero risk when fully covered', function (): void {
    $method = new MethodComplexity(
        name: 'handle',
        cyclomaticComplexity: 15,
        coveragePercentage: 100.0,
    );

    expect($method->riskScore())->toBe(0.0)
        ->and($method->isHighRisk())->toBeFalse();
});

it('reports not high risk when complexity is low', function (): void {
    $method = new MethodComplexity(
        name: 'simple',
        cyclomaticComplexity: 3,
        coveragePercentage: 0.0,
    );

    expect($method->isHighRisk())->toBeFalse();
});

it('calculates partial risk score', function (): void {
    $method = new MethodComplexity(
        name: 'render',
        cyclomaticComplexity: 20,
        coveragePercentage: 50.0,
    );

    expect($method->riskScore())->toBe(10.0)
        ->and($method->isHighRisk())->toBeFalse();
});
