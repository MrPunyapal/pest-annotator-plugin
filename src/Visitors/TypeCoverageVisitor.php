<?php

declare(strict_types=1);

namespace PestAnnotator\Visitors;

use PestAnnotator\Data\MissingTypeInfo;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;

final class TypeCoverageVisitor extends NodeVisitorAbstract
{
    private string $currentClass = '';

    private int $totalDeclarations = 0;

    private int $typedDeclarations = 0;

    /** @var array<int, MissingTypeInfo> */
    private array $missingTypes = [];

    public function enterNode(Node $node): null
    {
        if ($node instanceof Class_ || $node instanceof Trait_) {
            $this->currentClass = $node->namespacedName?->toString() ?? ($node->name?->toString() ?? 'anonymous');
        }

        if ($node instanceof ClassMethod) {
            $this->analyzeMethod($node);
        }

        if ($node instanceof Property) {
            $this->analyzeProperty($node);
        }

        return null;
    }

    public function getTotalDeclarations(): int
    {
        return $this->totalDeclarations;
    }

    public function getTypedDeclarations(): int
    {
        return $this->typedDeclarations;
    }

    /** @return array<int, MissingTypeInfo> */
    public function getMissingTypes(): array
    {
        return $this->missingTypes;
    }

    public function reset(): void
    {
        $this->currentClass = '';
        $this->totalDeclarations = 0;
        $this->typedDeclarations = 0;
        $this->missingTypes = [];
    }

    private function analyzeMethod(ClassMethod $method): void
    {
        $methodName = $method->name->toString();

        if ($methodName === '__construct') {
            $this->analyzeConstructorParams($method);

            return;
        }

        $this->totalDeclarations++;

        if ($method->returnType !== null) {
            $this->typedDeclarations++;
        } else {
            $this->missingTypes[] = new MissingTypeInfo(
                kind: 'return',
                name: $methodName,
                line: $method->getStartLine(),
                context: $methodName,
            );
        }

        foreach ($method->params as $param) {
            $this->totalDeclarations++;

            if ($param->type !== null) {
                $this->typedDeclarations++;
            } else {
                $paramName = $param->var instanceof Node\Expr\Variable ? (string) $param->var->name : 'unknown';
                $this->missingTypes[] = new MissingTypeInfo(
                    kind: 'param',
                    name: '$'.$paramName,
                    line: $param->getStartLine(),
                    context: $methodName,
                );
            }
        }
    }

    private function analyzeConstructorParams(ClassMethod $method): void
    {
        foreach ($method->params as $param) {
            if ($param->flags !== 0) {
                $this->totalDeclarations++;

                if ($param->type !== null) {
                    $this->typedDeclarations++;
                } else {
                    $paramName = $param->var instanceof Node\Expr\Variable ? (string) $param->var->name : 'unknown';
                    $this->missingTypes[] = new MissingTypeInfo(
                        kind: 'property',
                        name: '$'.$paramName,
                        line: $param->getStartLine(),
                        context: '__construct',
                    );
                }

                continue;
            }

            $this->totalDeclarations++;

            if ($param->type !== null) {
                $this->typedDeclarations++;
            } else {
                $paramName = $param->var instanceof Node\Expr\Variable ? (string) $param->var->name : 'unknown';
                $this->missingTypes[] = new MissingTypeInfo(
                    kind: 'param',
                    name: '$'.$paramName,
                    line: $param->getStartLine(),
                    context: '__construct',
                );
            }
        }

        if ($method->returnType === null) {
            // constructors implicitly return void, so we don't count them
        }
    }

    private function analyzeProperty(Property $property): void
    {
        foreach ($property->props as $prop) {
            $this->totalDeclarations++;

            if ($property->type !== null) {
                $this->typedDeclarations++;
            } else {
                $this->missingTypes[] = new MissingTypeInfo(
                    kind: 'property',
                    name: '$'.$prop->name->toString(),
                    line: $property->getStartLine(),
                    context: $this->currentClass,
                );
            }
        }
    }
}
