<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'linkcreator';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
