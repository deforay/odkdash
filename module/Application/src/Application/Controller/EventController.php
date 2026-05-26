<?php

namespace Application\Controller;

use Application\Service\CommonService;
use Application\Service\EventService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class EventController extends AbstractActionController
{
    private EventService $eventService;

    public function __construct($eventService)
    {
        $this->eventService = $eventService;
    }

    public function indexAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $payload = $this->eventService->getFeed((array) $request->getPost());
            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $response->setContent(CommonService::jsonEncode($payload));
            return $response;
        }

        return new ViewModel([
            'eventTypes' => $this->eventService->getEventTypes(),
        ]);
    }
}
