<?php

namespace OrganizeSeries\domain\interfaces;

/**
 * Interface CollectionInterface
 * Inspired by Event Espresso Collection Interface.
 *
 * @package OrganizeSeries\domain\interfaces
 * @subpackage
 * @author  Darren Ethier
 * @since   2.5.7
 */
interface CollectionInterface
{
    /**
     * Attaches an object to the Collection optionally with the given identifier.
     *
     * @param  object $object
     * @param  string $identifier @see setIdentifier()
     * @return bool
     */
    public function add($object, $identifier = '');

    /**
     * Sets the data associated with an object in the Collection
     * if no $identifier is supplied, then the spl_object_hash() is used
     *
     * @param  object $object
     * @param  string $identifier
     * @return bool
     */
    public function setIdentifier($object, $identifier = '');

    /**
     * Finds and returns an object in the Collection based on the identifier that was set using addObject()
     * Note: the pointer is reset to the beginning of the collection before returning
     *
     * @param string $identifier
     * @return object
     */
    public function get($identifier);

    /**
     * Returns TRUE or FALSE depending on whether the object is within the Collection
     * based on the supplied $identifier
     *
     * @param  string $identifier
     * @return bool
     */
    public function has($identifier);

    /**
     * Returns TRUE or FALSE depending on whether the supplied object is within the Collection
     *
     * @param object $object
     * @return bool
     */
    public function hasObject($object);

    /**
     * Detaches an object from the Collection
     *
     * @param object $object
     * @return bool
     */
    public function remove($object);

    /**
     * Advances pointer to the object whose identifier matches that which was provided
     *
     * @param string $identifier
     * @return boolean
     */
    public function setCurrent($identifier);

    /**
     * Advances pointer to the provided object
     *
     * @param object $object
     * @return boolean
     */
    public function setCurrentUsingObject($object);

    /**
     * Returns the object occupying the index before the current object,
     * unless this is already the first object, in which case it just returns the first object
     *
     * @return object
     */
    public function previous();

    /**
     * Returns the index of a given object, or false if not found
     *
     * @see http://stackoverflow.com/a/8736013
     * @param object $object
     * @return boolean|int|string
     */
    public function indexOf($object);


    /**
     * Returns the object at the given index
     *
     * @see http://stackoverflow.com/a/8736013
     * @param boolean|int|string $index
     * @return object
     */
    public function objectAtIndex($index);

    /**
     * Returns the sequence of objects as specified by the offset and length
     *
     * @see http://stackoverflow.com/a/8736013
     * @param int $offset
     * @param int $length
     * @return array
     */
    public function slice($offset, $length);

    /**
     * Inserts an object (or an array of objects) at a certain point
     *
     * @see http://stackoverflow.com/a/8736013
     * @param object[]|object $objects A single object or an array of objects
     * @param int             $index
     */
    public function insertAt($objects, $index);

    /**
     * Removes the object at the given index
     *
     * @see http://stackoverflow.com/a/8736013
     * @param integer $index
     */
    public function removeAt($index);


    /**
     * detaches ALL objects from the Collection
     */
    public function detachAll();


    /**
     * unsets and detaches ALL objects from the Collection
     */
    public function trashAndDetachAll();
}