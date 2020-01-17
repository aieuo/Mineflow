<?php

namespace aieuo\mineflow\variable;

class ObjectVariable extends Variable {

    public $type = Variable::NUMBER;

    /**
     * @param object $value
     * @param string $name
     */
    public function __construct($value, string $name = "") {
        parent::__construct($value, $name);
    }

    /**
     * @return object
     */
    public function getValue() {
        return parent::getValue();
    }

    public function getValueFromIndex(string $index): ?Variable {
        return null;
    }
    
    public function isSavable(): bool {
        return false;
    }

    public function toStringVariable(): StringVariable {
        return new StringVariable($this->__toString(), $this->getName());
    }

    public function __toString() {
        if (method_exists($this->getValue(), "__toString")) {
            $str = $this->getValue()->__toString();
        } else {
            $str = get_class($this->getValue());
        }
        return $str;
    }

    public function jsonSerialize() {
        return [
            $this->getName(),
            $this->getType(),
            $this->getValue(),
        ];
    }
}