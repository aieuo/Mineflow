<?php

declare(strict_types=1);


namespace aieuo\mineflow\flowItem\form\page\custom;

use function array_pop;
use function array_shift;
use function array_unshift;
use function array_values;

class CustomFormResponseProcessor {

    /** @var array<callable(array): array> */
    private array $processors = [];

    public function clear(): void {
        $this->processors = [];
    }

    /**
     * @param callable(array): array $processor
     * @return CustomFormResponseProcessor
     */
    public function preprocess(callable $processor): self {
        $this->processors[] = $processor;
        return $this;
    }

    /**
     * @param int $index
     * @param callable(mixed): mixed $processor
     * @return CustomFormResponseProcessor
     */
    public function preprocessAt(int $index, callable $processor): self {
        return $this->preprocess(function (array $data) use($index, $processor) {
            $data[$index] = $processor($data[$index]);
            return $data;
        });
    }

    public function shift(): self {
        return $this->preprocess(function (array $data) {
            array_shift($data);
            return $data;
        });
    }

    public function pop(): self {
        return $this->preprocess(function (array $data) {
            array_pop($data);
            return $data;
        });
    }

    public function unshift(mixed $value): self {
        return $this->preprocess(function (array $data) use($value) {
            array_unshift($data, $value);
            return $data;
        });
    }

    public function push(mixed $value): self {
        return $this->preprocess(function (array $data) use($value) {
            $data[] = $value;
            return $data;
        });
    }

    public function discard(int $index): self {
        return $this->preprocess(function (array $data) use ($index) {
            unset($data[$index]);
            return array_values($data);
        });
    }

    /**
     * @param int[] $indexes
     * @return $this
     */
    public function rearrange(array $indexes): self {
        return $this->preprocess(function (array $data) use ($indexes) {
            $arranged = [];
            foreach ($indexes as $index) {
                $arranged[] = $data[$index];
            }
            return $arranged;
        });
    }

    public function logicalNOT(int $index): self {
        return $this->preprocess(function (array $data) use ($index) {
            $data[$index] = !$data[$index];
            return $data;
        });
    }

    /**
     * @param callable(array $data): void $validator
     * @return CustomFormResponseProcessor
     */
    public function validate(callable $validator): self {
        $this->preprocess(function (array $data) use($validator) {
            $validator($data);
            return $data;
        });
        return $this;
    }

    public function build(): callable {
        $processors = $this->processors;

        return function (array $data) use ($processors) {
            foreach ($processors as $processor) {
                $data = $processor($data);
            }
            return $data;
        };
    }
}
