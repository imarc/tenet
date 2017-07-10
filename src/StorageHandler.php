<?php

namespace Tenet;

use InvalidArgumentException;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 *
 */
class StorageHandler
{
	/**
	 *
	 */
	protected $accessor;


	/**
	 *
	 */
	protected $directory;


	/**
	 *
	 */
	public function __construct(Accessor $accessor, $storage_directory)
	{
		$this->accessor  = $accessor;
		$this->directory = rtrim(realpath($storage_directory), '/\\' . DIRECTORY_SEPARATOR);

		if (!is_writable($this->directory) || !is_dir($this->directory)) {
			throw new InvalidArgumentException(sprintf(
				'The provided storage root "%s" is not a directory or is not writable',
				$storage_directory
			));
		}
	}


	/**
	 *
	 */
	public function getDirectory($sub_directory = NULL)
	{
		if ($sub_directory == NULL) {
			return $this->directory . DIRECTORY_SEPARATOR;
		}

		$sub_directory = ltrim($sub_directory, '/\\' . DIRECTORY_SEPARATOR);
		$directory     = $this->directory . DIRECTORY_SEPARATOR . $sub_directory;

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

		$class    = get_class($entity);
		$manager  = $args->getObjectManager();
		$metadata = $manager->getClassMetadata($class);

		foreach ($metadata->getFieldNames() as $field) {
			if ($metadata->getTypeOfField($field) != FileType::FILE) {
				continue;
			}

			$callable = $entity->generateStoreCallable($field);

			$callable($this);
		}
	}
}
