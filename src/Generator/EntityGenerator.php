<?php

namespace Tenet\Storage;

use RuntimeException;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;

use Doctrine\Tests\Common\Proxy\ReturnTypesClass;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

class EntityGenerator
{
	/**
	 *
	 */
	protected $toOneTypes = [
		ClassMetadataInfo::ONE_TO_ONE,
		ClassMetadataInfo::MANY_TO_ONE
	];

	/**
	 *
	 */
	protected $typeMap = [
		Type::DATETIMETZ    => '\DateTime',
		Type::DATETIME      => '\DateTime',
		Type::DATE          => '\DateTime',
		Type::TIME          => '\DateTime',
		Type::OBJECT        => '\stdClass',
		Type::BIGINT        => 'integer',
		Type::SMALLINT      => 'integer',
		Type::TEXT          => 'string',
		Type::BLOB          => 'string',
		Type::DECIMAL       => 'string',
		Type::JSON_ARRAY    => 'array',
		Type::SIMPLE_ARRAY  => 'array',
	];



	/**
	 *
	 */
	public function __construct(EntityManager $em, DisconnectedClassMetadataFactory $cmf)
	{
		$cmf->setEntityManager($em);

		$this->metaData      = $cmf->getAllMetaData();
		$this->entityManager = $em;
	}


	/**
	 *
	 */
	public function build()
	{
		foreach ($this->metaData as $meta_data) {
			$class_name = $meta_data->getName();
			$space_name = $this->parseNamespace($class_name);

			printf('Generated classes related to %s\%s' . PHP_EOL, $space_name, $class_name);

			$base_space  = new PhpNamespace(ltrim($space_name . '\\' . $this->baseNamespace, '\\'));
			$base_class  = $base_space->addClass($class_name);
			$constructor = $base_class->addMethod('__construct')
				-> setVisibility("public")
				-> addComment("Instantiate a new " . $base_class->getName())
			;

			if($this->rootClass) {
				$base_space->addUse($this->entityParent);
				$base_class->setExtends($this->entityParent);
			}

			$base_space->addUse('Doctrine\Common\Collections\ArrayCollection');

			foreach ($meta_data->getFieldNames() as $field) {
				$type = $this->translateType($meta_data->getTypeOfField($field));

				$base_class->addProperty($field)
					-> setVisibility("protected")
					-> addComment("")
					-> addComment("@access protected")
					-> addComment("@var $type");
				;

				$base_class->addMethod('get' . ucfirst($field))
					-> setVisibility("public")
					-> addComment("Get the value of $field")
					-> addComment("")
					-> addComment("@access public")
					-> addComment("@return $type The value of $field")
					-> addBody("return \$this->$field;")
				;

				$base_class->addMethod('set' . ucfirst($field))
					-> setVisibility("public")
					-> addComment("Set the value of $field")
					-> addComment("")
					-> addComment("@access public")
					-> addComment("@param $type \$value The value to set to $field")
					-> addComment("@return " . $base_class->getName() . " The object instance for method chaining")
					-> addBody("\$this->$field = \$value;")
					-> addBody("")
					-> addBody("return \$this;")
					-> addParameter("value")
				;
			}

			foreach ($meta_data->getAssociationMappings() as $mapping) {
				$field = $mapping['fieldName'];
				$type  = in_array($mapping['type'], $this->toOneTypes)
					? $mapping['targetEntity']
					: 'ArrayCollection';

				$base_class->addProperty($field)
					-> setVisibility("protected")
					-> addComment("")
					-> addComment("@access protected")
					-> addComment("@var $type");
				;

				$base_class->addMethod('get' . ucfirst($field))
					-> setVisibility("public")
					-> addComment("Get the value of $field")
					-> addComment("")
					-> addComment("@access public")
					-> addComment("@return $type The value of $field")
					-> addBody("return \$this->$field;")
				;

				if ($type == 'ArrayCollection') {
					$constructor->addBody("\$this->$field = new ArrayCollection();");

					//
					// hasRelatedEntities()
					// addRelatedEntities()
					// removeRelatedEntities()
					//

				} else {

					//
					// hasRelatedEntity()
					//
					// On set, if value is set to null, check if bi-directional and remove
					//

					$base_class->addMethod('set' . ucfirst($field))
						-> setVisibility("public")
						-> addComment("Set the value of $field")
						-> addComment("")
						-> addComment("@access public")
						-> addComment("@param $type \$value The value to set to $field")
						-> addComment("@return " . $base_class->getName() . " The object instance for method chaining")
						-> addBody("\$this->$field = \$value;")
						-> addBody("")
						-> addBody("return \$this;")
						-> addParameter("value")
						-> setTypeHint($type);
				}
			}

			$this->sortMethods($base_class);
			$this->sortProperties($base_class);

			$this->write($this->entityRoot, $base_space, $base_class, TRUE);

			$space = new PhpNamespace($space_name);
			$class = $space->addClass($class_name)
				-> setExtends(ltrim($space_name . '\\' . $this->baseNamespace . '\\' . $class_name, '\\'))
			;

			$class->addMethod('__construct')
				-> setVisibility("public")
				-> addComment("Instantiate a new " . $class->getName())
				-> addBody("return parent::__construct();");
			;

			$this->write($this->entityRoot, $space, $class);

			if ($meta_data->customRepositoryClassName) {
				$repo_class_name = $meta_data->customRepositoryClassName;
				$repo_space_name = $this->parseNamespace($repo_class_name);

				$repo_space      = new PhpNamespace($repo_space_name);
				$repo_class      = $repo_space->addClass($repo_class_name);

				$repo_space->addUse($this->repositoryParent);
				$repo_class->setExtends($this->repositoryParent);

				$this->write($this->repositoryRoot, $repo_space, $repo_class);
			}
		}
	}


