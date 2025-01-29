<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\token;

class VariableToken {

    public const PLUS = "+";
    public const MINUS = "-";
    public const ASTERISK = "*";
    public const SLASH = "/";
    public const L_PAREN = "(";
    public const R_PAREN = ")";
    public const L_BRACE = "{";
    public const R_BRACE = "}";
    public const DOT = ".";
    public const COMMA = ",";
    public const ESCAPE = "\\";
    public const STRING = "string";

    public function __construct(
        private readonly string $token,
        private readonly string $type,
        private readonly string $trimmedLeft = "",
        private readonly string $trimmedRight = "",
    ) {
    }

    public function getToken(): string {
        return $this->token;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getTrimmedLeft(): string {
        return $this->trimmedLeft;
    }

    public function getTrimmedRight(): string {
        return $this->trimmedRight;
    }

    public function __toString(): string {
        return $this->getTrimmedLeft().$this->getToken().$this->getTrimmedRight();
    }
}