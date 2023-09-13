<?php

namespace DigitalMarketingFramework\Distributor\Mail\Route;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\StringSchema;
use DigitalMarketingFramework\Distributor\Core\Route\Route;
use DigitalMarketingFramework\Distributor\Mail\DataDispatcher\AbstractMailDataDispatcher;

abstract class AbstractMailRoute extends Route
{
    public const DATA_DISPATCHER_KEYWORD = 'mail';

    public const DEFAULT_PASSTHROUGH_FIELDS = true;

    public const KEY_FROM = 'sender';
    public const DEFAULT_FROM = '';

    public const KEY_TO = 'recipients';
    public const DEFAULT_TO = '';

    public const KEY_REPLY_TO = 'replyTo';
    public const DEFAULT_REPLY_TO = '';

    public const KEY_SUBJECT = 'subject';
    public const DEFAULT_SUBJECT = 'New Form Submission';

    public const KEY_ATTACH_UPLOADED_FILES = 'includeAttachmentsInMail';
    public const DEFAULT_ATTACH_UPLOADED_FILES = false;

    protected function getDispatcher(): AbstractMailDataDispatcher
    {
        /** @var AbstractMailDataDispatcher $dispatcher */
        $dispatcher = $this->registry->getDataDispatcher(static::DATA_DISPATCHER_KEYWORD);

        $dispatcher->setFrom($this->getConfig(static::KEY_FROM));
        $dispatcher->setTo($this->getConfig(static::KEY_TO));
        $dispatcher->setReplyTo($this->getConfig(static::KEY_REPLY_TO));
        $dispatcher->setSubject($this->getConfig(static::KEY_SUBJECT));

        $attachUploadedFiles = $this->getConfig(static::KEY_ATTACH_UPLOADED_FILES);
        $dispatcher->setAttachUploadedFiles($attachUploadedFiles);

        return $dispatcher;
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();
        $schema->addProperty(static::KEY_FROM, new StringSchema(static::DEFAULT_FROM));
        $schema->addProperty(static::KEY_TO, new StringSchema(static::DEFAULT_TO));
        $schema->addProperty(static::KEY_REPLY_TO, new StringSchema(static::DEFAULT_REPLY_TO));
        $schema->addProperty(static::KEY_SUBJECT, new StringSchema(static::DEFAULT_SUBJECT));
        $schema->addProperty(static::KEY_ATTACH_UPLOADED_FILES, new BooleanSchema(static::DEFAULT_ATTACH_UPLOADED_FILES));
        return $schema;
    }
}
