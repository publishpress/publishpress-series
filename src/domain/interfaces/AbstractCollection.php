<?php

namespace OrganizeSeries\domain\interfaces;

use OrganizeSeries\domain\exceptions\InvalidEntityException;
use OrganizeSeries\domain\model\ClassOrInterfaceFullyQualifiedName;
use SplObjectStorage;

class AbstractCollection extends SplObjectStorage implements CollectionInterface
{
	/**
	 * @var string
	 */
	private $class_or_interface_restricted_to;


    /**
     * AbstractCollection constructor.
     *
     * @param ClassOrInterfaceFullyQualifiedName $class_or_interface_restricted_to
     */
	public function __construct(ClassOrInterfaceFullyQualifiedName $class_or_interface_restricted_to)
	{
		$this->class_or_interface_restricted_to = $class_or_interface_restricted_to->__toString();
	}

    /**
     * Attaches an object to the Collection optionally with the given identifier.
     *
     * @param  object $object
     * @param  string $identifier @see setIdentifier()
     * @return bool
     * @throws InvalidEntityException
     */
    public function add( $object, $identifier = null ) {
        if ( ! $object instanceof $this->class_or_interface_restricted_to ) {
            throw new InvalidEntityException( $object, $this->class_or_interface_restricted_to );
        }
        $this->attach( $object );
        $this->setIdentifier( $object, $identifier );
        return $this->contains( $object );
    }



    /**
     * Sets the data associated with an object in the Collection
     * if no $identifier is supplied, then the spl_object_hash() is used
     *
     * @param  object $object
     * @param  string $identifier
     * @return bool
     */
    public function setIdentifier( $object, $identifier = null ) {
        $identifier = ! empty( $identifier ) ? $identifier : spl_object_hash( $object );
        $this->rewind();
        while ( $this->valid() ) {
            if ( $object === $this->current() ) {
                $this->setInfo( $identifier );
                $this->rewind();
                return true;
            }
            $this->next();
        }
        return false;
    }



    /**
     * Finds and returns an object in the Collection based on the identifier that was set using addObject()
     * Note: the pointer is reset to the beginning of the collection before returning
     *
     * @param string $identifier
     * @return object
     */
    public function get( $identifier ) {
        $this->rewind();
        while ( $this->valid() ) {
            if ( $identifier === $this->getInfo() ) {
                $object = $this->current();
                $this->rewind();
                return $object;
            }
            $this->next();
        }
        return null;
    }



    /**
     * Returns TRUE or FALSE depending on whether the object is within the Collection
     * based on the supplied $identifier
     *
     * @param  string $identifier
     * @return bool
     */
    public function has( $identifier ) {
        $this->rewind();
        while ( $this->valid() ) {
            if ( $identifier === $this->getInfo() ) {
                $this->rewind();
                return true;
            }
            $this->next();
        }
        return false;
    }



    /**
     * Returns TRUE or FALSE depending on whether the supplied object is within the Collection
     *
     * @param object $object
     * @return bool
     */
    public function hasObject( $object ) {
        return $this->contains( $object );
    }



    /**
     * Detaches an object from the Collection
     *
     * @param object $object
     * @return bool
     */
    public function remove( $object ) {
        $this->detach( $object );
        return true;
    }



    /**
     * Advances pointer to the object whose identifier matches that which was provided
     *
     * @param string $identifier
     * @return boolean
     */
    public function setCurrent( $identifier ) {
        $this->rewind();
        while ( $this->valid() ) {
            if ( $identifier === $this->getInfo() ) {
                return true;
            }
            $this->next();
        }
        return false;
    }



    /**
     * Advances pointer to the provided object
     *
     * @param object $object
     * @return boolean
     */
    public function setCurrentUsingObject( $object ) {
        $this->rewind();
        while ( $this->valid() ) {
            if ( $this->current() === $object ) {
                return true;
            }
            $this->next();
        }
        return false;
    }



    /**
     * Returns the object occupying the index before the current object,
     * unless this is already the first object, in which case it just returns the first object
     *
     * @return object
     */
    public function previous() {
        $index = $this->indexOf( $this->current() );
        if ( $index === 0 ) {
            return $this->current();
        }
        $index--;
        return $this->objectAtIndex( $index );
    }



    /**
     * Returns the index of a given object, or false if not found
     *
     * @see http://stackoverflow.com/a/8736013
     * @param object $object
     * @return boolean|int|string
     */
    public function indexOf( $object ) {
        if ( ! $this->contains( $object ) ) {
            return false;
        }
        foreach ( $this as $index => $obj ) {
            if ( $obj === $object ) {
                return $index;
            }
        }
        return false;
    }



    /**
     * Returns the object at the given index
     *
     * @see http://stackoverflow.com/a/8736013
     * @param boolean|int|string $index
     * @return object
     */
    public function objectAtIndex( $index ) {
        $iterator = new LimitIterator( $this, $index, 1 );
        $iterator->rewind();
        return $iterator->current();
    }



    /**
     * Returns the sequence of objects as specified by the offset and length
     *
     * @see http://stackoverflow.com/a/8736013
     * @param int $offset
     * @param int $length
     * @return array
     */
    public function slice( $offset, $length ) {
        $slice = array();
        $iterator = new LimitIterator( $this, $offset, $length );
        foreach ( $iterator as $object ) {
            $slice[] = $object;
        }
        return $slice;
    }



    /**
     * Inserts an object (or an array of objects) at a certain point
     *
     * @see http://stackoverflow.com/a/8736013
     * @param object[]|object $objects A single object or an array of objects
     * @param int             $index
     */
    public function insertAt( $objects, $index ) {
        if ( ! is_array( $objects ) ) {
            $objects = array( $objects );
        }
        // check to ensure that objects don't already exist in the collection
        foreach ( $objects as $key => $object ) {
            if ( $this->contains( $object ) ) {
                unset( $objects[ $key ] );
            }
        }
        // do we have any objects left?
        if ( ! $objects ) {
            return;
        }
        // detach any objects at or past this index
        $remaining = array();
        if ( $index < $this->count() ) {
            $remaining = $this->slice( $index, $this->count() - $index );
            foreach ( $remaining as $object ) {
                $this->detach( $object );
            }
        }
        // add the new objects we're splicing in
        foreach ( $objects as $object ) {
            $this->attach( $object );
        }
        // attach the objects we previously detached
        foreach ( $remaining as $object ) {
            $this->attach( $object );
        }
    }



    /**
     * Removes the object at the given index
     *
     * @see http://stackoverflow.com/a/8736013
     * @param integer $index
     */
    public function removeAt( $index ) {
        $this->detach( $this->objectAtIndex( $index ) );
    }



    /**
     * detaches ALL objects from the Collection
     */
    public function detachAll()
    {
        $this->rewind();
        while ($this->valid()) {
            $object = $this->current();
            $this->next();
            $this->detach($object);
        }
    }



    /**
     * unsets and detaches ALL objects from the Collection
     */
    public function trashAndDetachAll()
    {
        $this->rewind();
        while ($this->valid()) {
            $object = $this->current();
            $this->next();
            $this->detach($object);
            unset($object);
        }
    }
}