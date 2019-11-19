<?php

namespace aieuo\mineflow\variable;

use pocketmine\utils\Config;
use aieuo\mineflow\Main;
use pocketmine\Player;

class VariableHelper {

    /** @var Variable[] */
    private $variables = [];

    /** @var Config */
    private $file;

    public function __construct(Main $owner, Config $file) {
        $this->owner = $owner;
        $this->file = $file;
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

        $datas = $this->file->get($name);
        switch ($datas["type"]) {
            case Variable::STRING:
                return new StringVariable($datas["name"], $datas["value"]);
            case Variable::NUMBER:
                return new NumberVariable($datas["name"], $datas["value"]);
            case Variable::LIST:
                return new ListVariable($datas["name"], $datas["value"]);
            case Variable::MAP:
                return new MapVariable($datas["name"], $datas["value"]);
            default:
                return null;
        }
    }

    /**
     * @param Variable $variable
     * @param bool $save
     */
    public function add(Variable $variable, bool $save = false) {
        if (!$save) {
            $this->variables[$variable->getName()] = $variable;
            return;
        }

        $datas = [
            "name" => $variable->getName(),
            "type" => $variable->getType(),
            "value" => $variable->getValue(),
        ];
        $this->file->set($variable->getName(), $datas);
        $this->file->save();
    }

    /**
     * @param  String $name
     * @return bool
     */
    public function delete(String $name) {
        unset($this->variables[$name]);

        $this->file->remove($name);
    }

    public function findVariables(string $string): array {
        $variables = [];
        if (preg_match_all("/({(?:[^{}]+|(?R))*})/", $string, $matches)) {
            foreach ($matches[0] as $name) {
                $name = substr($name, 1, -1);
                if (strpos($name, "{") !== false and strpos($name, "}") !== false) {
                    $add = $this->findVariables($name);
                    $variables = array_merge($variables, $add);
                    continue;
                }
                $variables[] = $name;
            }
        }
        return $variables;
    }

    /**
     * 文字列の中にある変数を置き換える
     * @param  string $string
     * @param  array $variables
     * @return string
     */
    public function replaceVariables(string $string, array $variables = [], bool $global = true) {
        $limit = 10;
        while (preg_match_all("/(\{(?:[^{}]+|(?R))*\})/", $string, $matches)) {
            foreach ($matches[0] as $name) {
                $name = substr($name, 1, -1);
                if (strpos($name, "{") !== false and strpos($name, "}") !== false) {
                    $name = $this->replaceVariables($name, $variables, $global);
                    continue;
                }
                $string = $this->replace($string, $name, $variables, $global);
            }
            if (--$limit < 0) break;
        }
        return $string;
    }

    /**
     * 変数を置き換える
     * @param  string $string
     * @param  string $replace
     * @param  array $variables
     * @return string
     */
    public function replace(string $string, string $replace, array $variables = [], bool $global = true) {
        if (strpos($string, "{".$replace."}") === false) return $string;

        $names = explode(".", preg_replace("/\[([^\[\]]+)\]/", '.${1}', $replace));
        $name = array_shift($names);

        $variable = $variables[$name] ?? ($global ? $this->get($name) : null);
        if (!($variable instanceof Variable)) {
            return str_replace("{".$replace."}", "§cUndefined variable: ".$name."§r", $string);
        }
        $value = $variable->getValue();

        foreach ($names as $name) {
            if (!is_array($value) and !($value instanceof ListVariable or $value instanceof MapVariable)) {
                if ($value instanceof Variable) $value = $value->toStringVariable()->getValue();
                if (is_array($value)) {
                    if (array_values($value) === $value) $value = (new ListVariable($name, $value))->__toString();
                    else $value = (new MapVariable($name, $value))->__toString();
                }
                return str_replace("{".$replace."}", "§cUndefined index: ".$value.".".$name."§r", $string);
            }

            $variable = $value;
            $value = $variable instanceof Variable ? $variable->getValueFromIndex($name) : ($variable[$name] ?? null);
            if ($value === null) {
                if ($variable instanceof Variable) $variable = $variable->toStringVariable()->getValue();
                if (is_array($variable)) {
                    if (array_values($variable) === $variable) $variable = (new ListVariable($name, $variable))->__toString();
                    else $variable = (new MapVariable($name, $variable))->__toString();
                }
                return str_replace("{".$replace."}", "§cUndefined index: ".$variable.".".$name."§r", $string);
            }
        }
        if ($value instanceof Variable) $value = $value->toStringVariable()->getValue();
        if (is_array($value)) {
            if (array_values($value) === $value) $value = (new ListVariable($name, $value))->__toString();
            else $value = (new MapVariable($name, $value))->__toString();
        }
        return str_replace("{".$replace."}", $value, $string);
    }

    /**
     * 文字列が変数か調べる
     * @param  string  $variable
     * @return boolean
     */
    public function isVariable(string $variable) {
        return preg_match("/^{.+}$/", $variable);
    }

    /**
     * 文字列に変数が含まれているか調べる
     * @param  string  $variable
     * @return boolean
     */
    public function containsVariable(string $variable) {
        return preg_match("/.*{.+}.*/", $variable);
    }

    /**
     * 文字列の型を調べる
     * @param  string $string
     * @return int
     */
    public function getType(string $string) {
        if (substr($string, 0, 5) === "(str)") {
            $type = Variable::STRING;
        } elseif (substr($string, 0, 5) === "(num)") {
            $type = Variable::NUMBER;
        } elseif (substr($string, 0, 6) === "(list)") {
            $type = Variable::LIST;
        } elseif (is_numeric($string)) {
            $type = Variable::NUMBER;
        } else {
            $type = Variable::STRING;
        }
        return $type;
    }

    /**
     * 文字列の型を変更する
     * @param  string $string
     * @return string|float
     */
    public function currentType(string $value) {
        if (mb_substr($value, 0, 5) === "(str)") {
            $value = mb_substr($value, 5);
        } elseif (mb_substr($value, 0, 5) === "(num)") {
            $value = mb_substr($value, 5);
            if (!$this->containsVariable($value)) $value = (float)$value;
        } elseif (substr($value, 0, 6) === "(list)") {
            $value = mb_substr($value, 6);
            if (!$this->containsVariable($value)) $value = Variable::create("list", $value, Variable::LIST)->getValue();
        } elseif (is_numeric($value)) {
            $value = (float)$value;
        }
        return $value;
    }
}