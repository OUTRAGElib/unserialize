<?php


namespace OUTRAGElib\Unserialize\Source;


trait SerializeInterfaceTrait
{
    /**
     *  Implements __wakeup functionality
     */
    public function unserialize($input)
    {
        foreach(unserialize($input) as $key => $value)
            $this->$key = $value;
    }
    
    
    /**
     *  Implements __sleep functionality
     */
    public function serialize()
    {
        return serialize(get_object_vars($this));
    }
}