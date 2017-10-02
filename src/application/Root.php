<?php

namespace OrganizeSeries\application;

use InvalidArgumentException;
use OrganizeSeries\domain\exceptions\InvalidInterfaceException;
use OrganizeSeries\domain\interfaces\AbstractBootstrap;
use OrganizeSeries\domain\Meta;
use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;
use OrganizeSeries\domain\services\ExtensionsRegistry;
use Pimple\Container as PimpleContainer;

/**
 * Root
 * This provides access to base services that are used everywhere (i.e. DI container).
 * @package OrganizeSeries\application
 * @author  Darren Ethier
 * @since   1.0.0
 */
class Root
{
	/**
	 * @var Container
	 */
	private static $container;


    /**
     * @var Meta
     */
	private static $core_meta;


    /**
     * Whether or not core has been initialized.
     * @var bool
     */
	private static $initialized = false;


    /**
     * Sets up container and Meta
     *
     * @param $file
     * @param $version
     * @throws InvalidArgumentException
     * @throws InvalidInterfaceException
     */
	public static function initialize($file, $version)
    {
        if (! self::$initialized) {
            $core_meta_fqcn = new ClassOrInterfaceFullyQualifiedName(
                Meta::class
            );
            self::container()->registerParameter('coreVersion', $version);
            self::container()->registerParameter('coreFile', $file);
            self::container()->registerDependency(
                $core_meta_fqcn,
                function ($container) {
                    return new Meta(
                        $container['coreFile'],
                        $container['coreVersion']
                    );
                }
            );
            self::$core_meta = self::container()->make($core_meta_fqcn);
        }
    }


    /**
     * Extensions use this to initialize their meta class.  All that happens is it is registered with the container.
     * It is left up to the add-on how they retrieve their meta.  It is recommended they add a static method on their
     * bootstrap class to retrieve the meta object from the container.
     *
     * @param string                             $file
     * @param string                             $version
     * @param ClassOrInterfaceFullyQualifiedName $extension_meta_fully_qualified_class_name
     * @throws InvalidArgumentException
     */
    public static function initializeExtensionMeta(
        $file,
        $version,
        ClassOrInterfaceFullyQualifiedName $extension_meta_fully_qualified_class_name
    ) {
        $parameter_prefix = md5($extension_meta_fully_qualified_class_name->__toString());
	    self::container()->registerParameter($parameter_prefix . 'File', $file);
	    self::container()->registerParameter($parameter_prefix . 'Version', $version);
	    self::container()->registerDependency(
	        $extension_meta_fully_qualified_class_name,
            function ($container) use ($parameter_prefix, $extension_meta_fully_qualified_class_name) {
	            $class_name = $extension_meta_fully_qualified_class_name->__toString();
	            return new $class_name(
	                $container[$parameter_prefix . 'File'],
                    $container[$parameter_prefix . 'Version']
                );
            }
        );
    }



    /**
     * @return Meta
     */
    public static function coreMeta()
    {
        return self::$core_meta;
    }


    /**
     * @return Container
     */
	public static function container()
	{
		if (! self::$container instanceof Container)
		{
			self::$container = new Container(new PimpleContainer());
		}
		return self::$container;
	}


    /**
     * Extensions can use this to register their bootstrap file with the Container.
     * Bootstrap is the only class that has to be registered this way.  All other classes the extension uses should be
     * registered within their Bootstrap file.
     * @param ClassOrInterfaceFullyQualifiedName $bootstrap_class
     * @throws InvalidArgumentException
     */
	public static function registerAndLoadExtensionBootstrap(ClassOrInterfaceFullyQualifiedName $bootstrap_class)
    {
        $bootstrap_fully_qualified_class_name = $bootstrap_class->__toString();
        if (! in_array(AbstractBootstrap::class, class_parents($bootstrap_class->__toString()), true))
        {
            throw new InvalidArgumentException(
                sprintf(
                    esc_html__(
                        'The %1$s method can only be used to register a child of %2%s.',
                        'organize-series'
                    ),
                    __METHOD__,
                    AbstractBootstrap::class
                )
            );
        }
        self::container()->registerDependency(
            $bootstrap_class,
            function($container) use ($bootstrap_fully_qualified_class_name) {
                return new $bootstrap_fully_qualified_class_name(
                    $container[ExtensionsRegistry::class],
                    $container[Router::class],
                    self::container()
                );
            }
        );
        self::container()->make($bootstrap_class);
    }
}