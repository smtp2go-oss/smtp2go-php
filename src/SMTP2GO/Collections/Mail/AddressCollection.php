<?php
namespace SMTP2GO\Collections\Mail;

use InvalidArgumentException;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Collections\Collection;

class AddressCollection extends Collection
{
    protected $items;

    public function __construct(array $addresses)
    {
        foreach ($addresses as $address) {
            $this->add($address);
        }
    }

    /**
     * @param mixed $address 
     * @return $this 
     * @throws InvalidArgumentException 
     */
    public function add($address)
    {
        if (is_a($address, Address::class)) {
            $this->items[] = $address;
        } else {
            throw new InvalidArgumentException('This collection expects objects of type ' . Address::class, ' but recieved ' . get_class($address));
        }
        return $this;
    }
}
