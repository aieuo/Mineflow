<?php
declare(strict_types=1);


namespace aieuo\mineflow\variable;

interface IteratorVariable extends \IteratorAggregate {

    public function pluck(string $index): ?Variable;

    public function first(): ?Variable;

    public function last(): ?Variable;

    public function firstKey(): ?Variable;

    public function lastKey(): ?Variable;

    public function keys(): ListVariable;

    public function values(): ListVariable;

    public function random(): ?Variable;

    public function shuffle(): IteratorVariable;

    public function take(int $amount): IteratorVariable;

    public function takeLast(int $amount): IteratorVariable;

    public function count(): NumberVariable;

    public function hasKey(int|string $key): bool;

    public function setValueAt(int|string $key, Variable $value): void;

    public function removeValue(Variable $value, bool $strict = true): void;

    public function removeValueAt(int|string $index): void;

    public function indexOf(Variable $value, bool $strict = true): int|string|null;

    public function toArray(): array;
}