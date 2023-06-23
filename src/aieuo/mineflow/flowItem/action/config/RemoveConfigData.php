<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\config;

use aieuo\mineflow\flowItem\base\ActionNameWithMineflowLanguage;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemCategory;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\flowItem\FlowItemPermission;
use aieuo\mineflow\flowItem\form\HasSimpleEditForm;
use aieuo\mineflow\flowItem\form\SimpleEditFormBuilder;
use aieuo\mineflow\flowItem\placeholder\ConfigPlaceholder;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use SOFe\AwaitGenerator\Await;

class RemoveConfigData extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ConfigPlaceholder $config;

    public function __construct(string $config = "", private string $key = "") {
        parent::__construct(self::REMOVE_CONFIG_VALUE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->config = new ConfigPlaceholder("config", $config);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->config->getName(), "key"];
    }

    public function getDetailReplaces(): array {
        return [$this->config->get(), $this->getKey()];
    }

    public function getConfig(): ConfigPlaceholder {
        return $this->config;
    }

    public function setKey(string $health): void {
        $this->key = $health;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function isDataValid(): bool {
        return $this->config->isNotEmpty() and $this->key !== "";
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $key = $source->replaceVariables($this->getKey());

        $config = $this->config->getConfig($source);
        $config->removeNested($key);

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->config->createFormElement($variables),
            new ExampleInput("@action.setConfig.form.key", "aieuo", $this->getKey(), true),
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
