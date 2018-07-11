<?php


namespace OUTRAGElib\Unserialize\Source;


trait MagicSerializeTrait
{
    /**
     *  Implements __wakeup functionality
     */
    public function __wakeup()
    {
        # nothing to do, object has been created without a constructor
        # but with variables already in place
    }
    
    
    /**
     *  Implements __sleep functionality
     */
    public function __sleep()
    {
        return array_keys(get_object_vars($this));
    }
}