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
use aieuo\mineflow\flowItem\argument\ConfigArgument;
use SOFe\AwaitGenerator\Await;

class SaveConfigFile extends FlowItem {
    use ActionNameWithMineflowLanguage;
    use HasSimpleEditForm;

    private ConfigArgument $config;

    public function __construct(string $config = "") {
        parent::__construct(self::SAVE_CONFIG_FILE, FlowItemCategory::CONFIG);
        $this->setPermissions([FlowItemPermission::CONFIG]);

        $this->config = new ConfigArgument("config", $config);
    }

    public function getDetailDefaultReplaces(): array {
        return [$this->config->getName()];
    }

    public function getDetailReplaces(): array {
        return [$this->config->get()];
    }

    public function getConfig(): ConfigArgument {
        return $this->config;
    }

    public function isDataValid(): bool {
        return $this->config->isNotEmpty();
    }

    protected function onExecute(FlowItemExecutor $source): \Generator {
        $config = $this->config->getConfig($source);
        $config->save();

        yield Await::ALL;
    }

    public function buildEditForm(SimpleEditFormBuilder $builder, array $variables): void {
        $builder->elements([
            $this->config->createFormElement($variables),
        ]);
    }

    public function loadSaveData(array $content): void {
        $this->config->set($content[0]);
    }

    public function serializeContents(): array {
        return [$this->config->get()];
    }
}
