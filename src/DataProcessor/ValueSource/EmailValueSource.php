<?php

namespace DigitalMarketingFramework\Distributor\Mail\DataProcessor\ValueSource;

use DigitalMarketingFramework\Core\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\Custom\ValueSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\DataProcessor\ValueSource\ValueSource;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Distributor\Mail\Model\Data\Value\EmailValue;

class EmailValueSource extends ValueSource
{
    public const KEY_ADDRESS = 'address';

    public const KEY_NAME = 'name';

    public function build(): null|EmailValue
    {
        $name = $this->dataProcessor->processValue(
            $this->getConfig(static::KEY_NAME),
            $this->context->copy()
        );

        $address = $this->dataProcessor->processValue(
            $this->getConfig(static::KEY_ADDRESS),
            $this->context->copy()
        );

        if ($address === null || $address === '') {
            return null;
        }

        if ($address instanceof ValueInterface) {
            $address = (string)$address;
        }

        if ($name instanceof ValueInterface) {
            $name = (string)$name;
        }

        return new EmailValue($address, $name ?? '');
    }

    public static function modifiable(): bool
    {
        return false;
    }

    public static function canBeMultiValue(): bool
    {
        return false;
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();
        $schema->addProperty(static::KEY_ADDRESS, new CustomSchema(ValueSchema::TYPE));
        $schema->addProperty(static::KEY_NAME, new CustomSchema(ValueSchema::TYPE));

        return $schema;
    }
}
