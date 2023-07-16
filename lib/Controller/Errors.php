<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Controller;

use Closure;

use OCA\LinkCreator\Service\LinkNotCreatable;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\LinkCreator\Service\LinkNotFound;

trait Errors {
	protected function handleNotFound(Closure $callback): DataResponse {
		try {
			return new DataResponse($callback());
		} catch (LinkNotFound $e) {
			$message = ['message' => $e->getMessage()];
			return new DataResponse($message, Http::STATUS_NOT_FOUND);
		}
	}

	protected function handleNotCreatable(Closure $callback): DataResponse
	{
		try {
			return new DataResponse($callback());
		} catch (LinkNotCreatable $e) {
			$message = [
				'message'	=> $e->getMessage(),
				'code'		=> $e->getCode(),
				'detail'	=> $e->getDetail(),
				'results'	=> $e->getResults()
			];
			return new DataResponse($message, Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}
}
