<?php

namespace DigitalMarketingFramework\Distributor\Mail\Manager;

use DigitalMarketingFramework\Core\Exception\DigitalMarketingFrameworkException;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;

class DefaultMailManager implements MailManagerInterface
{
    public const TRANSPORT_TYPE = 'type';

    public const TRANSPORT_TYPE_SENDMAIL = 'sendmail';

    public const TRANSPORT_TYPE_SMTP = 'smtp';

    public const TRANSPORT_CONFIG = 'config';

    public const TRANSPORT_CONFIG_SENDMAIL_CMD = 'cmd';

    public const TRANSPORT_CONFIG_SMTP_DOMAIN = 'domain';

    public const TRANSPORT_CONFIG_SMTP_PORT = 'port';

    public const TRANSPORT_CONFIG_SMTP_USERNAME = 'username';

    public const TRANSPORT_CONFIG_SMTP_PASSWORD = 'password';

    /**
     * @var array<string, array<string, string>|string>
     */
    protected $transportConfiguration = [
        self::TRANSPORT_TYPE => self::TRANSPORT_TYPE_SENDMAIL,
        self::TRANSPORT_CONFIG => [
            self::TRANSPORT_CONFIG_SENDMAIL_CMD => '/usr/sbin/sendmail -t',
        ],
    ];

    /**
     * @return array<string, array<string, string>|string>
     */
    public function getTransportConfiguration(): array
    {
        return $this->transportConfiguration;
    }

    /**
     * @param array<string, array<string, string>|string> $transportConfiguration
     */
    public function setTransportConfiguration(array $transportConfiguration): void
    {
        $this->transportConfiguration = $transportConfiguration;
    }

    public function getTransport(): TransportInterface|EsmtpTransport|SendmailTransport|SmtpTransport|null
    {
        $transport = null;
        $config = $this->transportConfiguration[static::TRANSPORT_CONFIG];
        switch ($this->transportConfiguration[static::TRANSPORT_TYPE]) {
            case static::TRANSPORT_TYPE_SENDMAIL:
                $dsn = 'sendmail://default';
                $dsn .= ($config[static::TRANSPORT_CONFIG_SENDMAIL_CMD] !== '') ? '?command=' . $config[static::TRANSPORT_CONFIG_SENDMAIL_CMD] : '';
                $transport = Transport::fromDsn($dsn);
                break;
            case static::TRANSPORT_TYPE_SMTP:
                $dsn = 'smtp://';
                if (isset($config[static::TRANSPORT_CONFIG_SMTP_USERNAME]) && isset($config[static::TRANSPORT_CONFIG_SMTP_PASSWORD])) {
                    $dsn .= urlencode($config[static::TRANSPORT_CONFIG_SMTP_USERNAME]) . ':' . urlencode($config[static::TRANSPORT_CONFIG_SMTP_PASSWORD]) . '@';
                }

                $dsn .= $config[static::TRANSPORT_CONFIG_SMTP_DOMAIN] . ':' . $config[static::TRANSPORT_CONFIG_SMTP_PORT];
                $transport = Transport::fromDsn($dsn);
                break;
        }

        return $transport;
    }

    public function getMailer(): Mailer
    {
        $transport = $this->getTransport();

        return new Mailer($transport);
    }

    public function createMessage(): Email
    {
        /** @var Email $message */
        $message = new Email();

        return $message;
    }

    public function sendMessage(Email $message): void
    {
        try {
            $this->getMailer()->send($message);
        } catch (RfcComplianceException $e) {
            throw new DigitalMarketingFrameworkException($e->getMessage());
        }
    }
}