	/**
	 *
	 */
	public function setBaseNamespace($namespace)
	{
		$this->baseNamespace = $namespace;
	}


	/**
	 *
	 */
	public function setEntityParentClass($parent_class)
	{
		$this->entityParent = $parent_class;
	}


	/**
	 *
	 */
	public function setEntityRoot($root_directory)
	{
		$this->entityRoot = $root_directory;
	}


	/**
	 *
	 */
	public function setRepositoryParentClass($parent_class)
	{
		$this->repositoryParent = $parent_class;
	}


	/**
	 *
	 */
	public function setRepositoryRoot($root_directory)
	{
		$this->repositoryRoot = $root_directory;
	}


	/**
	 *
	 */
	protected function parseNamespace(&$class)
	{
		$parts = explode('\\', $class);
		$class = array_pop($parts);

		return implode('\\', $parts);
	}


	/**
	 *
	 */
	protected function sortMethods($class)
	{
		$methods = $class->getMethods();

		usort($methods, function($a, $b) {
			return $a->getName() < $b->getName()
				? -1
				: 1;
		});

		$class->setMethods($methods);
	}


	/**
	 *
	 */
	protected function sortProperties($class)
	{
		$properties = $class->getProperties();

		usort($properties, function($a, $b) {
			return $a->getName() < $b->getName()
				? -1
				: 1;
		});

		$class->setProperties($properties);
	}


	/**
	 *
	 */
	protected function translateType($type)
	{
		return isset($this->typeMap[$type])
			? $this->typeMap[$type]
			: $type;
	}


	/**
	 *
	 */
	protected function write($target_path, $space, $class, $overwrite = FALSE)
	{
		$space_path = str_replace('\\', DIRECTORY_SEPARATOR, $space->getName());
		$directory  = $target_path . DIRECTORY_SEPARATOR . $space_path;
		$file_path  = $directory . DIRECTORY_SEPARATOR . $class->getName() . '.php';

		if (!is_dir($directory)) {
			if (!@mkdir($directory, 0755, TRUE)) {
				throw new RuntimeException(sprintf(
					'Could not create directory "%s" for writing',
					$directory
				));
			}
		}

		if (!is_writable($directory)) {
			throw new RuntimeException(sprintf(
				'Directory "%s" is not writable',
				$directory
			));
		}

		if (file_exists($file_path)) {
			if ($overwrite) {
				if (!is_writable($file_path)) {
					throw new RuntimeException(sprintf(
						'Could not write file "%s", file is not writable',
						$file_path
					));
				}

				return file_put_contents($file_path, '<?php ' . $space);

			} else {
				return FALSE;
			}
		}
	}
}
