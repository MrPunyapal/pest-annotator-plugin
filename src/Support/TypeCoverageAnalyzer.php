<?php

declare(strict_types=1);

namespace PestAnnotator\Support;

use PestAnnotator\Data\ClassTypeCoverage;
use PestAnnotator\Data\TypeCoverageReport;
use PestAnnotator\Visitors\TypeCoverageVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

final class TypeCoverageAnalyzer
{
    /**
     * Analyzes PHP files for type coverage.
     *
     * @param  array<int, string>  $filePaths
     */
    public function analyze(array $filePaths): TypeCoverageReport
    {
        $parser = (new ParserFactory)->createForHostVersion();
        $classes = [];

        foreach ($filePaths as $filePath) {
            if (! file_exists($filePath)) {
                continue;
            }

            $code = file_get_contents($filePath);

            if ($code === false) {
                continue;
            }

            $ast = $parser->parse($code);

            if ($ast === null) {
                continue;
            }

            $visitor = new TypeCoverageVisitor;
            $traverser = new NodeTraverser;
            $traverser->addVisitor(new NameResolver);
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);

            $className = $this->extractClassName($filePath, $code);

            $classes[$className] = new ClassTypeCoverage(
                className: $className,
                filePath: $filePath,
                totalDeclarations: $visitor->getTotalDeclarations(),
                typedDeclarations: $visitor->getTypedDeclarations(),
                missingTypes: $visitor->getMissingTypes(),
            );
        }

        ksort($classes);

        return new TypeCoverageReport($classes);
    }

    private function extractClassName(string $filePath, string $code): string
    {
        if (preg_match('/namespace\s+([\w\\\\]+)\s*;/', $code, $nsMatch)
            && preg_match('/(?:class|trait|interface|enum)\s+(\w+)/', $code, $classMatch)) {
            return $nsMatch[1].'\\'.$classMatch[1];
        }

        if (preg_match('/(?:class|trait|interface|enum)\s+(\w+)/', $code, $classMatch)) {
            return $classMatch[1];
        }

        return basename($filePath, '.php');
    }
}
