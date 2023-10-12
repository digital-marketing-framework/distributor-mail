<?php

namespace DigitalMarketingFramework\Distributor\Mail\Route;

use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\BooleanSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\ContainerSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\Custom\ValueSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\CustomSchema;
use DigitalMarketingFramework\Core\ConfigurationDocument\SchemaDocument\Schema\SchemaInterface;
use DigitalMarketingFramework\Core\DataProcessor\ValueSource\ConstantValueSource;
use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\TemplateEngine\TemplateEngineInterface;
use DigitalMarketingFramework\Distributor\Core\Route\Route;
use DigitalMarketingFramework\Distributor\Mail\DataDispatcher\MailDataDispatcher;

class MailRoute extends Route
{
    public const KEY_FROM = 'sender';

    public const KEY_TO = 'recipients';

    public const KEY_REPLY_TO = 'replyTo';

    public const KEY_SUBJECT = 'subject';

    public const DEFAULT_SUBJECT = 'New Form Submission';

    public const KEY_ATTACH_UPLOADED_FILES = 'includeAttachmentsInMail';

    public const DEFAULT_ATTACH_UPLOADED_FILES = false;

    public const KEY_PLAIN_TEMPLATE = 'plainTextTemplate';

    public const KEY_USE_HTML = 'useHtml';

    public const DEFAULT_USE_HTML = false;

    public const KEY_HTML_TEMPLATE = 'htmlTemplate';

    protected static function getDefaultPassthroughFields(): bool
    {
        return true;
    }

    protected function getDispatcher(): MailDataDispatcher
    {
        /** @var MailDataDispatcher $dispatcher */
        $dispatcher = $this->registry->getDataDispatcher('mail');

        $from = $this->dataProcessor->processValue(
            $this->getConfig(static::KEY_FROM),
            $this->getDataProcessorContext()
        );

        if ($from === null || $from === '') {
            throw new DigitalMarketingFrameworkException('MailDataDispatcher: $from must not be empty');
        }

        $dispatcher->setFrom($from);

        $to = $this->dataProcessor->processValue(
            $this->getConfig(static::KEY_TO),
            $this->getDataProcessorContext()
        );

        if ($to === null || $to === '') {
            throw new DigitalMarketingFrameworkException('MailDataDispatcher: $to must not be empty');
        }

        $dispatcher->setTo($to);

        $replyTo = $this->dataProcessor->processValue(
            $this->getConfig(static::KEY_REPLY_TO),
            $this->getDataProcessorContext()
        );
        $dispatcher->setReplyTo($replyTo);

        $subject = $this->dataProcessor->processValue(
            $this->getConfig(static::KEY_SUBJECT),
            $this->getDataProcessorContext()
        );

        if ($subject === null || $subject === '') {
            throw new DigitalMarketingFrameworkException('MailDataDispatcher: $subject must not be empty');
        }

        $dispatcher->setSubject($subject);

        $attachUploadedFiles = $this->getConfig(static::KEY_ATTACH_UPLOADED_FILES);
        $dispatcher->setAttachUploadedFiles($attachUploadedFiles);

        $dispatcher->setPlainTemplateConfig($this->getConfig(static::KEY_PLAIN_TEMPLATE));
        $dispatcher->setUseHtml($this->getConfig(static::KEY_USE_HTML));
        $dispatcher->setHtmlTemplateConfig($this->getConfig(static::KEY_HTML_TEMPLATE));

        return $dispatcher;
    }

    public static function getSchema(): SchemaInterface
    {
        /** @var ContainerSchema $schema */
        $schema = parent::getSchema();

        $subjectSchema = new CustomSchema(ValueSchema::TYPE, ValueSchema::createStandardValueConfiguration('constant', [ConstantValueSource::KEY_VALUE => static::DEFAULT_SUBJECT]));
        $schema->addProperty(static::KEY_SUBJECT, $subjectSchema)->setWeight(15);

        $schema->addProperty(static::KEY_FROM, new CustomSchema(ValueSchema::TYPE, ValueSchema::createStandardValueConfiguration('email')))->setWeight(20);
        $schema->addProperty(static::KEY_TO, new CustomSchema(ValueSchema::TYPE, ValueSchema::createStandardValueConfiguration('email')))->setWeight(25);
        $schema->addProperty(static::KEY_REPLY_TO, new CustomSchema(ValueSchema::TYPE, ValueSchema::createStandardValueConfiguration('email')))->setWeight(30);

        $schema->addProperty(static::KEY_ATTACH_UPLOADED_FILES, new BooleanSchema(static::DEFAULT_ATTACH_UPLOADED_FILES));

        $plainTemplate = new CustomSchema(TemplateEngineInterface::TYPE_PLAIN_TEXT);
        $schema->addProperty(static::KEY_PLAIN_TEMPLATE, $plainTemplate);

        $schema->addProperty(static::KEY_USE_HTML, new BooleanSchema(static::DEFAULT_USE_HTML));

        $htmlTemplate = new CustomSchema(TemplateEngineInterface::TYPE_HTML);
        $htmlTemplate->getRenderingDefinition()->addVisibilityConditionByValue('../' . static::KEY_USE_HTML)->addValue(true);
        $schema->addProperty(static::KEY_HTML_TEMPLATE, $htmlTemplate);

        return $schema;
    }
}
