<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Service;

use DateTime;
use DateTimeInterface;
use Exception;

use OC;
use OC\Config;
use OC\Files\Filesystem;
use OC\User\NoUserException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\LinkCreator\Db\Link;
use OCA\LinkCreator\Db\LinkMapper;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IConfig;
use OCP\IDateTimeFormatter;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUserManager;
use OCP\IUserSession;
use OCP\Security\ISecureRandom;

class LinkService {
	private LinkMapper $mapper;
    private IRootFolder $storage;
	protected string $appName;

	private IL10N $l10n;

	private IUserSession $currentUserSession;

	private IUserManager $userManager;

	private ISecureRandom $secureRandom;

	private IDateTimeFormatter $dateTimeFormatter;

	private IConfig $config;
	private IRequest $request;

	public function __construct(string $appName,
								IRequest $request,
								IUserSession $userSession,
								IUserManager $userManager,
								IL10N $l10n,
								ISecureRandom $secureRandom,
								IDateTimeFormatter $dateTimeFormatter,
								IConfig $config,
								LinkMapper $mapper,
								IRootFolder $storage) {
		$this->mapper = $mapper;
        $this->storage = $storage;
		$this->appName = $appName;
		$this->userManager = $userManager;
		$this->currentUserSession = $userSession;
		$this->request = $request;
		$this->l10n = $l10n;
		$this->secureRandom = $secureRandom;
		$this->dateTimeFormatter = $dateTimeFormatter;
		$this->config = $config;
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
	 * @throws LinkNotCreatable
	 * @throws \OCP\DB\Exception
	 */
    public function create(string $from, string $to): Link
	{
		$rootDir	= $this->config->getSystemValue('datadirectory');
		$user		= $this->currentUserSession->getUser();
        $home		= $user->getHome();
		$toParts	= explode('/', trim($to, '/'));
		$toFileName	= array_pop($toParts);
		$toParent	= implode('/', $toParts);

		try {
			if($this->storage->getUserFolder($user->getUID())->nodeExists(trim($to, '/'))) {
				throw new LinkNotCreatable(
					'File exists already',
					LinkNotCreatable::OPERATION_NOT_PERMITTED,
					'',
					400
				);
			}
			$fromFileInfo	= $this->storage->getUserFolder($user->getUID())->get(trim($from, '/'));
			$toFileInfo		= $this->storage->getUserFolder($user->getUID())->get($toParent);
		}
		catch (NotFoundException $exception) {
			throw new LinkNotCreatable(
				$exception->getMessage(),
				LinkNotCreatable::NO_SUCH_FILE_OR_DIRECTORY,
				'',
				$exception->getCode(),
				$exception
			);
		}
		catch (NotPermittedException $exception) {
			throw new LinkNotCreatable(
				$exception->getMessage(),
				LinkNotCreatable::OPERATION_NOT_PERMITTED,
				'',
				$exception->getCode(),
				$exception
			);
		}
		catch (NoUserException $exception) {
			throw new LinkNotCreatable(
				$exception->getMessage(),
				LinkNotCreatable::PERMISSION_DENIED,
				'',
				$exception->getCode(),
				$exception
			);
		}

		$fromAbsolute	= $rootDir.$fromFileInfo->getPath();
		$toAbsolute		= $rootDir.$toFileInfo->getPath()."/$toFileName";
        $link = new Link();
		$link->setFrom($from);
		$link->setTo($to);
		$link->setUserId($user->getUID());
        $link->setFromAbsolute($fromAbsolute);
		$link->setToAbsolute($toAbsolute);
        $link->setCreatedAt((new DateTime())->format('Y-m-d H:i:s'));
		$link->setResultCode(0);

        $results = [];
        $code = 0;

		exec("ln -s $fromAbsolute $toAbsolute", $results, $code);
		if($code !== 0) {
			switch ($code) {
				case 1:
					throw new LinkNotCreatable(
						'Link could not be Created due to an Error',
						LinkNotCreatable::OPERATION_NOT_PERMITTED,
						implode("\n", $results),
						$code);
				case 2:
					throw new LinkNotCreatable(
						'Link could not be Created due to an Error',
						LinkNotCreatable::NO_SUCH_FILE_OR_DIRECTORY,
						implode("\n", $results),
						$code);
				case 64:
					throw new LinkNotCreatable(
						'Link could not be Created due to an Error',
						LinkNotCreatable::SYNTAX_ERROR,
						implode("\n", $results),
						$code);
				case 66:
					throw new LinkNotCreatable(
						'Link could not be Created due to an Error',
						LinkNotCreatable::PERMISSION_DENIED,
						implode("\n", $results),
						$code);
				case 69:
					throw new LinkNotCreatable(
						'Link could not be Created due to an Error',
						LinkNotCreatable::TOO_MANY_LINKS,
						implode("\n", $results),
						$code);
				case 73:
					throw new LinkNotCreatable(
						'Link could not be Created due to an Error',
						LinkNotCreatable::RO_FILESYSTEM,
						implode("\n", $results),
						$code);
				case 74:
					throw new LinkNotCreatable(
						'Link could not be Created due to an Error',
						LinkNotCreatable::INVALID_CROSS_DEVICE_LINK,
						implode("\n", $results),
						$code);
				case 131:
					throw new LinkNotCreatable(
						'Link could not be Created due to an Error',
						LinkNotCreatable::BROKEN_PIPE,
						implode("\n", $results),
						$code);
				default:
					throw new LinkNotCreatable(
						'Link could not be Created due to an Unknown Error',
						'Error unknown',
						implode("\n", $results),
						$code);
			}
		}

		try {
			$scanner = $this->storage->getUserFolder($user->getUID())->getStorage()->getScanner();
		}
		catch (NotFoundException $exception) {
			throw new LinkNotCreatable(
				$exception->getMessage(),
				LinkNotCreatable::NO_SUCH_FILE_OR_DIRECTORY,
				'',
				$exception->getCode(),
				$exception
			);
		}
		catch (NotPermittedException $exception) {
			throw new LinkNotCreatable(
				$exception->getMessage(),
				LinkNotCreatable::OPERATION_NOT_PERMITTED,
				'',
				$exception->getCode(),
				$exception
			);
		}
		catch (NoUserException $exception) {
			throw new LinkNotCreatable(
				$exception->getMessage(),
				LinkNotCreatable::PERMISSION_DENIED,
				'',
				$exception->getCode(),
				$exception
			);
		}

		$scanner->scan($toParent);
		$link->setResultString(implode("\n", $results));
		$link->setResultCode(0);
		return $this->mapper->insert($link);
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
