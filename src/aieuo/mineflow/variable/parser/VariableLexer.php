<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser;

use aieuo\mineflow\variable\parser\token\VariableToken;
use function mb_str_split;
use function preg_replace;
use function trim;

class VariableLexer {

    private array $tokens = [];

    private string $token = "";

    public function __construct(private string $source) {
        $this->source = preg_replace("/\[(.*?)]/u", ".$1", $this->source);
    }

    public function lexer(): array {
        $this->tokens = [];
        $this->token = "";
        $brackets = 0;
        $escape = false;

        foreach (mb_str_split($this->source) as $char) {
            if ($escape) {
                $this->token .= $char;
                $escape = false;
                continue;
            }

            switch ($char) {
                case VariableToken::ESCAPE:
                    $escape = true;
                    break;
                case VariableToken::PLUS:
                case VariableToken::MINUS:
                case VariableToken::ASTERISK:
                case VariableToken::SLASH:
                case VariableToken::L_PAREN:
                case VariableToken::R_PAREN:
                case VariableToken::DOT:
                    $this->push();
                    $this->tokens[] = $char;

                    if ($char === VariableToken::L_PAREN) {
                        $brackets++;
                    } elseif ($char === VariableToken::R_PAREN) {
                        $brackets--;
                    }
                    break;
                case VariableToken::COMMA:
                    if ($brackets > 0) {
                        $this->push();
                        $this->tokens[] = $char;
                    } else {
                        $this->token .= $char;
                    }
                    break;
                default:
                    $this->token .= $char;
                    break;
            }
        }
        $this->push();

        return $this->tokens;
    }

    private function push(): void {
        $token = trim($this->token);
        if ($token !== "") {
            $this->tokens[] = $token;
        }
        $this->token = "";
    }

}
