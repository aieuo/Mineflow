<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;

class StringVariable extends Variable implements \JsonSerializable {

    public int $type = Variable::STRING;

    public function getValue(): string {
        return (string)parent::getValue();
    }

    public function add($target): StringVariable {
        return new StringVariable($this->getValue().$target);
    }

    public function sub($target): StringVariable {
        return new StringVariable(str_replace((string)$target, "", $this->getValue()));
    }

    public function mul($target): StringVariable {
        if ($target instanceof NumberVariable) $target = $target->getValue();
        if(is_numeric($target)) return new StringVariable(str_repeat($this->getValue(), (int)$target));

        throw new UnsupportedCalculationException();
    }

    public function callMethod(string $name, array $parameters = []): ?Variable {
        return match ($name) {
            "length" => new NumberVariable(mb_strlen($this->getValue())),
            "toLowerCase", "lowercase" => new StringVariable(mb_strtolower($this->getValue())),
            "toUpperCase", "uppercase" => new StringVariable(mb_strtoupper($this->getValue())),
            "substring" => new StringVariable(mb_substr($this->getValue(), $parameters[0], $parameters[1] ?? null)),
            default => null,
        };
    }

    public function toNBTTag(): Tag {
        return new StringTag((string)$this->value);
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->getType(),
            "value" => $this->getValue(),
        ];
    }
}
