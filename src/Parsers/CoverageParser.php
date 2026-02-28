<?php

declare(strict_types=1);

namespace PestCoverageAnnotator\Parsers;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use InvalidArgumentException;
use PestCoverageAnnotator\Data\ClassCoverage;
use PestCoverageAnnotator\Data\CoverageReport;
use RuntimeException;

final class CoverageParser
{
    /**
     * Parses a Clover XML coverage file and returns a structured report.
     *
     * @param array<int, string> $includePrefixes directory prefixes to include (e.g. ['app/', 'src/'])
     */
    public function parse(string $xmlPath, array $includePrefixes = ['app/', 'src/']): CoverageReport
    {
        $this->validateFile($xmlPath);

        $dom = $this->loadXml($xmlPath);
        $xpath = new DOMXPath($dom);

        $files = $xpath->query('//file');

        if (! $files instanceof DOMNodeList || $files->length === 0) {
            return new CoverageReport([]);
        }

        $classes = [];

        foreach ($files as $fileNode) {
            if (! $fileNode instanceof DOMElement) {
                continue;
            }

            $filePath = $fileNode->getAttribute('name');

            if (! $this->shouldIncludeFile($filePath, $includePrefixes)) {
                continue;
            }

            $this->parseFileNode($fileNode, $xpath, $filePath, $classes);
        }

        ksort($classes);

        return new CoverageReport($classes);
    }

    private function validateFile(string $xmlPath): void
    {
        if (! file_exists($xmlPath)) {
            throw new InvalidArgumentException("Coverage file not found: {$xmlPath}");
        }

        if (! is_readable($xmlPath)) {
            throw new InvalidArgumentException("Coverage file is not readable: {$xmlPath}");
        }
    }

    private function loadXml(string $xmlPath): DOMDocument
    {
        $dom = new DOMDocument();

        $previousErrorSetting = libxml_use_internal_errors(true);

        $loaded = $dom->load($xmlPath);

        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorSetting);

        if (! $loaded || $errors !== []) {
            $errorMessages = array_map(
                static fn ($error): string => trim($error->message),
                $errors,
            );

            throw new RuntimeException(
                'Failed to parse coverage XML: ' . implode(', ', $errorMessages)
            );
        }

        return $dom;
    }

    /**
     * Determines whether a file path matches any of the include prefixes.
     *
     * @param array<int, string> $includePrefixes
     */
    private function shouldIncludeFile(string $filePath, array $includePrefixes): bool
    {
        $normalizedPath = str_replace('\\', '/', $filePath);

        foreach ($includePrefixes as $prefix) {
            $normalizedPrefix = str_replace('\\', '/', $prefix);

            if (str_contains($normalizedPath, "/{$normalizedPrefix}")) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parses a single <file> node and populates the classes array.
     *
     * @param array<string, ClassCoverage> $classes
     */
    private function parseFileNode(DOMElement $fileNode, DOMXPath $xpath, string $filePath, array &$classes): void
    {
        $classNodes = $xpath->query('class', $fileNode);

        if (! $classNodes instanceof DOMNodeList || $classNodes->length === 0) {
            return;
        }

        $coveredLines = $this->extractCoveredLines($xpath, $fileNode);

        foreach ($classNodes as $classNode) {
            if (! $classNode instanceof DOMElement) {
                continue;
            }

            $className = $this->resolveClassName($classNode);

            if ($className === '' || $className === '{anonymous}') {
                continue;
            }

            $methods = $this->extractMethods($xpath, $fileNode, $coveredLines);

            if ($methods === []) {
                continue;
            }

            $classes[$className] = new ClassCoverage(
                className: $className,
                filePath: $filePath,
                methods: $methods,
            );
        }
    }

    /**
     * Extracts line coverage data from a file node.
     *
     * @return array<int, int> line number => hit count
     */
    private function extractCoveredLines(DOMXPath $xpath, DOMElement $fileNode): array
    {
        $lineNodes = $xpath->query('line[@type="stmt" or @type="method"]', $fileNode);
        $coveredLines = [];

        if ($lineNodes instanceof DOMNodeList) {
            foreach ($lineNodes as $lineNode) {
                if (! $lineNode instanceof DOMElement) {
                    continue;
                }

                $lineNum = (int) $lineNode->getAttribute('num');
                $count = (int) $lineNode->getAttribute('count');
                $coveredLines[$lineNum] = $count;
            }
        }

        return $coveredLines;
    }

    private function resolveClassName(DOMElement $classNode): string
    {
        $namespace = $classNode->getAttribute('namespace');
        $name = $classNode->getAttribute('name');

        if ($namespace !== '' && $namespace !== 'global') {
            return $namespace . '\\' . $name;
        }

        return $name;
    }

    /**
     * Extracts methods and their coverage status from a file node.
     *
     * @param array<int, int> $coveredLines
     * @return array<string, bool>
     */
    private function extractMethods(DOMXPath $xpath, DOMElement $fileNode, array $coveredLines): array
    {
        $methodNodes = $xpath->query('line[@type="method"]', $fileNode);
        $methods = [];

        if (! $methodNodes instanceof DOMNodeList) {
            return $methods;
        }

        foreach ($methodNodes as $methodNode) {
            if (! $methodNode instanceof DOMElement) {
                continue;
            }

            $methodName = $methodNode->getAttribute('name');
            $count = (int) $methodNode->getAttribute('count');

            if ($methodName === '') {
                continue;
            }

            $methods[$methodName] = $count > 0;
        }

        return $methods;
    }
}
