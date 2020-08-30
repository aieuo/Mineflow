<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\flowItem\action\Action;
use aieuo\mineflow\flowItem\action\ActionFactory;
use aieuo\mineflow\recipe\Recipe;
use pocketmine\utils\Config;

class VariableHelper {

    /** @var Variable[] */
    private $variables = [];

    /** @var Config */
    private $file;

    public function __construct(Config $file) {
        $this->file = $file;
        $this->file->setJsonOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
    }

    /**
     * @param  string $name
     * @param  bool $save
     * @return bool
     */
    public function exists(String $name, bool $save = false): bool {
        if (isset($this->variables[$name]) and !$save) return true;

        return $this->file->exists($name);
    }

    /**
     * @param  string $name
     * @param  bool $save
     * @return null|Variable
     */
    public function get(String $name, bool $save = false): ?Variable {
        if (isset($this->variables[$name]) and !$save) return $this->variables[$name];
        if (!$this->exists($name)) return null;

        $data = $this->file->get($name);
        return $data instanceof Variable ? $data : Variable::create($data["value"], $data["name"], $data["type"]);
    }

    /**
     * @param string $name
     * @param bool $save
     * @return Variable|null
     */
    public function getNested(string $name, bool $save = false): ?Variable {
        $names = explode(".", $name);
        $name = array_shift($names);
        if (!$this->exists($name, $save)) return null;

        $variable = $this->get($name, $save);
        foreach ($names as $name) {
            if (!($variable instanceof ListVariable) and !($variable instanceof ObjectVariable)) return null;
            $variable = $variable->getValueFromIndex($name);
        }
        return $variable;
    }

    /**
     * @param Variable $variable
     * @param bool $save
     */
    public function add(Variable $variable, bool $save = false): void {
        if (!$save) {
            $this->variables[$variable->getName()] = $variable;
            return;
        }

        if (!$variable->isSavable() or empty($variable->getName())) return;
        $this->file->set($variable->getName(), $variable);
        $this->file->save();
    }

    /**
     * @param String $name
     * @return void
     */
    public function delete(String $name): void {
        unset($this->variables[$name]);

        $this->file->remove($name);
    }

    public function saveAll(): void {
        foreach ($this->variables as $variable) {
            $this->add($variable, true);
        }
        $this->variables = [];
    }

    public function findVariables(string $string): array {
        $variables = [];
        if (preg_match_all("/({(?:[^{}]+|(?R))*})/", $string, $matches)) {
            foreach ($matches[0] as $name) {
                $name = substr($name, 1, -1);
                $variables[] = $name;
            }
        }
        return $variables;
    }

    /**
     * 文字列の中にある変数を置き換える
     * @param string $string
     * @param Variable[] $variables
     * @param bool $global
     * @return string
     */
    public function replaceVariables(string $string, array $variables = [], bool $global = true): string {
        $limit = 10;
        while (preg_match_all("/({(?:[^{}]+|(?R))*})/", $string, $matches)) {
            foreach ($matches[0] as $name) {
                $name = substr($name, 1, -1);
                if (strpos($name, "{") !== false and strpos($name, "}") !== false) {
                    $replaced = $this->replaceVariables($name, $variables, $global);
                    $string = str_replace($name, $replaced, $string);
                    $name = $replaced;
                }
                $string = $this->replaceVariable($string, $name, $variables, $global);
            }
            if (--$limit < 0) break;
        }
        return $string;
    }

    public function replaceVariablesAndFunctions(string $string, Recipe $origin, bool $global = true): string {
        $limit = 10;
        while (preg_match_all("/({(?:[^{}]+|(?R))*})/", $string, $matches)) {
            foreach ($matches[0] as $name) {
                $name = substr($name, 1, -1);
                if (strpos($name, "{") !== false and strpos($name, "}") !== false) {
                    $replaced = $this->replaceVariablesAndFunctions($name, $origin, $global);
                    $string = str_replace($name, $replaced, $string);
                    $name = $replaced;
                }
                if (strpos($name, "(") !== false and strpos($name, ")") !== false) {
                    $string = $this->replaceFunction($string, $name, $origin);
                } else {
                    $string = $this->replaceVariable($string, $name, $origin->getVariables(), $global);
                }
            }
            if (--$limit < 0) break;
        }
        return $string;
    }

