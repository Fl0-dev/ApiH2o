<?php

namespace App\EventListener;

use App\Service\Something;
use Doctrine\Common\Annotations\Reader;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SomethingAnnotationSubscriber implements EventSubscriberInterface
{

    private Reader $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function onControllerEvent(ControllerEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }

        $this->handleAnnotation($controller[0]);
    }

    private function handleAnnotation ($controller)
    {
        $reflectionController = new \ReflectionClass($controller);

        //on pourrait utiliser getMethodAnnotations() si on voulait attaquer sur une route
        $annotation = $this->annotationReader->getClassAnnotation($reflectionController, Something::class);

        if ($annotation instanceof Something) {
            var_dump($annotation);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onControllerEvent',
        ];
    }
}