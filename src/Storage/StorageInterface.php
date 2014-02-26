<?php
namespace Tenet\Storage;

interface StorageInterface
{
	public function storeField(StorageHandler $handler, $field);
}