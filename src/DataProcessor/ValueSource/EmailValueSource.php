<?php

namespace DigitalMarketingFramework\Distributor\Mail\DataProcessor\ValueSource;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Core\DataProcessor\ValueSource\ValueSource;
use DigitalMarketingFramework\Distributor\Mail\Model\Data\Value\EmailValue;

class EmailValueSource extends ValueSource
{
    public const KEY_ADDRESS = 'address';
    public const DEFAULT_ADDRESS = '';

    public const KEY_NAME = 'name';
    public const DEFAULT_NAME = '';

    public function build(): null|EmailValue
    {
        if (!is_array($this->configuration)) {
            $this->configuration = [static::KEY_ADDRESS => $this->configuration];
        }

        $name = $this->getConfig(static::KEY_NAME);
        $address = $this->getConfig(static::KEY_ADDRESS);

        if (!$address) {
            return null;
        }

        return new EmailValue($address, $name);
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();
        $schema->addProperty(static::KEY_ADDRESS, new StringSchema());
        $schema->addProperty(static::KEY_NAME, new StringSchema());
        return $schema;
    }
}