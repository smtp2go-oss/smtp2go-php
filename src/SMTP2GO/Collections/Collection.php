<?php

namespace SMTP2GO\Collections;

class Collection implements \ArrayAccess, \Iterator
{
    protected $items;
    protected $position;

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function add($item)
    {
        $this->items[] = $item;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param mixed $offset 
     * @return void 
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    
    /**
     * @param mixed $offset 
     * @return mixed 
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    /**
     * Get the value of items
     */
    public function getItems()
    {
        return $this->items;
    }

    /** @return void  */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /** @return mixed  */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->items[$this->position];
    }

    /** @return mixed  */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /** @return void  */
    public function next(): void
    {
        ++$this->position;
    }

    /** @return bool  */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }
}
