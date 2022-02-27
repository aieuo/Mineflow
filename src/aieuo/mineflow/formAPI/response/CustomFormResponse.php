<?php

namespace aieuo\mineflow\formAPI\response;

use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Element;

class CustomFormResponse extends FormResponse {
    private array $responseOverrides = [];
    private array $defaultOverrides = [];
    private array $elementOverrides = [];
    private bool $ignoreResponse = false;
    private bool $resend = false;
    /* @var callable|null */
    private $interruptCallback;
    private CustomForm $form;

    public function __construct(CustomForm $form, array $data) {
        $this->form = $form;
        parent::__construct($data);
    }

    public function getCustomForm(): CustomForm {
        return $this->form;
    }

    public function getInputResponse(): string {
        return $this->response[$this->currentIndex];
    }

    public function getDropdownResponse(): int {
        return $this->response[$this->currentIndex];
    }

    public function getToggleResponse(): bool {
        return $this->response[$this->currentIndex];
    }

    public function getSliderResponse(): float {
        return $this->response[$this->currentIndex];
    }

    public function overrideResponse($response): void {
        $this->responseOverrides[$this->currentIndex] = $response;
    }

    public function getResponseOverrides(): array {
        return $this->responseOverrides;
    }

    public function getDefaultOverrides(): array {
        return $this->defaultOverrides;
    }

    public function overrideElement(Element $element, $default = null): void {
        $this->elementOverrides[$this->currentIndex] = $element;
        if ($default !== null) $this->defaultOverrides[$this->currentIndex] = $default;
    }

    public function getElementOverrides(): array {
        return $this->elementOverrides;
    }

    public function ignoreResponse(): void {
        $this->ignoreResponse = true;
    }

    public function isResponseIgnored(): bool {
        return $this->ignoreResponse;
    }

    public function setResend(bool $resend): void {
        $this->resend = $resend;
    }

    public function shouldResendForm(): bool {
        return $this->resend;
    }

    public function setInterruptCallback(?callable $callback): void {
        $this->interruptCallback = $callback;
    }

    public function getInterruptCallback(): ?callable {
        return $this->interruptCallback;
    }
}