<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\MineflowMethodErrorException;
use aieuo\mineflow\exception\UndefinedMineflowMethodException;
use aieuo\mineflow\exception\UndefinedMineflowPropertyException;
use aieuo\mineflow\exception\UndefinedMineflowVariableException;
use aieuo\mineflow\exception\UnsupportedCalculationException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemFactory;
use aieuo\mineflow\Main;
use aieuo\mineflow\Mineflow;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\object\AxisAlignedBBVariable;
use aieuo\mineflow\variable\object\BlockVariable;
use aieuo\mineflow\variable\object\ConfigVariable;
use aieuo\mineflow\variable\object\EntityVariable;
use aieuo\mineflow\variable\object\EventVariable;
use aieuo\mineflow\variable\object\HumanVariable;
use aieuo\mineflow\variable\object\InventoryVariable;
use aieuo\mineflow\variable\object\ItemVariable;
use aieuo\mineflow\variable\object\LivingVariable;
use aieuo\mineflow\variable\object\LocationVariable;
use aieuo\mineflow\variable\object\PlayerVariable;
use aieuo\mineflow\variable\object\PositionVariable;
use aieuo\mineflow\variable\object\RecipeVariable;
use aieuo\mineflow\variable\object\ScoreboardVariable;
use aieuo\mineflow\variable\object\ServerVariable;
use aieuo\mineflow\variable\object\UnknownVariable;
use aieuo\mineflow\variable\object\Vector3Variable;
use aieuo\mineflow\variable\object\WorldVariable;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Config;
use function array_is_list;
use function array_map;
use function is_array;
use function is_bool;
use function is_null;
use function is_numeric;
use function preg_match;
use function substr;

class VariableHelper {

    /** @var Variable[] */
    private array $variables = [];

    public function __construct(private Config $file) {
        $this->file->setJsonOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);

