<?php

namespace DigitalMarketingFramework\Distributor\Mail\Model\Data\Value;

use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;

class EmailValue implements ValueInterface
{
    public function __construct(
        protected string $address,
        protected string $name = ''
    ) {
    }

    public function __toString(): string
    {
        if ($this->name !== '') {
            return sprintf('%s <%s>', $this->name, $this->address);
        }
        return $this->address;
    }

    public function getValue(): string
    {
        return (string)$this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string> $data
     */
    public function pack(): array
    {
        return [
            'address' => $this->address,
            'name' => $this->name,
        ];
    }

    /**
     * @param array<string> $packed
     */
    public static function unpack(array $packed): ValueInterface
    {
        return new static($packed['address'], $packed['name']);
    }
}
