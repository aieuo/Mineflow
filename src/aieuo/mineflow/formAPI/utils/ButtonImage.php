<?php

namespace aieuo\mineflow\formAPI\utils;

class ButtonImage implements \JsonSerializable {

	public const TYPE_PATH = "path";
	public const TYPE_URL = "url";

	private string $type;
	private string $data;

	public function __construct(string $image, string $type = self::TYPE_PATH) {
		$this->data = $image;
		$this->type = $type;
	}

	public function getType(): string {
		return $this->type;
	}

	public function setType(string $type): void {
		$this->type = $type;
	}

	public function getData(): string {
		return $this->data;
	}

	public function setData(string $data): void {
		$this->data = $data;
	}

	public function jsonSerialize(): array {
	    return [
	        "type" => $this->getType(),
            "data" => $this->getData(),
        ];
    }
}