        VariableSerializer::init();
        VariableDeserializer::init();
    }

    public function loadVariables(): void {
        foreach ($this->file->getAll() as $name => $data) {
            $variable = VariableDeserializer::deserialize($data);

            if ($variable === null) {
                Main::getInstance()->getLogger()->warning(Language::get("variable.load.failed"));
                continue;
            }

            $this->variables[$name] = $variable;
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
            $variable = $variable->getProperty($name1);
        }
        return $variable;
    }

    public function add(string $name, Variable $variable): void {
        $this->variables[$name] = $variable;
    }

    public function delete(string $name): void {
        unset($this->variables[$name]);

        $this->file->remove($name);
    }

    public function saveAll(): void {
        foreach ($this->variables as $name => $variable) {
            $serialized = VariableSerializer::serialize($variable);

            if ($serialized !== null) {
                $this->file->set($name, $serialized);
            } elseif ($variable instanceof \JsonSerializable) {
                $this->file->set($name, $variable);
            }
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
                if (str_contains($name, "{") and str_contains($name, "}")) {
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
        if (!str_contains($string, "{".$replace."}") or preg_match("/%\d+/u", $replace)) return $string;

        $result = (string)$this->runVariableStatement($replace, $variables, $executor, $global);
        return str_replace("{".$replace."}", $result, $string);
    }

    /**
     * @param string $replace
     * @param Variable[] $variables
     * @param FlowItemExecutor|null $executor
     * @param bool $global
     * @return Variable
     */
    public function runVariableStatement(string $replace, array $variables = [], ?FlowItemExecutor $executor = null, bool $global = true): Variable {
        $tokens = $this->lexer($replace);
        $ast = $this->parse($tokens);
        return $this->runAST($ast, $executor, $variables, $global);
    }

    public function lexer(string $source): array {
        $source = preg_replace("/\[(.*?)]/u", ".$1", $source);

        $tokens = [];
        $token = "";
        $brackets = 0;
        $escape = false;

        foreach (preg_split("//u", $source, -1, PREG_SPLIT_NO_EMPTY) as $char) {
            if ($escape) {
                $token .= $char;
                $escape = false;
                continue;
            }

            switch ($char) {
                case "\\":
                    $escape = true;
                    break;
                case "+":
                case "-":
                case "*":
                case "/":
                case ">":
                case "(":
                case ")":
                    $tokens[] = trim($token);
                    $tokens[] = $char;
                    $token = "";

                    if ($char === "(") {
                        $brackets ++;
                    } elseif ($char === ")") {
                        $brackets --;
                    }
                    break;
                case ",":
                    if ($brackets > 0) {
                        $tokens[] = trim($token);
                        $tokens[] = $char;
                        $token = "";
                    } else {
                        $token .= $char;
                    }
                    break;
                default:
                    $token .= $char;
                    break;
            }
        }
        $tokens[] = trim($token);
        return array_values(array_filter($tokens, fn($token) => $token !== ""));
    }

    public function parse(array &$tokens, int $priority = 0): string|array|Variable {
        $rules = [
            ["type" => 1, "ops" => [","]],
            ["type" => 0, "ops" => [">"]],
            ["type" => 0, "ops" => ["+", "-"]], // 1 + 2, 1 - 2
            ["type" => 0, "ops" => ["*", "/"]], // 1 * 2, 1 / 2
            ["type" => 2, "ops" => ["+", "-"]], // +1, -1
            ["type" => 3, "ops" => ["("]], //method aiueo(1)
            ["type" => 4, "ops" => ["("]], // (1 + 2)
        ];

        if (!isset($rules[$priority])) {
            $value = array_shift($tokens);
            return is_numeric($value) ? new NumberVariable((float)$value) : $value;
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
            $right = $this->parse($tokens);
            array_shift($tokens); // )
            return $right;
        }

        $left = $this->parse($tokens, $priority + 1);
        if ($type === 3) {
            while (isset($tokens[0]) and in_array($tokens[0], $ops, true)) {
                array_shift($tokens); // (
                $right = $tokens[0] === ")" ? "" : $this->parse($tokens);
                array_shift($tokens); // )
                $tmp = $left;
                $left = ["left" => $tmp, "op" => "()", "right" => $right];
            }
            return $left;
        }

        while (isset($tokens[0]) and in_array($tokens[0], $ops, true)) {
            $tmp = $left;
            $left = ["left" => $tmp, "op" => array_shift($tokens), "right" => $this->parse($tokens, $priority + 1)];
        }
        return $left;
    }

    /**
     * @param string|array|Variable $ast
     * @param FlowItemExecutor|null $executor
     * @param array<string, Variable> $variables
     * @param bool $global
     * @return Variable
     */
    public function runAST(string|array|Variable $ast, ?FlowItemExecutor $executor = null, array $variables = [], bool $global = false): Variable {
        if (is_string($ast)) return $this->mustGetVariableNested($ast, $variables, $global);
        if ($ast instanceof Variable) return $ast;

        if (!isset($ast["left"])) {
            $result = "";
            foreach ($ast as $value) {
                if (is_array($value)) $result .= (",".$this->runAST($value, $executor, $variables, $global));
                else $result .= (",".$value);
            }
            return $this->mustGetVariableNested(substr($result, 1), $variables, $global);
        }

        $op = $ast["op"];
        $left = is_array($ast["left"]) ? $this->runAST($ast["left"], $executor, $variables, $global) : $ast["left"];
        if (is_array($ast["right"]) and $op !== ">" and ($op !== "()" or isset($ast["right"]["op"]))) {
            $right = $this->runAST($ast["right"], $executor, $variables, $global);
        } else {
            $right = $ast["right"];
        }

        if (is_string($left)) {
            if ($op === "()") {
                if ($executor === null) throw new UnsupportedCalculationException();
                return $this->runMethodCall($left, !is_array($right) ? [$right] : $right, $executor, $variables, $global);
            }

            $left = $this->mustGetVariableNested($left, $variables, $global);
        }

        if ($op === ">") {
            return $left->map($right, $executor, $variables, $global);
        }

        if (is_string($right)) {
            $right = $this->mustGetVariableNested($right, $variables, $global);
        }

        return match ($op) {
            "+" => $left->add($right),
            "-" => $left->sub($right),
            "*" => $left->mul($right),
            "/" => $left->div($right),
            default => throw new UnsupportedCalculationException(),
        };
    }

    public function runMethodCall(string $left, array $right, FlowItemExecutor $executor, array $variables, bool $global): Variable {
        $tmp = explode(".", $left);
        $name = array_pop($tmp);
        $target = implode(".", $tmp);

        if ($target === "") {
            try {
                $result = $this->runAction($name, $right, $executor);
                if (is_bool($result)) return new BooleanVariable($result);
                if (is_numeric($result)) return new NumberVariable($result);
                return new StringVariable($result);
            } catch (\UnexpectedValueException $e) {
                return new StringVariable($e->getMessage());
            }
        }

        $variable = $this->mustGetVariableNested($target, $variables, $global);
        try {
            $result = $variable->callMethod($name, array_map(function (mixed $arg) {
                if ($arg instanceof Variable) {
                    $arg = $this->variableArrayToArray([$arg])[0];
                }
                return $arg;
            }, $right));
        } catch (\Error $e) {
            if (Mineflow::isDebug()) {
                Main::getInstance()->getLogger()->logException($e);
            }
            throw new MineflowMethodErrorException($target, $name, $e->getMessage());
        }

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
            $variable = $variable->getProperty($name1);

            if ($variable === null) {
                throw new UndefinedMineflowPropertyException($tmp, $name1);
            }
            $tmp .= ".".$name1;
        }

        return $variable;
    }

    public function copyOrCreateVariable(string $value, ?FlowItemExecutor $executor = null): Variable {
        if ($this->isSimpleVariableString($value)) {
            $variable = $executor?->getVariable(substr($value, 1, -1)) ?? $this->getNested(substr($value, 1, -1));
            if ($variable !== null) {
                return $variable;
            }
        }

        $value = $this->replaceVariables($value, $executor?->getVariables() ?? []);
        return Variable::create($this->currentType($value), $this->getType($value));
    }

    public function isSimpleVariableString(string $variable): bool {
        return (bool)preg_match("/^{[^{}\[\]]+}$/u", $variable);
    }

    public function isVariableString(string $variable): bool {
        return (bool)preg_match("/^{[^{}]+}$/u", $variable);
    }

    public function containsVariable(string $variable): bool {
        return (bool)preg_match("/{.+}/u", $variable);
    }

    public function getType(string $string): string {
        if (str_starts_with($string, "(str)")) {
            $type = StringVariable::getTypeName();
        } elseif (str_starts_with($string, "(num)")) {
            $type = NumberVariable::getTypeName();
        } elseif (is_numeric($string)) {
            $type = NumberVariable::getTypeName();
        } else {
            $type = StringVariable::getTypeName();
        }
        return $type;
    }

    public function currentType(string $value): string|float {
        if (str_starts_with($value, "(str)")) {
            $newValue = mb_substr($value, 5);
        } elseif (str_starts_with($value, "(num)")) {
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
            $result[$key] = match (true) {
                is_array($value) => array_is_list($value) ? new ListVariable($this->toVariableArray($value)) : new MapVariable($this->toVariableArray($value)),
                is_numeric($value) => new NumberVariable((float)$value),
                is_bool($value) => new BooleanVariable($value),
                is_null($value) => new NullVariable(),
                default => new StringVariable($value),
            };
        }
        return $result;
    }

    public function arrayToListVariable(array $data): ListVariable {
        $variableArray = $this->toVariableArray($data);

        if (array_is_list($variableArray)) return new ListVariable($variableArray);
        return new MapVariable($variableArray);
    }

    public function tagToVariable(Tag $tag): Variable {
        return match (true) {
            $tag instanceof StringTag => new StringVariable($tag->getValue()),
            $tag instanceof ByteTag => new BooleanVariable((bool)$tag->getValue()),
            $tag instanceof FloatTag, $tag instanceof IntTag, $tag instanceof DoubleTag => new NumberVariable($tag->getValue()),
            $tag instanceof ListTag => new ListVariable($this->listTagToVariableArray($tag)),
            $tag instanceof CompoundTag => new MapVariable($this->listTagToVariableArray($tag)),
            default => new NullVariable(),
        };
    }

    public function listTagToVariableArray(ListTag|CompoundTag $tag): array {
        $result = [];
        foreach ($tag as $key => $value) {
            $result[$key] = $this->tagToVariable($value);
        }
        return $result;
    }

    /**
     * @param array<string, Variable> $variables
     * @return array
     */
    public function variableArrayToArray(array $variables): array {
        $result = [];
        foreach ($variables as $name => $variable) {
            $value = $variable->getValue();
            if (is_array($value)) $value = $this->variableArrayToArray($value);

            $result[$name] = $value;
        }
        return $result;
    }

    public function initVariableProperties(): void {
        ListVariable::registerProperties();
        MapVariable::registerProperties();
        StringVariable::registerProperties();
        NumberVariable::registerProperties();
        AxisAlignedBBVariable::registerProperties();
        BlockVariable::registerProperties();
        ConfigVariable::registerProperties();
        EntityVariable::registerProperties();
        EventVariable::registerProperties();
        HumanVariable::registerProperties();
        InventoryVariable::registerProperties();
        ItemVariable::registerProperties();
        LivingVariable::registerProperties();
        LocationVariable::registerProperties();
        PlayerVariable::registerProperties();
        PositionVariable::registerProperties();
        RecipeVariable::registerProperties();
        ScoreboardVariable::registerProperties();
        ServerVariable::registerProperties();
        UnknownVariable::registerProperties();
        Vector3Variable::registerProperties();
        WorldVariable::registerProperties();
    }
}
