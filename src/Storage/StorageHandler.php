<?php
namespace Tenet\Storage;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use InvalidArgumentException;
use RuntimeException;

class StorageHandler
{
	private $storageDirectory;

	/**
	 *
	 */
	public function __construct($storage_directory)
	{
		$this->storageDirectory = rtrim(realpath($storage_directory), '/\\' . DIRECTORY_SEPARATOR);

		if (!is_writable($this->storageDirectory) || !is_dir($this->storageDirectory)) {
			throw new InvalidArgumentException(sprintf(
				'The provided storage root "%s" is not a directory or is not writable',
				$storage_directory
			));
		}
	}


	/**
	 *
	 */
	public function getStorageDirectory($sub_directory = NULL)
	{
		if ($sub_directory == NULL) {
			return $this->storageDirectory . DIRECTORY_SEPARATOR;
		}

		$sub_directory = ltrim($sub_directory, '/\\' . DIRECTORY_SEPARATOR);
		$directory     = $this->storageDirectory . DIRECTORY_SEPARATOR . $sub_directory;

		if (!is_writable($directory)) {
			if (@mkdir($directory, 0775, TRUE) === FALSE) {
				throw new RuntimeException(sprintf(
					'Unable to create writable directory at "%s"',
					$directory
				));
			}
		}

		return realpath($directory) . DIRECTORY_SEPARATOR;
	}


	/**
	 *
	 */
	public function preUpdate(LifecycleEventArgs $args)
	{
		$this->triggerStorage($args);
	}


	/**
	 *
	 */
	public function prePersist(LifecycleEventArgs $args)
	{
		$this->triggerStorage($args);
	}


	/**
	 *
	 */
	protected function triggerStorage(LifecycleEventArgs $args)
	{
		$entity = $args->getObject();

		if (!$entity instanceof StorageInterface) {
			return;
		}

		$manager   = $args->getObjectManager();
		$metadata  = $manager->getClassMetadata(get_class($entity));

		foreach ($metadata->getFieldNames() as $field) {
			$type = $metadata->getTypeOfField($field);

			if ($type == 'file') {
				$custom_store_method = 'store' . ucfirst($field);

				if (method_exists($entity, $custom_store_method)) {
					$entity->$custom_store_method($this);
				} else {
					$entity->storeField($this, $field);
				}
			}
		}
	}
}
