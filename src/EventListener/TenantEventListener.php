<?php

namespace SprintF\Bundle\MultiTenant\EventListener;

use Doctrine\ORM\EntityManagerInterface;
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
        private readonly ?EntityManagerInterface $em = null,
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

        // Если не удалось определить арендатора, то...
        if (null === $tenant) {
            // ...устанавливаем в контексте арендатора в null
            $this->context->clearTenant();
            // ...выключаем фильтр для запросов Doctrine
            if ($this->em?->getFilters()->isEnabled('tenant_filter')) {
                $this->em?->getFilters()->disable('tenant_filter');
            }

            return;
        }

        // Сохраняем ссылку на арендатора в контексте
        $this->context->setTenant($tenant);
        // Включаем фильтр Doctrine
        $this->em?->getFilters()->enable('tenant_filter');
        $this->em?->getFilters()->getFilter('tenant_filter')->setParameter('tenant_id', $tenant->getId());
    }
}
