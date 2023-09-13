<?php

namespace DigitalMarketingFramework\Distributor\Mail;

use DigitalMarketingFramework\Core\Initialization;
use DigitalMarketingFramework\Core\Registry\RegistryDomain;
use DigitalMarketingFramework\Distributor\Core\DataDispatcher\DataDispatcherInterface;
use DigitalMarketingFramework\Distributor\Core\Route\RouteInterface;
use DigitalMarketingFramework\Distributor\Mail\DataDispatcher\MailDataDispatcher;
use DigitalMarketingFramework\Distributor\Mail\Route\MailRoute;

class DistributorMailInitialization extends Initialization
{
    protected const PLUGINS = [
        RegistryDomain::DISTRIBUTOR => [
            DataDispatcherInterface::class => [
                MailDataDispatcher::class,
            ],
            RouteInterface::class => [
                MailRoute::class,
            ],
        ],
    ];

    protected const SCHEMA_MIGRATIONS = [];

    public function __construct()
    {
        parent::__construct('distributor-mail', '1.0.0');
    }
}
