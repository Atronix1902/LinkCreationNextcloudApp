<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Tests\Unit\Controller;

use OCA\LinkCreator\Controller\LinkApiController;

class LinkApiControllerTest extends LinkControllerTest {
	public function setUp(): void {
		parent::setUp();
		$this->controller = new LinkApiController($this->request, $this->service, $this->userId);
	}
}
