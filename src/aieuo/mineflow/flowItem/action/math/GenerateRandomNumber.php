<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\math;

use aieuo\mineflow\flowItem\argument\NumberArgument;
use aieuo\mineflow\flowItem\argument\StringArgument;
use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\variable\NumberVariable;
use SOFe\AwaitGenerator\Await;

class GenerateRandomNumber extends TypeGetMathVariable {
    use ActionNameWithMineflowLanguage;

    private NumberArgument $min;
    private NumberArgument $max;

    protected StringArgument $resultName;

    public function __construct(string $min = "", string $max = "", string $resultName = "random") {
        parent::__construct(self::GENERATE_RANDOM_NUMBER, resultName: $resultName);

        $this->min = new NumberArgument("min", $min, example: "0");
        $this->max = new NumberArgument("max", $max, example: "10");
        $this->resultName = new StringArgument("result", $resultName, "@action.form.resultVariableName", example: "random");
    }

    public function getDetailDefaultReplaces(): array {
        return ["min", "max", "result"];
    }

    public function getDetailReplaces(): array {
        return [$this->min->get(), $this->max->get(), $this->resultName->get()];
    }

    public function getMin(): NumberArgument {
        return $this->min;
    }

    public function getMax(): NumberArgument {
        return $this->max;
    }

    public function isDataValid(): bool {
        return $this->min->isNotEmpty() and $this->max->isNotEmpty() and $this->resultName->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $min = $this->min->getInt($source);
        $max = $this->max->getInt($source);
        $resultName = $this->resultName->getString($source);

        $rand = mt_rand($min, $max);
        $source->addVariable($resultName, new NumberVariable($rand));

        yield Await::ALL;
        return $rand;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->min->createFormElement($variables),
            $this->max->createFormElement($variables),
            $this->resultName->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->min->set($content[0]);
        $this->max->set($content[1]);
        $this->resultName->set($content[2]);
    }

    public function serializeContents(): array {
        return [$this->min->get(), $this->max->get(), $this->resultName->get()];
    }
}
