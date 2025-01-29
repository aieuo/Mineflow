<?php

namespace aieuo\mineflow\exception;

use Throwable;

class UnsupportedCalculationException extends MineflowException {
    public function __construct(string $message = null, int $code = 0, Throwable $previous = null) {
        parent::__construct($message ?? "§cUnsupported calculation§r", $code, $previous);
    }
}