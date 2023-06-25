<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Tests\Unit\Controller;

use OCA\LinkCreator\Controller\LinkController;
use PHPUnit\Framework\TestCase;

use OCP\IRequest;

use OCA\LinkCreator\Service\LinkService;

class LinkControllerTest extends TestCase {
	protected LinkController $controller;
	protected string $userId = 'john';
	protected $service;
	protected $request;

	public function setUp(): void {
		$this->request = $this->getMockBuilder(IRequest::class)->getMock();
		$this->service = $this->getMockBuilder(LinkService::class)
			->disableOriginalConstructor()
			->getMock();
		$this->controller = new LinkController($this->request, $this->service, $this->userId);
	}
}
