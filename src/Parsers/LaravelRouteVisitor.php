<?php

namespace PhpScout\Parsers;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\StaticCall;

class LaravelRouteVisitor extends NodeVisitorAbstract {
    public array $routes = [];

    public function leaveNode(Node $node) {
        // Detect Route::get(), Route::post(), etc.
        if ($node instanceof StaticCall && $node->class->toString() === 'Route') {
            $method = $node->name->toString();
            $args = $node->args;

            if (count($args) >= 2) {
                $uri = $args[0]->value->value ?? 'unknown';
                $action = $args[1]->value;

                $controllerAction = 'Closure or Array';
                
                // Handle 'Controller@method' string syntax
                if ($action instanceof Node\Scalar\String_) {
                    $controllerAction = $action->value;
                } 
                // Handle [Controller::class, 'method'] syntax
                elseif ($action instanceof Node\Expr\Array_) {
                    $controllerAction = $this->resolveArrayAction($action);
                }

                $this->routes[] = [
                    'http_method' => strtoupper($method),
                    'uri' => $uri,
                    'action' => $controllerAction
                ];
            }
        }
        return null;
    }

    private function resolveArrayAction(Node\Expr\Array_ $node): string {
        // Logic to extract Controller::class and 'method'
        return "ControllerArrayDefinition";
    }
}