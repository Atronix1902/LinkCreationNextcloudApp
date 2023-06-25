<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Tests\Integration\Controller;

use OCA\LinkCreator\Controller\LinkController;
use OCP\AppFramework\App;
use OCP\AppFramework\Db\QBMapper;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

use OCA\LinkCreator\Db\Link;
use OCA\LinkCreator\Db\LinkMapper;

class LinkIntegrationTest extends TestCase {
	private LinkController $controller;
	private QBMapper $mapper;
	private string $userId = 'john';

	public function setUp(): void {
		$app = new App('linkcreator');
		$container = $app->getContainer();

		// only replace the user id
		$container->registerService('userId', function () {
			return $this->userId;
		});

		// we do not care about the request but the controller needs it
		$container->registerService(IRequest::class, function () {
			return $this->createMock(IRequest::class);
		});

		$this->controller = $container->get(LinkController::class);
		$this->mapper = $container->get(LinkMapper::class);
	}

	public function testUpdate(): void {
		// create a new note that should be updated
		$note = new Link();
		$note->setTitle('old_title');
		$note->setContent('old_content');
		$note->setUserId($this->userId);

		$id = $this->mapper->insert($note)->getId();

		// fromRow does not set the fields as updated
		$updatedNote = Link::fromRow([
			'id' => $id,
			'user_id' => $this->userId
		]);
		$updatedNote->setContent('content');
		$updatedNote->setTitle('title');

		$result = $this->controller->update($id, 'title', 'content');

		$this->assertEquals($updatedNote, $result->getData());

		// clean up
		$this->mapper->delete($result->getData());
	}
}
