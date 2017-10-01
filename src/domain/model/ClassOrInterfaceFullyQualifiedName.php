<?php

namespace OrganizeSeries\domain\model;
use function interface_exists;
use OrganizeSeries\domain\exceptions\InvalidInterfaceException;

/**
 * ClassOrInterfaceFullyQualifiedName;
 * A value object for a class or interface string.
 * Validates that the string represents a valid class or interface.
 *
 * @package OrganizeSeries\domain\model
 * @author  Darren Ethier
 * @since   1.0.0
 */
class ClassOrInterfaceFullyQualifiedName
{
	/**
	 * @var string
	 */
	private $class_or_interface_fqcn;
	
	
	/**
	 * ClassOrInterfaceFullyQualifiedName constructor.
	 *
	 * @param string $class_or_interface_fqcn
	 *
	 * @throws InvalidInterfaceException
	 */
	public function __construct($class_or_interface_fqcn)
	{
		$class_or_interface_fqcn = (string) $class_or_interface_fqcn;
		if (! interface_exists($class_or_interface_fqcn) && ! class_exists($class_or_interface_fqcn)) {
			throw new InvalidInterfaceException($class_or_interface_fqcn);
		}
		$this->class_or_interface_fqcn = $class_or_interface_fqcn;
	}
	
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->class_or_interface_fqcn;
	}
}