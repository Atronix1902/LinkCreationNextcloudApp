<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Db;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

use OCP\AppFramework\Db\Entity;

/**
 * @method getId(): int
 * @method getFrom(): string
 * @method setFrom(string $from): void
 * @method getTo(): string
 * @method setTo(string $to): void
 * @method getUserId(): string
 * @method setUserId(string $userId): void
 * @method getCreatedAt(): string
 * @method setCreatedAt(string $createdAt): void
 * @method getFromAbsolute(): string
 * @method setFromAbsolute(string $from): void
 * @method getToAbsolute(): string
 * @method setToAbsolute(string $to): void
 * @method getResultString(): ?string
 * @method setResultString(?string $result): void
 * @method getResultCode(): int
 * @method setResultCode(int $code): void
 */
class Link extends Entity implements JsonSerializable {
	protected string $title = '';
	protected string $from = '';
	protected string $to = '';
    protected string $userId = '';
    protected string $createdAt = '';
    protected string $fromAbsolute = '';
    protected string $toAbsolute = '';
	protected ?string $resultString = null;
	protected int $resultCode = 0;


	#[ArrayShape(['id' => "int", 'from' => "string", 'to' => "string", 'createdAt' => "string", 'resultString' => 'string|null', 'resultCode' => 'int', 'fromAbsolute' => 'string', 'toAbsolute' => 'string'])]
    public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'from' => $this->from,
			'to' => $this->to,
            'fromAbsolute' => $this->fromAbsolute,
            'toAbsolute' => $this->toAbsolute,
            'createdAt' => $this->createdAt,
            'resultString' => $this->resultString ?? null,
			'resultCode' => $this->resultCode
		];
	}
}
