<?php

namespace aieuo\mineflow\variable;

class ObjectVariable extends Variable {

    public $type = Variable::OBJECT;

    /* @var string|null $showString */
    private $showString;

    /**
     * @param object $value
     * @param string $name
     * @param string|null $str
     */
    public function __construct(object $value, string $name = "", ?string $str = null) {
        parent::__construct($value, $name);
        $this->showString = $str;
    }

    public function getValue(): object {
        return parent::getValue();
    }

    public function getShowString(): ?string {
        return $this->showString;
    }

    public function __toString(): string {
        if (!empty($this->showString)) return (string)$this->showString;
        if (method_exists($this->getValue(), "__toString")) {
            $str = (string)$this->getValue();
        } else {
            $str = (string)get_class($this->getValue());
        }
        return $str;
    }

    /**
     * @param string $name
     * @return DummyVariable[]
     */
    public static function getValuesDummy(string $name): array {
        return [];
    }
}