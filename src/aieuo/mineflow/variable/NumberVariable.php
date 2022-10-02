<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\Tag;
use function is_int;

class NumberVariable extends Variable implements \JsonSerializable {

    public int $type = Variable::NUMBER;

    public static function zero(): self {
        return new NumberVariable(0);
    }

    public function __construct(int|float $value) {
        parent::__construct($value);
    }

    public function getValue(): int|float {
        return parent::getValue();
    }

    public function modulo(NumberVariable $var): NumberVariable {
        $result = $this->getValue() % $var->getValue();
        return new NumberVariable($result);
    }

    public function add($target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() + $target->getValue());
        if (is_numeric($target)) return new NumberVariable($this->getValue() + $target);

        throw new UnsupportedCalculationException();
    }

    public function sub($target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() - $target->getValue());
        if (is_numeric($target)) return new NumberVariable($this->getValue() - $target);

        throw new UnsupportedCalculationException();
    }

    public function mul($target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() * $target->getValue());
        if (is_numeric($target)) return new NumberVariable($this->getValue() * $target);

        throw new UnsupportedCalculationException();
    }

    public function div($target): NumberVariable {
        if ($target instanceof NumberVariable) return new NumberVariable($this->getValue() / $target->getValue());
        if (is_numeric($target)) return new NumberVariable($this->getValue() / $target);

        throw new UnsupportedCalculationException();
    }

    public function toStringVariable(): StringVariable {
        return new StringVariable((string)$this->getValue());
    }

    public function toNBTTag(): Tag {
        if (is_int($this->value)) {
            return new IntTag($this->value);
        }

        return new FloatTag((float)$this->value);
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->getType(),
            "value" => $this->getValue(),
        ];
    }
}
