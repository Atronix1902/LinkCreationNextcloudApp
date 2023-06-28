<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000001Date20230624220931 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('linkcreator')) {
			$table = $schema->createTable('linkcreator');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('from', 'string', [
				'notnull' => true,
				'length' => 511
			]);
            $table->addColumn('to', 'string', [
                'notnull' => true,
                'length' => 511
            ]);
            $table->addColumn('from_absolute', 'string', [
                'notnull' => true,
                'length' => 511
            ]);
            $table->addColumn('to_absolute', 'string', [
                'notnull' => true,
                'length' => 511
            ]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 200,
			]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true
            ]);

			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'linkcreator_user_id_index');
		}
		return $schema;
	}
}
