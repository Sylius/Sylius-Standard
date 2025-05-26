<?php
namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThemePreviewFrameSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [ResponseEvent::class => ['onKernelResponse', -100]];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        // only in dev environment
//        if ($this->container->getParameter('kernel.environment') !== 'dev') {
//            return;
//        }
//        if (0 !== strpos($request->getPathInfo(), '/theme/preview')) {
//            return;
//        }

        $response = $event->getResponse();
        // remove blocking headers entirely
        $response->headers->remove('X-Frame-Options');
        $response->headers->remove('Content-Security-Policy');
    }
}
