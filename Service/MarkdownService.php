<?php

namespace Andchir\MarkdownBundle\Service;

use App\Event\CategoryUpdatedEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\EventDispatcher\GenericEvent;
use App\Events;
use Psr\Log\LoggerInterface;

class MarkdownService
{
    /** @var ContainerInterface */
    private $container;
    /** @var array */
    protected $config;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ContainerInterface $container,
        LoggerInterface $logger,
        array $config = []
    )
    {
        $this->container = $container;
        $this->config = $config;
        $this->logger = $logger;
    }


}
