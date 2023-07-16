<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\LinkCreator\Service;

use Throwable;

class LinkNotCreatable extends \Exception {
	public const OPERATION_NOT_PERMITTED = 'The user may not have sufficient permissions to create the symbolic link. Or the link already exists';
	public const NO_SUCH_FILE_OR_DIRECTORY = 'The target file or directory specified in the command does not exist';
	public const SYNTAX_ERROR = 'There might be a syntax error in the ln -s command or incorrect usage of the command-line options';
	public const PERMISSION_DENIED = 'The user does not have the necessary permissions to access the source or destination directory';
	public const TOO_MANY_LINKS = 'The maximum number of symbolic links for the file system has been reached';
	public const RO_FILESYSTEM = 'The file system where the link is being created is mounted as read-only, preventing the creation of symbolic links';
	public const INVALID_CROSS_DEVICE_LINK = 'The source and destination directories are on different file systems, and the operating system does not support creating symbolic links across file systems';
	public const BROKEN_PIPE = 'An error occurred while trying to write the symbolic link to the destination directory';

	private string $results;
	private string $detail;
	public function __construct(string $message, string $detail, string $results, int $code, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
		$this->results = $results;
		$this->detail = $detail;
	}

	public function getResults(): string
	{
		return $this->results;
	}

	public function getDetail(): string
	{
		return $this->detail;
	}
}
