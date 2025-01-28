<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable\parser\exception;

use aieuo\mineflow\exception\MineflowException;
use aieuo\mineflow\variable\parser\token\VariableToken;
use pocketmine\utils\TextFormat;
use function count;

class VariableParseException extends MineflowException {

    /**
     * @param string $message
     * @param VariableToken[] $tokens
     * @param int $position
     */
    public function __construct(
        string                 $message,
        private readonly array $tokens,
        private readonly int   $position,
    ) {
        parent::__construct($message." ".$this->getHighlightedTokens(), 0, null);
    }

    public function getTokens(): array {
        return $this->tokens;
    }

    public function getPosition(): int {
        return $this->position;
    }

    public function getHighlightedTokens(): string {
        $text = TextFormat::YELLOW;
        foreach ($this->getTokens() as $i => $token) {
            $text .= $token->getTrimmedLeft();
            if ($i === $this->getPosition()) {
                $text .= (TextFormat::RED.$token->getToken().TextFormat::YELLOW);
            } else {
                $text .= $token->getToken();
            }
            $text .= $token->getTrimmedRight();
        }
        if ($this->getPosition() >= count($this->getTokens())) {
            $text .= TextFormat::RED."_".TextFormat::YELLOW;
        }
        return $text;
    }
}