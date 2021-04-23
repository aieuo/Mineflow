<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UndefinedMineflowMethodException;
use aieuo\mineflow\exception\UndefinedMineflowPropertyException;
use aieuo\mineflow\exception\UndefinedMineflowVariableException;
use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemFactory;
use pocketmine\utils\Config;

class VariableHelper {

    /** @var Variable[] */
    private $variables = [];

    /** @var Config */
    private $file;

    public function __construct(Config $file) {
        $this->file = $file;
        $this->file->setJsonOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);

        foreach ($file->getAll() as $name => $data) {
            $this->variables[$name] = Variable::fromArray($data);
        }
    }

    public function exists(string $name): bool {
        return isset($this->variables[$name]);
    }

    public function get(string $name): ?Variable {
        return $this->variables[$name] ?? null;
    }

    public function getNested(string $name): ?Variable {
        $names = explode(".", $name);
        $name = array_shift($names);
        if (!$this->exists($name)) return null;

        $variable = $this->get($name);
        foreach ($names as $name1) {
            if (!($variable instanceof Variable)) return null;
            $variable = $variable->getValueFromIndex($name1);
        }
        return $variable;
    }

    public function add(string $name, Variable $variable): void {
        $this->variables[$name] = $variable;
    }

    /**
     * @param String $name
     * @return void
     */
    public function delete(string $name): void {
        unset($this->variables[$name]);

        $this->file->remove($name);
    }

    public function saveAll(): void {
        foreach ($this->variables as $name => $variable) {
            if (!($variable instanceof \JsonSerializable) and $name !== "") continue;
            $this->file->set($name, $variable);
        }
        $this->file->save();
    }

    public function findVariables(string $string): array {
        $variables = [];
        if (preg_match_all("/{(.+?)}/u", $string, $matches)) {
            foreach ($matches[1] as $name) {
                $variables[] = $name;
            }
        }
        return $variables;
    }

    /**
     * @param string $string
     * @param Variable[] $variables
     * @param FlowItemExecutor|null $executor
     * @param bool $global
     * @return string
     */
    public function replaceVariables(string $string, array $variables = [], ?FlowItemExecutor $executor = null, bool $global = true): string {
        $limit = 10;
        while (preg_match_all("/({(?:[^{}]+|(?R))*})/u", $string, $matches)) {
            foreach ($matches[0] as $name) {
                $name = substr($name, 1, -1);
                if (strpos($name, "{") !== false and strpos($name, "}") !== false) {
                    $replaced = $this->replaceVariables($name, $variables, $executor, $global);
                    $string = str_replace($name, $replaced, $string);
                    $name = $replaced;
                }
                $string = $this->replaceVariable($string, $name, $variables, $executor, $global);
            }
            if (--$limit < 0) break;
        }
        return $string;
    }

    /**
     * @param string $string
     * @param string $replace
     * @param FlowItemExecutor|null $executor
     * @param Variable[] $variables
     * @param bool $global
     * @return string
     */
    public function replaceVariable(string $string, string $replace, array $variables = [], ?FlowItemExecutor $executor = null, bool $global = true): string {
        if (strpos($string, "{".$replace."}") === false) return $string;

        $tokens = $this->lexer($replace);
        $ast = $this->parse($tokens);

        if (is_string($ast)) $result = $this->mustGetVariableNested($ast, $variables, $global);
        elseif ($ast instanceof Variable) $result = (string)$ast;
        else $result = (string)$this->run($ast, $executor, $variables, $global);

        return str_replace("{".$replace."}", $result, $string);
    }

    public function lexer(string $source): array {
        $source = preg_replace("/\[(.*?)]/u", ".$1", $source);
        return preg_split("/(\d+(?:\.\d+)?|[^\s.,+-\/*()]+(?:\.[^\s.,+-\/*()]+)*)|\s|(.)/u", $source, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

    /**
     * @param array $tokens
     * @param int $priority
     * @return array|Variable|string
     */
    public function parse(array &$tokens, int $priority = 0) {
        $rules = [
            ["type" => 1, "ops" => [","]],
            ["type" => 0, "ops" => ["+", "-"]], // 1 + 2, 1 - 2
            ["type" => 0, "ops" => ["*", "/"]], // 1 * 2, 1 / 2
            ["type" => 2, "ops" => ["+", "-"]], // +1, -1
            ["type" => 3, "ops" => ["("]], //method aiueo(1)
            ["type" => 4, "ops" => ["("]], // (1 + 2)
        ];

        if (!isset($rules[$priority])) {
            $value = array_shift($tokens);
            return is_numeric($value) ? new NumberVariable($value) : $value;
        }

        $type = $rules[$priority]["type"];
        $ops = $rules[$priority]["ops"];

        if ($type === 1) {
            $left = $this->parse($tokens, $priority + 1);
            $list = [$left];
            while (count($tokens) > 0 and in_array($tokens[0], $ops, true)) {
                array_shift($tokens);
                $list[] = $this->parse($tokens, $priority + 1);
            }
            return count($list) > 1 ? $list : $left;
        }

        if (($type === 2 or $type === 4) and !in_array($tokens[0], $ops, true)) {
            return $this->parse($tokens, $priority + 1);
        }

        if ($type === 2) {
            return ["left" => 0, "op" => array_shift($tokens), "right" => $this->parse($tokens, $priority + 1)];
        }
        if ($type === 4) {
            array_shift($tokens); // (
            $right = $this->parse($tokens, 0);
            array_shift($tokens); // )
            return $right;
        }

        if ($type === 3) {
            $left = $this->parse($tokens, $priority + 1);
            while (isset($tokens[0]) and in_array($tokens[0], $ops, true)) {
                array_shift($tokens); // (
                $right = $tokens[0] === ")" ? "" : $this->parse($tokens, 0);
                array_shift($tokens); // )
                $tmp = $left;
                $left = ["left" => $tmp, "op" => "()", "right" => $right];
            }
            return $left;
        }

        $left = $this->parse($tokens, $priority + 1);
        while (isset($tokens[0]) and in_array($tokens[0], $ops, true)) {
            $tmp = $left;
            $left = ["left" => $tmp, "op" => array_shift($tokens), "right" => $this->parse($tokens, $priority + 1)];
        }
        return $left;
    }

    public function run(array $ast, ?FlowItemExecutor $executor = null, array $variables = [], bool $global = false): Variable {
        if (!isset($ast["left"])) {
            $result = "";
            foreach ($ast as $value) {
                if (is_array($value)) $result .= (",".$this->run($value, $executor, $variables, $global));
                else $result .= (",".$value);
            }
            return $this->mustGetVariableNested(substr($result, 1), $variables, $global);
        }

        $left = is_array($ast["left"]) ? $this->run($ast["left"], $executor, $variables, $global) : $ast["left"];
        $right = is_array($ast["right"]) ? $this->run($ast["right"], $executor, $variables, $global) : $ast["right"];
        $op = $ast["op"];

        if (is_string($left)) {
            if ($op === "()") {
                if ($executor === null) throw new UnsupportedCalculationException();
                return $this->runMethodCall($left, is_string($right) ? [$right] : $right, $executor, $variables, $global);
            }

            $left = $this->mustGetVariableNested($left, $variables, $global);
        }
        if (is_string($right)) {
            $right = $this->mustGetVariableNested($right, $variables, $global);
        }

        switch ($op) {
            case "+":
                return $left->add($right);
            case "-":
                return $left->sub($right);
            case "*":
                return $left->mul($right);
            case "/":
                return $left->div($right);
        }

        throw new UnsupportedCalculationException();
    }

    public function runMethodCall(string $left, array $right, FlowItemExecutor $executor, array $variables, bool $global): Variable {
        $tmp = explode(".", $left);
        $name = array_pop($tmp);
        $target = implode(".", $tmp);

        if ($target === "") {
            try {
                $result = $this->runAction($name, $right, $executor);
                if (is_bool($result)) return new BoolVariable($result);
                if (is_numeric($result)) return new NumberVariable($result);
                return new StringVariable($result);
            } catch (\UnexpectedValueException $e) {
                return new StringVariable($e->getMessage());
            }
        }

        $variable = $this->mustGetVariableNested($target, $variables, $global);
        $result = $variable->callMethod($name, $right);
        if ($result === null) throw new UndefinedMineflowMethodException($target, $name);
        return $result;
    }

    public function runAction(string $name, array $parameters, FlowItemExecutor $executor) {
        $action = FlowItemFactory::get($name, true);
        if ($action === null) throw new \UnexpectedValueException("§cUnknown action id {$name}");
        if (!$action->allowDirectCall()) throw new \UnexpectedValueException("§cCannot call direct {$name}");

        $class = get_class($action);

        /** @var FlowItem $newAction */
        $newAction = new $class(...$parameters);
        $generator = $newAction->execute($executor);
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        /** @noinspection PhpUnusedLocalVariableInspection */
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($generator as $_) {
        }
        return $generator->getReturn();
    }

    public function mustGetVariableNested(string $name, array $variables = [], bool $global = false): Variable {
        $names = explode(".", $name);
        $name = array_shift($names);
        if (!isset($variables[$name]) and !$this->exists($name)) throw new UndefinedMineflowVariableException($name);

        $variable = $variables[$name] ?? ($global ? $this->get($name) : null);
        if ($variable === null) throw new UndefinedMineflowVariableException($name);

        $tmp = $name;
        foreach ($names as $name1) {
            $variable = $variable->getValueFromIndex($name1);

            if ($variable === null) {
                throw new UndefinedMineflowPropertyException($tmp, $name1);
            }
            $tmp .= ".".$name1;
        }

        return $variable;
    }

    public function isVariableString(string $variable): bool {
        return (bool)preg_match("/^{[^{}\[\].]+}$/u", $variable);
    }

    public function containsVariable(string $variable): bool {
        return (bool)preg_match("/{.+}/u", $variable);
    }

    public function getType(string $string): int {
        if (strpos($string, "(str)") === 0) {
            $type = Variable::STRING;
        } elseif (strpos($string, "(num)") === 0) {
            $type = Variable::NUMBER;
        } elseif (is_numeric($string)) {
            $type = Variable::NUMBER;
        } else {
            $type = Variable::STRING;
        }
        return $type;
    }

    public function currentType(string $value) {
        if (mb_strpos($value, "(str)") === 0) {
            $newValue = mb_substr($value, 5);
        } elseif (mb_strpos($value, "(num)") === 0) {
            $newValue = mb_substr($value, 5);
            if (!$this->containsVariable($value)) $newValue = (float)$value;
        } elseif (is_numeric($value)) {
            $newValue = (float)$value;
        } else {
            $newValue = $value;
        }
        return $newValue;
    }

    public function toVariableArray(array $data): array {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (array_values($value) === $value) {
                    $result[$key] = new ListVariable($this->toVariableArray($value));
                } else {
                    $result[$key] = new MapVariable($this->toVariableArray($value));
                }
            } elseif (is_numeric($value)) {
                $result[$key] = new NumberVariable((float)$value);
            } else {
                $result[$key] = new StringVariable($value);
            }
        }
        return $result;
    }
}