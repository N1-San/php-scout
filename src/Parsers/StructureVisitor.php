<?php
namespace PhpScout\Parsers;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Use_;

class StructureVisitor extends NodeVisitorAbstract {
    public array $classes = [];
    public array $imports = [];

    public function leaveNode(Node $node) {
        if ($node instanceof Class_) {
            $methods = [];
            foreach ($node->getMethods() as $method) {
                $methods[] = $method->name->toString();
            }
            $this->classes[] = [
                'name' => $node->namespacedName ? $node->namespacedName->toString() : $node->name->toString(),
                'methods' => $methods
            ];
        }

        if ($node instanceof Use_) {
            foreach ($node->uses as $use) {
                $this->imports[] = $use->name->toString();
            }
        }
        return null;
    }
}