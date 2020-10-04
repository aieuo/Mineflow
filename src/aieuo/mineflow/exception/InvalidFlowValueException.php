<?php
declare(strict_types=1);

namespace aieuo\mineflow\exception;

use aieuo\mineflow\utils\Language;
use Throwable;

class InvalidFlowValueException extends \RuntimeException {
    /** @var string */
    private $name;

    public function __construct(string $name = "", string $message = "", int $code = 0, Throwable $previous = null) {
        parent::__construct(Language::get("flowItem.error", [$name, $message]), $code, $previous);
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }
}