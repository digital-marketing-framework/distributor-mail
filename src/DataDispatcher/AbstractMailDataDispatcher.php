<?php

namespace DigitalMarketingFramework\Distributor\Mail\DataDispatcher;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use DigitalMarketingFramework\Core\Log\LoggerAwareTrait;
use DigitalMarketingFramework\Core\Model\Data\Value\FileValue;
use DigitalMarketingFramework\Core\Model\Data\Value\MultiValue;
use DigitalMarketingFramework\Core\Model\Data\Value\ValueInterface;
use DigitalMarketingFramework\Core\Utility\GeneralUtility;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcher;
use DigitalMarketingFramework\Distributor\Mail\Manager\DefaultMailManager;
use DigitalMarketingFramework\Distributor\Mail\Manager\MailManagerInterface;
use DigitalMarketingFramework\Distributor\Mail\Model\Data\Value\EmailValue;
use DigitalMarketingFramework\Distributor\Mail\Utility\MailUtility;
use DigitalMarketingFramework\Typo3\Distributor\Core\Registry\Registry;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;

abstract class AbstractMailDataDispatcher extends DataDispatcher
{
    use LoggerAwareTrait;

    protected MailManagerInterface $mailManager;

    protected bool $attachUploadedFiles = false;
    protected string $from = '';
    protected string $to = '';
    protected string $replyTo = '';
    protected string $subject = '';

    /** @phpstan-ignore-next-line (TODO: @Ude is $registry still necessary) */
    public function __construct(
        string $keyword,
        Registry $registry,
        MailManagerInterface $mailManager = null
    ) {
        parent::__construct($keyword);
        $this->mailManager = $mailManager ?? new DefaultMailManager();
    }

    /**
     * Checks string for suspicious characters
     *
     * @param string $string String to check
     * @return string Valid or empty string
     */
    protected function sanitizeHeaderString(string $string): string
    {
        $pattern = '/[\\r\\n\\f\\e]/';
        if (preg_match($pattern, $string) > 0) {
            $this->logger->warning('Dirty mail header found: "' . $string . '"');
            $string = '';
        }
        return $string;
    }

    /**
     * @param Email $message
     * @param array<string|ValueInterface> $data
     */
    protected function processData(Email &$message, array $data): void
    {
        try {
            $from = $this->getAddressData($this->from, true);
            foreach ($from as $key => $value) {
                $message->addFrom($value);
            }

            $to = $this->getAddressData($this->to);
            foreach ($to as $key => $value) {
                $message->addTo($value);
            }

            if ($this->replyTo) {
                $replyTo = $this->getAddressData($this->replyTo, true);
                foreach ($replyTo as $key => $value) {
                    $message->addReplyTo($value);
                }
            }
            $plainBody = $this->getPlainBody($data);
            $htmlBody = $this->getHtmlBody($data);
            if ($htmlBody) {
                $message->html($htmlBody);
                if ($plainBody) {
                    $message->text($plainBody);
                }
            } elseif ($plainBody) {
                $message->text($plainBody);
            }
            $subject = $this->subject;
            $message->subject($this->sanitizeHeaderString($subject));
        } catch (RfcComplianceException $e) {
            throw new DigitalMarketingFrameworkException($e->getMessage());
        }
    }

    /**
     * @param Email $message
     * @param array<mixed> $data
     */
    protected function processAttachments(Email &$message, array $data): void
    {
        $uploadFields = $this->getUploadFields($data);
        if (!empty($uploadFields)) {
            /** @var FileValue $uploadField */
            foreach ($uploadFields as $uploadField) {
                $message->attachFromPath(
                    $uploadField->getPublicUrl(),
                    $uploadField->getFileName(),
                    $uploadField->getMimeType()
                );
            }
        }
    }

    /**
     *
     * @param array<string|ValueInterface> $data
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
        } catch (\Exception $e) {
            throw new DigitalMarketingFrameworkException($e->getMessage());
        }
    }

    /**
     * getAddressData
     *
     * Input examples:
     * 'address@domain.tld'
     * 'Some Name <address@domain.tld>'
     * 'address@domain.tld, address-2@domain.tld'
     * 'Some Name <address@domain.tld>, address-2@domain.tld, Some Other Name <address-3@domain.tld>'
     * ['address@domain.tld', 'Some Name <address@domain.tld>']
     * MultiValueField(['address@domain.tld', 'Some Name <address@domain.tld>'])
     * EmailValue()
     * [EmailValue(), 'address@domain.tld']
     * MultiValue([EmailValue(), 'address@domain.tld'])
     *
     * @param string|array<string>|MultiValue|EmailValue $addresses
     * @param bool $onlyOneAddress
     * @return array<string>
     */
    protected function getAddressData($addresses, $onlyOneAddress = false)
    {
        if ($addresses instanceof EmailValue) {
            $addresses = [$addresses];
        } elseif ($onlyOneAddress) {
            $addresses = [$addresses];
        } else {
            $addresses = GeneralUtility::castValueToArray($addresses);
        }
        $addresses = array_filter($addresses);

        $result = [];
        foreach ($addresses as $address) {
            $name = '';
            $email = '';
            if ($address instanceof EmailValue) {
                $name = $address->getName();
                $email = $address->getAddress();
            } elseif (preg_match('/^([^<]+)<([^>]+)>$/', $address, $matches)) {
                // Some Name <some-address@domain.tld>
                $name = $matches[1];
                $email = $matches[2];
            } else {
                $email = $address;
            }

            if ($name) {
                $result[trim($email)] = MailUtility::encode($name);
            } else {
                $result[] = trim($email);
            }
        }
        return $result;
    }

    public function getAttachUploadedFiles(): bool
    {
        return $this->attachUploadedFiles;
    }

    public function setAttachUploadedFiles(bool $attachUploadedFiles): void
    {
        $this->attachUploadedFiles = $attachUploadedFiles;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function setTo(string $to): void
    {
        $this->to = $to;
    }

    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    public function setReplyTo(string $replyTo): void
    {
        $this->replyTo = $replyTo;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @param array<mixed> $data
     * @return array<Filevalue>
     */
    public function getUploadFields(array $data): array
    {
        return array_filter($data, function ($a) { return $a instanceof FileValue; });
    }

    /**
     * @param array<mixed> $data
     */
    abstract protected function getPlainBody(array $data): string;

    /**
     * @param array<mixed> $data
     */
    abstract protected function getHtmlBody(array $data): string;
}