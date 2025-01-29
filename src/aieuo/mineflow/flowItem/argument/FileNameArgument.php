<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\exception\InvalidFormValueException;
use aieuo\mineflow\utils\Utils;

class FileNameArgument extends StringArgument {

    public function __construct(
        string $name,
        string $value = "",
        string $description = "@action.createConfig.form.name",
        string $example = "config",
        bool   $optional = false,
    ) {
        parent::__construct($name, $value, $description, $example, $optional);
    }

    /**
     * @param array{0: string} $data
     * @return void
     */
    public function handleFormResponse(mixed ...$data): void {
        if (!Utils::isValidFileName($data[0])) {
            throw new InvalidFormValueException("@form.recipe.invalidName", 0);
        }

        $this->value($data[0]);
    }
}