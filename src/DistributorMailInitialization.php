<?php

namespace DigitalMarketingFramework\Distributor\Mail;

use DigitalMarketingFramework\Core\DataProcessor\ValueSource\ValueSourceInterface;
use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Core\Registry\RegistryInterface;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Route\OutboundRouteInterface;
use DigitalMarketingFramework\Distributor\Mail\DataDispatcher\MailDataDispatcher;
use DigitalMarketingFramework\Distributor\Mail\DataProcessor\ValueSource\EmailValueSource;
use DigitalMarketingFramework\Distributor\Mail\Manager\DefaultMailManager;
use DigitalMarketingFramework\Distributor\Mail\Manager\MailManagerInterface;
use DigitalMarketingFramework\Distributor\Mail\Route\MailOutboundRoute;

class DistributorMailInitialization extends Initialization
{
    protected const PLUGINS = [
        RegistryDomain::CORE => [
            ValueSourceInterface::class => [
                EmailValueSource::class,
            ],
        ],
        RegistryDomain::DISTRIBUTOR => [
            DataDispatcherInterface::class => [
                MailDataDispatcher::class,
            ],
            OutboundRouteInterface::class => [
                MailOutboundRoute::class,
            ],
        ],
    ];

    protected const SCHEMA_MIGRATIONS = [];

    public function __construct(
        protected ?MailManagerInterface $mailManager = null,
        string $packageAlias = ''
    ) {
        parent::__construct('distributor-mail', '1.0.0', $packageAlias);
    }

    protected function getAdditionalPluginArguments(string $interface, string $pluginClass, RegistryInterface $registry): array
    {
        if ($pluginClass === MailDataDispatcher::class) {
            return [$this->mailManager ?? $registry->createObject(DefaultMailManager::class)];
        }

        return parent::getAdditionalPluginArguments($interface, $pluginClass, $registry);
    }
}
