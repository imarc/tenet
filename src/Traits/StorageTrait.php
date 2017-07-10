<?php

namespace Tenet;

use SplFileInfo;
use RuntimeException;

/**
 *
 */
trait StorageTrait
{
	/**
	 *
	 */
	public function generateStoreCallable($field)
	{
		$callable = [$this, 'store' . ucfirst($field)];

		if (!is_callable($callable)) {
			$callable = function($handler) use ($field) {
				$this->store($field, $handler);
			};
		}

		return $callable;
	}


	/**
	 *
	 */
	public function store($field, StorageHandler $handler)
	{
		$suffix     = ucfirst($field);
		$get_method = 'get' . $suffix;
		$set_method = 'set' . $suffix;
		$file       = $this->$get_method();

		if (!$file instanceof SplFileInfo) {
			return;
		}

		$storage_directory = $handler->getStorageDirectory('files');

		if (($file->getPath() . DIRECTORY_SEPARATOR) == $storage_directory) {
			return;
		}

		$content_md5_hash  = md5_file($file->getRealPath());
		$storage_path      = $storage_directory . $content_md5_hash . '.' . $file->getClientOriginalExtension();

		if (!file_exists($storage_path)) {
			if (!copy($file->getRealPath(), $storage_path)) {
				throw new RuntimeException(sprintf(
					'Could not store file "%s" at location "%s"',
					$file->getPathname(),
					$storage_path
				));
			}
		}

		$this->$set_method(new SplFileInfo($storage_path));
	}
}
