<?php

namespace DigitalMarketingFramework\Distributor\Mail\DataDispatcher;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareInterface;
use DigitalMarketingFramework\Core\FileStorage\FileStorageAwareTrait;
use DigitalMarketingFramework\Core\Model\Data\Value\FileValueInterface;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\TemplateEngine\TemplateEngineAwareInterface;
use DigitalMarketingFramework\Core\TemplateEngine\TemplateEngineAwareTrait;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcher;
use DigitalMarketingFramework\Distributor\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Mail\Manager\MailManagerInterface;
use DigitalMarketingFramework\Mail\Utility\MailUtility;
use Exception;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;

class MailDataDispatcher extends DataDispatcher implements TemplateEngineAwareInterface, FileStorageAwareInterface
{
    use TemplateEngineAwareTrait;
    use FileStorageAwareTrait;

    protected bool $attachUploadedFiles = false;

    protected string|ValueInterface $from = '';

    protected string|ValueInterface $to = '';

    protected string|ValueInterface|null $replyTo = '';

    protected string|ValueInterface $subject = '';

    /** @var array<string,mixed> */
    protected array $plainTemplateConfig = [];

    /** @var array<string,mixed> */
    protected array $htmlTemplateConfig = [];

    protected bool $useHtml;

    public function __construct(
        string $keyword,
        RegistryInterface $registry,
        protected MailManagerInterface $mailManager,
    ) {
        parent::__construct($keyword, $registry);
    }

    /**
     * @param array<string|ValueInterface> $data
     */
    protected function processData(Email &$message, array $data): void
    {
        try {
            $from = MailUtility::getAddressData($this->from, true);
            foreach ($from as $value) {
                $message->addFrom($value);
            }

            $to = MailUtility::getAddressData($this->to);
            foreach ($to as $value) {
                $message->addTo($value);
            }

            if ($this->replyTo) {
                $replyTo = MailUtility::getAddressData($this->replyTo, true);
                foreach ($replyTo as $value) {
                    $message->addReplyTo($value);
                }
            }

            $contentData = $this->attachUploadedFiles ? $this->getAllButUploadFields($data) : $data;
            $plainBody = $this->getPlainBody($contentData);
            $htmlBody = $this->getHtmlBody($contentData);

            if ($plainBody === '' && $htmlBody === '') {
                throw new DigitalMarketingFrameworkException('email body seems to be empty');
            }

            if ($htmlBody !== '') {
                $message->html($htmlBody);
            }

            if ($plainBody !== '') {
                $message->text($plainBody);
            }

            $subject = MailUtility::sanitizeHeaderString($this->subject);
            if ($subject === '') {
                $this->logger->warning('Dirty mail header found: "' . $this->subject . '"');
            }

            $message->subject($subject);
        } catch (RfcComplianceException $e) {
            throw new DigitalMarketingFrameworkException($e->getMessage());
        }
    }

    /**
     * @param array<string,string|ValueInterface> $data
     */
    protected function processAttachments(Email &$message, array $data): void
    {
        $uploadFields = $this->getUploadFields($data);
        foreach ($uploadFields as $uploadField) {
            $fileContents = $this->fileStorage->getFileContents($uploadField->getRelativePath());
            $message->attach(
                $fileContents,
                $uploadField->getFileName(),
                $uploadField->getMimeType()
            );
        }
    }

    /**
     * @param array<string,string|ValueInterface> $data
     */
    public function send(array $data): void
    {
        try {
            $message = $this->mailManager->createMessage();
            $this->processData($message, $data);
            if ($this->attachUploadedFiles) {
                $this->processAttachments($message, $data);
            }

            $this->mailManager->sendMessage($message);
        } catch (Exception $e) {
            throw new DigitalMarketingFrameworkException($e->getMessage());
        }
    }

    protected function getPreviewData(array $data): array
    {
        $previewData = parent::getPreviewData($data);

        try {
            $message = $this->mailManager->createMessage();
            $this->processData($message, $data);

            $previewData['config']['Subject'] = $message->getSubject();

            $previewData['config']['From'] = implode(', ', array_map(static function (Address $address) {
                return $address->toString();
            }, $message->getFrom()));

            $previewData['config']['To'] = implode(', ', array_map(static function (Address $address) {
                return $address->toString();
            }, $message->getTo()));

            $previewData['config']['Reply to'] = implode(', ', array_map(static function (Address $address) {
                return $address->toString();
            }, $message->getReplyTo()));

            $previewData['config']['Attach files'] = $this->attachUploadedFiles ? 'yes' : 'no';

            $previewData['plainText'] = $message->getTextBody();

            $previewData['htmlText'] = $message->getHtmlBody();
        } catch (Exception $e) {
            throw new DigitalMarketingFrameworkException($e->getMessage());
        }

        return $previewData;
    }

    public function getAttachUploadedFiles(): bool
    {
        return $this->attachUploadedFiles;
    }

    public function setAttachUploadedFiles(bool $attachUploadedFiles): void
    {
        $this->attachUploadedFiles = $attachUploadedFiles;
    }

    public function getFrom(): string|ValueInterface
    {
        return $this->from;
    }

    public function setFrom(string|ValueInterface $from): void
    {
        $this->from = $from;
    }

    public function getTo(): string|ValueInterface
    {
        return $this->to;
    }

    public function setTo(string|ValueInterface $to): void
    {
        $this->to = $to;
    }

    public function getReplyTo(): string|ValueInterface|null
    {
        return $this->replyTo;
    }

    public function setReplyTo(string|ValueInterface|null $replyTo): void
    {
        $this->replyTo = $replyTo;
    }

    public function getSubject(): string|ValueInterface
    {
        return $this->subject;
    }

    public function setSubject(string|ValueInterface $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return array<string,mixed>
     */
    public function getPlainTemplateConfig(): array
    {
        return $this->plainTemplateConfig;
    }

    /**
     * @param array<string,mixed> $plainTemplateConfig
     */
    public function setPlainTemplateConfig(array $plainTemplateConfig): void
    {
        $this->plainTemplateConfig = $plainTemplateConfig;
    }

    /**
     * @return array<string,mixed>
     */
    public function getHtmlTemplateConfig(): array
    {
        return $this->htmlTemplateConfig;
    }

    /**
     * @param array<string,mixed> $htmlTemplateConfig
     */
    public function setHtmlTemplateConfig(array $htmlTemplateConfig): void
    {
        $this->htmlTemplateConfig = $htmlTemplateConfig;
    }

    public function getUseHtml(): bool
    {
        return $this->useHtml;
    }

    public function setUseHtml(bool $useHtml): void
    {
        $this->useHtml = $useHtml;
    }

    /**
     * @param array<string,string|ValueInterface> $data
     *
     * @return array<FileValueInterface>
     */
    public function getUploadFields(array $data): array
    {
        return array_filter($data, static function ($value) {
            return $value instanceof FileValueInterface;
        });
    }

    /**
     * @param array<string,string|ValueInterface> $data
     *
     * @return array<string,string|ValueInterface>
     */
    public function getAllButUploadFields(array $data): array
    {
        return array_filter($data, static function ($value) {
            return !$value instanceof FileValueInterface;
        });
    }

    /**
     * @param array<string,string|ValueInterface> $data
     */
    protected function getPlainBody(array $data): string
    {
        return $this->templateEngine->render($this->plainTemplateConfig, $data);
    }

    /**
     * @param array<string,string|ValueInterface> $data
     */
    protected function getHtmlBody(array $data): string
    {
        if (!$this->useHtml) {
            return '';
        }

        return $this->templateEngine->render($this->htmlTemplateConfig, $data);
    }
}
