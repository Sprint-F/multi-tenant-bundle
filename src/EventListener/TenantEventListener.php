<?php

namespace SprintF\Bundle\MultiTenant\EventListener;

use SprintF\Bundle\MultiTenant\Context\TenantContextInterface;
use SprintF\Bundle\MultiTenant\Resolver\TenantResolverInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 500)]
class TenantEventListener
{
    public function __construct(
        private readonly TenantResolverInterface $resolver,
        private readonly TenantContextInterface $context,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Работаем только с основным запросом.
        if (!$event->isMainRequest()) {
            return;
        }

        // Определяем арендатора исходя из данных запроса и используя резолвер.
        $request = $event->getRequest();
        $tenant = $this->resolver->resolveTenant($request);

        if (null === $tenant) {
            $this->context->clearTenant();

            return;
        }

        $this->context->setTenant($tenant);
    }
}