    public function replaceFunction(string $string, string $replace, Recipe $origin): string {
        if (strpos($string, "{".$replace."}") === false) return $string;

        if (preg_match("/^([a-zA-Z0-9]+)\((.*)\)$/", $replace, $matches)) {
            $name = $matches[1];
            $parameters = $matches[2];

            $action = ActionFactory::get($name);
            if ($action === null) {
                return str_replace("{".$replace."}", "§cUnknown action id", $string);
            }
            if (!$action->allowDirectCall()) {
                return str_replace("{".$replace."}", "§cCannot call direct $name", $string);
            }

            $class = get_class($action);
            /** @var Action $newAction */
            $newAction = new $class(...array_filter(array_map("trim", explode(",", $parameters)), function ($t) { return $t !== ""; }));
            /** @noinspection PhpStatementHasEmptyBodyInspection */
            foreach ($newAction->parent($origin)->execute($origin) as $_) {
            }
            $result = $newAction->getReturnValue();
            $string = str_replace("{".$replace."}", $result, $string);
        }
        return $string;
    }

    /**
     * 変数を置き換える
     * @param string $string
     * @param string $replace
     * @param Variable[] $variables
     * @param bool $global
     * @return string
     */
    public function replaceVariable(string $string, string $replace, array $variables = [], bool $global = true): string {
        if (strpos($string, "{".$replace."}") === false) return $string;

        $names = explode(".", preg_replace("/\[([^\[\]]+)]/", '.${1}', $replace));
        $name = array_shift($names);

        $variable = $variables[$name] ?? ($global ? $this->get($name) : null);
        if (!($variable instanceof Variable)) {
            return str_replace("{".$replace."}", "§cUndefined variable: ".$name."§r", $string);
        }
        $value = $variable->getValue();

        if (empty($names)) {
            $value = $variable->toStringVariable()->getValue();
            return str_replace("{".$replace."}", $value, $string);
        }

        $tmp = $name;
        foreach ($names as $name) {
            if (!($variable instanceof ListVariable) and !($variable instanceof ObjectVariable)) {
                return str_replace("{".$replace."}", "§cUndefined index: ".$tmp.".§l".$name."§r", $string);
            }

            $value = $variable->getValueFromIndex($name);
            if ($value === null) {
                return str_replace("{".$replace."}", "§cUndefined index: ".$tmp.".§l".$name."§r", $string);
            }

            $tmp .= ".".$name;
            $variable = $value;
        }
        if ($value instanceof Variable) $value = $value->toStringVariable()->getValue();
        return str_replace("{".$replace."}", $value, $string);
    }

    /**
     * 文字列が変数か調べる
     * @param  string  $variable
     * @return boolean
     */
    public function isVariableString(string $variable): bool {
        return preg_match("/^{[^{}\[\].]+}$/", $variable);
    }

    /**
     * 文字列に変数が含まれているか調べる
     * @param  string  $variable
     * @return boolean
     */
    public function containsVariable(string $variable): bool {
        return preg_match("/.*{.+}.*/", $variable);
    }

    /**
     * 文字列の型を調べる
     * @param  string $string
     * @return int
     */
    public function getType(string $string): int {
        if (substr($string, 0, 5) === "(str)") {
            $type = Variable::STRING;
        } elseif (substr($string, 0, 5) === "(num)") {
            $type = Variable::NUMBER;
        } elseif (is_numeric($string)) {
            $type = Variable::NUMBER;
        } else {
            $type = Variable::STRING;
        }
        return $type;
    }

    /**
     * 文字列の型を変更する
     * @param string $value
     * @return string|float
     */
    public function currentType(string $value) {
        if (mb_substr($value, 0, 5) === "(str)") {
            $value = mb_substr($value, 5);
        } elseif (mb_substr($value, 0, 5) === "(num)") {
            $value = mb_substr($value, 5);
            if (!$this->containsVariable($value)) $value = (float)$value;
        } elseif (is_numeric($value)) {
            $value = (float)$value;
        }
        return $value;
    }

    public function toVariableArray(array $data): array {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (array_values($value) === $value) {
                    $result[$key] = new ListVariable($this->toVariableArray($value));
                } else {
                    $result[$key] = new MapVariable($this->toVariableArray($value), $key);
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