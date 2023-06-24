<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\condition\config;

use aieuo\mineflow\flowItem\base\ConditionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\condition\Condition;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\argument\ConfigArgument;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use SOFe\AwaitGenerator\Await;

class ExistsConfigData extends FlowItem implements Condition {
    use ConditionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ConfigArgument $config;

    public function __construct(string $config = "", private string $key = "") {
        parent::__construct(self::EXISTS_CONFIG_DATA, FlowItemCategory::CONFIG);

        $this->config = new ConfigArgument("config", $config);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->config->getName(), "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->config->get(), $this->getKey()];
    }

    public function getConfig(): ConfigArgument {
        return $this->config;
    }

    public function setKey(string $key): void {
        $this->key = $key;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function isDataValid(): bool {
        return $this->config->isNotEmpty() and $this->getKey() !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $config = $this->config->getConfig($source);

        $key = $source->replaceVariables($this->getKey());

        yield Await::ALL;
        return $config->getNested($key) !== null;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->config->createFormElement($variables),
            new ExampleInput("@condition.existsConfig.form.key", "aieuo", $this->getKey(), true),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->config->set($content[0]);
        $this->setKey($content[1]);
    }

    public function serializeContents(): array {
        return [$this->config->get(), $this->getKey()];
    }
}
