<?php

namespace DigitalMarketingFramework\Distributor\Mail\Route;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Distributor\Mail\DataDispatcher\MailDataDispatcher;

class MailRoute extends AbstractMailRoute
{
    public const KEY_VALUE_DELIMITER = 'valueDelimiter';
    public const DEFAULT_VALUE_DELIMITER = '\s=\s';

    public const KEY_LINE_DELIMITER = 'lineDelimiter';
    public const DEFAULT_LINE_DELIMITER = '\n';

    protected function getDispatcher(): MailDataDispatcher
    {
        /** @var MailDataDispatcher $dispatcher */
        $dispatcher = parent::getDispatcher();
        $dispatcher->setValueDelimiter($this->getConfig(static::KEY_VALUE_DELIMITER));
        $dispatcher->setLineDelimiter($this->getConfig(static::KEY_LINE_DELIMITER));
        return $dispatcher;
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();
        $schema->addProperty(static::KEY_VALUE_DELIMITER, new StringSchema(static::DEFAULT_VALUE_DELIMITER));
        $schema->addProperty(static::KEY_LINE_DELIMITER, new StringSchema(static::DEFAULT_LINE_DELIMITER));
        return $schema;
    }
}