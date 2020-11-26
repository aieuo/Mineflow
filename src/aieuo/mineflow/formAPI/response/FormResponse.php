<?php

namespace aieuo\mineflow\formAPI\response;

class FormResponse {
    /** @var array */
    private $errors = [];

    /** @var array */
    protected $response;
    /** @var int */
    protected $currentIndex = 0;

    public function __construct(array $data) {
        $this->response = $data;
    }

    public function setCurrentIndex(int $index): void {
        $this->currentIndex = $index;
    }

    public function getCurrentIndex(): int {
        return $this->currentIndex;
    }

    public function addError(string $error): void {
        $this->errors[] = [$error, $this->currentIndex];
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function hasError(): bool {
        return count($this->errors) > 0;
    }

}