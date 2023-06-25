<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Link>
 */
class LinkMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'linkcreator', Link::class);
	}

	/**
	 * @throws MultipleObjectsReturnedException
	 * @throws DoesNotExistException|Exception
     */
	public function find(int $id, string $userId): Link {
        $qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('linkcreator')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		return $this->findEntity($qb);
	}

    /**
     * @param string $userId
     * @return array
     * @throws Exception
     */
	public function findAll(string $userId): array {
        $qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('linkcreator')
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)));
		return $this->findEntities($qb);
	}
}
