<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser;

use aieuo\mineflow\exception\UndefinedMineflowMethodException;
use aieuo\mineflow\exception\UndefinedMineflowPropertyException;
use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\variable\global\DefaultGlobalMethodVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\parser\node\BinaryExpressionNode;
use aieuo\mineflow\variable\parser\node\ConcatenateNode;
use aieuo\mineflow\variable\parser\node\EvaluableIdentifierNode;
use aieuo\mineflow\variable\parser\node\EvaluableNameNode;
use aieuo\mineflow\variable\parser\node\GlobalMethodNode;
use aieuo\mineflow\variable\parser\node\IdentifierNode;
use aieuo\mineflow\variable\parser\node\MethodNode;
use aieuo\mineflow\variable\parser\node\NameNode;
use aieuo\mineflow\variable\parser\node\Node;
use aieuo\mineflow\variable\parser\node\PropertyNode;
use aieuo\mineflow\variable\parser\node\StringNode;
use aieuo\mineflow\variable\parser\node\ToStringNode;
use aieuo\mineflow\variable\parser\node\UnaryExpressionNode;
use aieuo\mineflow\variable\parser\node\WrappedNode;
use aieuo\mineflow\variable\parser\token\VariableToken;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use function implode;
use function is_numeric;

class VariableEvaluator {
    public function __construct(
        protected readonly VariableRegistry $variables,
        protected readonly bool             $fetchFromGlobalVariable = true,
    ) {
    }

    public function eval(Node $node): Variable {
        if ($node instanceof WrappedNode) {
            return $this->eval($node->getStatement());
        }

        if ($node instanceof StringNode) {
            return new StringVariable($node->getString());
        }

        if ($node instanceof NameNode) {
            return new StringVariable($node->getName());
        }

        if ($node instanceof IdentifierNode) {
            if (is_numeric($node->getName())) {
                return new NumberVariable((float)$node->getName());
            }

            $name = $node->getName();
            if (!$this->fetchFromGlobalVariable) {
                return $this->variables->mustGet($name);
            }

            return $this->variables->get($name) ?? VariableRegistry::global()->mustGet($name);
        }

        if ($node instanceof EvaluableNameNode) {
            return $this->eval($node->getName());
        }

        if ($node instanceof EvaluableIdentifierNode) {
            $name = (string)$this->eval($node->getName());

            if (is_numeric($name)) {
                return new NumberVariable((float)$name);
            }

            if (!$this->fetchFromGlobalVariable) {
                return $this->variables->mustGet($name);
            }

            return $this->variables->get($name) ?? VariableRegistry::global()->mustGet($name);
        }

        if ($node instanceof BinaryExpressionNode) {
            $left = $this->eval($node->getLeft());
            $right = $this->eval($node->getRight());
            return match ($node->getOperator()) {
                VariableToken::PLUS => $left->add($right),
                VariableToken::MINUS => $left->sub($right),
                VariableToken::ASTERISK => $left->mul($right),
                VariableToken::SLASH => $left->div($right),
                default => throw new UnsupportedCalculationException(),
            };
        }

        if ($node instanceof UnaryExpressionNode) {
            $left = NumberVariable::zero();
            $right = $this->eval($node->getRight());
            return match ($node->getOperator()) {
                VariableToken::PLUS => $left->add($right),
                VariableToken::MINUS => $left->sub($right),
                default => throw new UnsupportedCalculationException(),
            };
        }

        if ($node instanceof PropertyNode) {
            $left = $this->eval($node->getLeft());
            $identifier = $this->eval($node->getIdentifier());
            return $left->getProperty((string)$identifier) ?? throw new UndefinedMineflowPropertyException((string)$node->getLeft(), (string)$identifier);
        }

        if ($node instanceof MethodNode) {
            $left = $this->eval($node->getLeft());
            $identifier = $this->eval($node->getIdentifier());
            $arguments = [];
            foreach ($node->getArguments() as $argument) {
                $arguments[] = $this->eval($argument);
            }
            return $left->callMethod((string)$identifier, $arguments) ?? throw new UndefinedMineflowMethodException((string)$node->getLeft(), (string)$identifier);
        }

        if ($node instanceof GlobalMethodNode) {
            $left = new DefaultGlobalMethodVariable();
            $identifier = $this->eval($node->getIdentifier());
            $arguments = [];
            foreach ($node->getArguments() as $argument) {
                $arguments[] = $this->eval($argument);
            }
            return $left->callMethod((string)$identifier, $arguments) ?? throw new UndefinedMineflowMethodException("", (string)$identifier);
        }

        if ($node instanceof ToStringNode) {
            $variable = $this->eval($node->getNode());
            return new StringVariable((string)$variable);
        }

        if ($node instanceof ConcatenateNode) {
            $strings = [];
            foreach ($node->getNodes() as $child) {
                $strings[] = (string)$this->eval($child);
            }
            return new StringVariable(implode("", $strings));
        }

        throw new \RuntimeException("Unknown node type ".$node::class);
    }
}