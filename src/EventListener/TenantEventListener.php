<?php

namespace SprintF\Bundle\MultiTenant\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use SprintF\Bundle\MultiTenant\Context\TenantContextInterface;
use SprintF\Bundle\MultiTenant\Resolver\TenantResolverInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Слушатель события kernel.request.
 *
 * Определяет текущего арендатора, используя резовлер, устанавливает контекст аренды, активирует фильтр запросов.
 */
#[AsEventListener(event: KernelEvents::REQUEST, priority: 500)]
class TenantEventListener
{
    public function __construct(
        private readonly TenantResolverInterface $resolver,
        private readonly TenantContextInterface $context,
        private readonly ?EntityManagerInterface $em = null,
        private readonly string $tenantFieldName,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        // Работаем только с основным запросом.
        if (!$event->isMainRequest()) {
            return;
        }

        // Включаем фильтр Doctrine
        $this->em?->getFilters()->enable('tenant_filter');
        $this->em?->getFilters()->getFilter('tenant_filter')->setParameter('tenant_field', $this->tenantFieldName);

        // Определяем арендатора исходя из данных запроса и используя резолвер.
        $request = $event->getRequest();
        $tenant = $this->resolver->resolveTenant($request);

        // Если не удалось определить арендатора, то...
        if (null === $tenant) {
            // ...устанавливаем в контексте арендатора в null
            $this->context->clearTenant();
            // ...и передаем эту информацию в фильтр через его параметр
            $this->em?->getFilters()->getFilter('tenant_filter')->setParameter('tenant_id', null);

            return;
        }

        // Сохраняем ссылку на арендатора в контексте
        $this->context->setTenant($tenant);
        // И передаем в параметры фильтра Doctrine
        $this->em?->getFilters()->getFilter('tenant_filter')->setParameter('tenant_id', $tenant->getId());
    }
}
