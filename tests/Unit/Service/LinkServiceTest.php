<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Tests\Unit\Service;

use OCA\LinkCreator\Service\LinkNotFound;
use OCP\Files\IRootFolder;
use PHPUnit\Framework\TestCase;

use OCP\AppFramework\Db\DoesNotExistException;

use OCA\LinkCreator\Db\Link;
use OCA\LinkCreator\Service\LinkService;
use OCA\LinkCreator\Db\LinkMapper;

class LinkServiceTest extends TestCase {
	private LinkService $service;
    private IRootFolder $storage;
	private string $userId = 'john';
	private $mapper;

	public function setUp(): void {
        $this->storage = $this->getMockBuilder(IRootFolder::class)->getMock();
		$this->mapper = $this->getMockBuilder(LinkMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->service = new LinkService($this->mapper, $this->storage);
	}
}
