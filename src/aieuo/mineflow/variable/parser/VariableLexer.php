<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser;

use aieuo\mineflow\variable\parser\token\VariableToken;
use function ltrim;
use function mb_str_split;
use function preg_replace;
use function rtrim;
use function str_replace;

class VariableLexer {

    /** @var VariableToken[] */
    protected array $tokens = [];

    protected string $token = "";

    public function lexer(string $source): array {
        $this->tokens = [];
        $this->token = "";
        $brackets = 0;
        $escape = false;

        $source = preg_replace("/\[(.*?)]/u", ".$1", $source);
        foreach (mb_str_split($source) as $char) {
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
        }
        $this->push();

        return $this->tokens;
    }

    protected function push(): void {
        $token1 = ltrim($this->token);
        $token = rtrim($token1);
        if ($token !== "") {
            $this->tokens[] = new VariableToken($token, VariableToken::STRING, str_replace($token1, "", $this->token), str_replace($token, "", $token1));
        }
        $this->token = "";
    }

}