<?php

namespace aieuo\mineflow\variable;

class ObjectVariable extends Variable {

    public $type = Variable::OBJECT;

    /* @var string|null $showString */
    private $showString;

    /**
     * @param object $value
     * @param string|null $str
     */
    public function __construct(object $value, ?string $str = null) {
        parent::__construct($value);
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
     * @return array<string, DummyVariable>
     */
    public static function getValuesDummy(): array {
        return [];
    }
}