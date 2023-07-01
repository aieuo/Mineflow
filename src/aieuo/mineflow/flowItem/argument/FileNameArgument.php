<?php
declare(strict_types=1);


namespace aieuo\mineflow\flowItem\argument;

use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
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

    public function buildEditPage(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->element($this->createFormElement($variables), function (string $data) {
            if (!Utils::isValidFileName($data)) {
                throw new \UnexpectedValueException("@form.recipe.invalidName", 0);
            }
        });
    }
}
