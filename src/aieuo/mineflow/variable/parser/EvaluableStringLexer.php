<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser;

use aieuo\mineflow\variable\parser\token\VariableToken;
use function mb_str_split;
use function preg_replace;

class EvaluableStringLexer extends VariableLexer {

    public function lexer(string $source): array {
        $this->tokens = [];
        $this->token = "";
        $brackets = 0;
        $braces = 0;
        $escape = false;

        $source = preg_replace("/({.*)\[(\d+)](.*})/u", "$1.$2$3", $source);
        foreach (mb_str_split($source) as $char) {
            if ($escape) {
                $this->token .= $char;
                $escape = false;
                continue;
            }
            if ($char === VariableToken::ESCAPE) {
                $escape = true;
                continue;
            }

            if ($char === VariableToken::L_BRACE) {
                $braces++;
            }
            if ($braces === 0) {
                $this->token .= $char;
                continue;
            }

            switch ($char) {
                case VariableToken::PLUS:
                case VariableToken::MINUS:
                case VariableToken::ASTERISK:
                case VariableToken::SLASH:
                case VariableToken::L_PAREN:
                case VariableToken::R_PAREN:
                case VariableToken::L_BRACE:
                case VariableToken::R_BRACE:
                case VariableToken::DOT:
                    $this->push();
                    $this->tokens[] = new VariableToken($char, $char);

                    if ($char === VariableToken::L_PAREN) {
                        $brackets++;
                    } elseif ($char === VariableToken::R_PAREN) {
                        $brackets--;
                    }
                    break;
                case VariableToken::COMMA:
                    if ($brackets > 0) {
                        $this->push();
                        $this->tokens[] = new VariableToken($char, $char);
                    } else {
                        $this->token .= $char;
                    }
                    break;
                default:
                    $this->token .= $char;
                    break;
            }

            if ($char === VariableToken::R_BRACE) {
                $braces--;
            }
        }
        $this->push();

        return $this->tokens;
    }
}