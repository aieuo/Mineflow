<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\token;

class VariableTokenType {
    public const PLUS = "+";
    public const MINUS = "-";
    public const ASTERISK = "*";
    public const SLASH = "/";
    public const L_PAREN = "(";
    public const R_PAREN = ")";
    public const DOT = ".";
    public const COMMA = ",";

    public const STRING = "string";
    public const NUMBER = "number";
}
