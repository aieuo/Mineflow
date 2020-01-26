<?php

namespace aieuo\mineflow\variable;

use pocketmine\utils\Config;
use aieuo\mineflow\Main;
use pocketmine\Player;
use pocketmine\utils\UUID;

class VariableHelper {

    /** @var Variable[] */
    private $variables = [];

    /** @var Config */
    private $file;
    /* @var Main */
    private $owner;

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

        $data = $this->file->get($name);
        return Variable::create($data["value"], $data["name"], $data["type"]);
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

        if (!$variable->isSavable() or empty($variable->getName())) return;
        $this->file->set($variable->getName(), $variable);
        $this->file->save();
    }

    /**
     * @param String $name
     * @return void
     */
    public function delete(String $name) {
        unset($this->variables[$name]);

        $this->file->remove($name);
    }

    public function saveAll() {
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
     * @param string $string
     * @param array $variables
     * @param bool $global
     * @return string
     */
    public function replaceVariables(string $string, array $variables = [], bool $global = true) {
        $limit = 10;
        while (preg_match_all("/({(?:[^{}]+|(?R))*})/", $string, $matches)) {
            foreach ($matches[0] as $name) {
                $name = substr($name, 1, -1);
                if (strpos($name, "{") !== false and strpos($name, "}") !== false) {
                    $name = $this->replaceVariables($name, $variables, $global);
                }
                $string = $this->replace($string, $name, $variables, $global);
            }
            if (--$limit < 0) break;
        }
        return $string;
    }

    /**
     * 変数を置き換える
     * @param string $string
     * @param string $replace
     * @param array $variables
     * @param bool $global
     * @return string
     */
    public function replace(string $string, string $replace, array $variables = [], bool $global = true) {
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
            if (!($variable instanceof Variable) or $variable instanceof StringVariable or $variable instanceof NumberVariable) {
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
    public function isVariableString(string $variable) {
        return preg_match("/^{[^{}\[\].]+}$/", $variable);
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
}