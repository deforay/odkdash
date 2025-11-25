<?php

namespace Application\Controller;

use Laminas\Config\Config;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class EventController extends AbstractActionController
{
    private $eventService = null;

    public function __construct($eventService)
    {
        $this->eventService = $eventService;
    }

    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */

        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->eventService->getAllDetails($params);
            return $this->getResponse()->setContent(CommonService::jsonEncode($result));
        }
    }
}
