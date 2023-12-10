<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser;

use aieuo\mineflow\exception\UndefinedMineflowMethodException;
use aieuo\mineflow\exception\UndefinedMineflowPropertyException;
use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\parser\node\BinaryExpressionNode;
use aieuo\mineflow\variable\parser\node\IdentifierNode;
use aieuo\mineflow\variable\parser\node\MethodNode;
use aieuo\mineflow\variable\parser\node\NameNode;
use aieuo\mineflow\variable\parser\node\Node;
use aieuo\mineflow\variable\parser\node\PropertyNode;
use aieuo\mineflow\variable\parser\node\UnaryExpressionNode;
use aieuo\mineflow\variable\parser\node\WrappedNode;
use aieuo\mineflow\variable\parser\token\VariableTokenType;
use aieuo\mineflow\variable\registry\VariableRegistry;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use function is_numeric;

class VariableEvaluator {
    public function __construct(
        private readonly VariableRegistry $variables,
        private readonly bool             $fetchFromGlobalVariable = true,
        private readonly array            $tmpVariables = [],
    ) {
    }

    public function eval(Node $node, string &$stmt = null): Variable {
        if ($node instanceof WrappedNode) {
            $var = $this->eval($node->getStatement(), $stmtStmt);
            $stmt = "(".$stmtStmt.")";
            return $var;
        }

        if ($node instanceof NameNode) {
            $stmt = $node->getName();
            return $this->tmpVariables[$node->getName()] ?? new StringVariable($node->getName());
        }

        if ($node instanceof IdentifierNode) {
            $stmt = $node->getName();

            if (is_numeric($node->getName())) {
                return new NumberVariable((float)$node->getName());
            }

            $name = (string)($this->tmpVariables[$node->getName()] ?? $node->getName());
            if (!$this->fetchFromGlobalVariable) {
                return $this->variables->mustGet($name);
            }

            return $this->variables->get($name) ?? VariableRegistry::global()->mustGet($name);
        }

        if ($node instanceof BinaryExpressionNode) {
            $left = $this->eval($node->getLeft(), $leftStmt);
            $right = $this->eval($node->getRight(), $rightStmt);
            $stmt = $leftStmt." ".$node->getOperator()." ".$rightStmt;
            return match ($node->getOperator()) {
                VariableTokenType::PLUS => $left->add($right),
                VariableTokenType::MINUS => $left->sub($right),
                VariableTokenType::ASTERISK => $left->mul($right),
                VariableTokenType::SLASH => $left->div($right),
                default => throw new UnsupportedCalculationException(),
            };
        }

        if ($node instanceof UnaryExpressionNode) {
            $left = NumberVariable::zero();
            $right = $this->eval($node->getRight(), $rightStmt);
            $stmt = $node->getOperator().$rightStmt;
            return match ($node->getOperator()) {
                VariableTokenType::PLUS => $left->add($right),
                VariableTokenType::MINUS => $left->sub($right),
                default => throw new UnsupportedCalculationException(),
            };
        }

        if ($node instanceof PropertyNode) {
            $left = $this->eval($node->getLeft(), $leftStmt);
            $identifier = $this->eval($node->getIdentifier(), $identifierStmt);
            $stmt = $leftStmt.".".$identifierStmt;
            return $left->getProperty($identifier->getValue()) ?? throw new UndefinedMineflowPropertyException($leftStmt, (string)$identifier->getValue());
        }

        if ($node instanceof MethodNode) {
            $left = $this->eval($node->getLeft(), $leftStmt);
            $identifier = $this->eval($node->getIdentifier(), $identifierStmt);
            $stmt = $leftStmt.".".$identifierStmt."(";
            $arguments = [];
            $argumentStmts = [];
            foreach ($node->getArguments() as $argument) {
                $arguments[] = $this->eval($argument, $argumentStmt);
                $argumentStmts[] = $argumentStmt;
            }
            $stmt .= implode(", ", $argumentStmts).")";
            return $left->callMethod($identifier->getValue(), $arguments) ?? throw new UndefinedMineflowMethodException($leftStmt, (string)$identifier->getValue());
        }

        throw new \RuntimeException("Unknown node type ".$node::class);
    }

}
