<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Service;

use DateTime;
use DateTimeInterface;
use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\LinkCreator\Db\Link;
use OCA\LinkCreator\Db\LinkMapper;
use OCP\Files\IRootFolder;
use OCP\Files\NotPermittedException;

class LinkService {
	private LinkMapper $mapper;
    private IRootFolder $storage;

	public function __construct(LinkMapper $mapper, IRootFolder $storage) {
		$this->mapper = $mapper;
        $this->storage = $storage;
	}

    /**
     * @return list<Link>
     * @throws \OCP\DB\Exception
     */
	public function findAll(string $userId): array {
		return $this->mapper->findAll($userId);
	}

    /**
     * @return never
     * @throws LinkNotFound
     * @throws Exception
     */
	private function handleException(Exception $e) {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new LinkNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

    /**
     * @throws LinkNotFound
     */
    public function find(int $id, string $userId): Link {
		try {
			return $this->mapper->find($id, $userId);

			// in order to be able to plug in different storage backends like files
		// for instance it is a good idea to turn storage related exceptions
		// into service related exceptions so controllers and service users
		// have to deal with only one type of exception
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

    /**
     * @throws \OCP\DB\Exception
     * @throws NotPermittedException
     */
    public function create(string $from, string $to, string $userId): Link {
        $config = new \OC\Config('config/');
        $base_path = $config->getValue('datadirectory');
        if($base_path === '' || $base_path === null) {
            $base_path = explode('apps/linkcreator', $base_path)[0];
        }
        $path = $base_path.$this->storage->getUserFolder($userId)->getPath();
        $note = new Link();
		$note->setFrom($from);
		$note->setTo($to);
		$note->setUserId($userId);
        $note->setFromAbsolute($base_path);
        $note->setCreatedAt((new DateTime())->format(DateTimeInterface::ATOM));

        $results = [];
        $code = 0;

        $response = $this->mapper->insert($note);
        $response->result = exec("touch $path/$from", $results, $code)."$base_path"."$path|".implode("\n",$results)."|$code";

		return $response;
	}

    /**
     * @throws LinkNotFound
     */
    public function delete(int $id, string $userId): Link {
		try {
			$note = $this->mapper->find($id, $userId);
			$this->mapper->delete($note);
			return $note;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
