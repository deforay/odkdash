<?php

namespace Application\Controller;

use Laminas\Config\Config;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class AuditTrailController extends AbstractActionController
{


    private $auditTrailService = null;

    public function __construct($auditTrailService)
    {
        $this->auditTrailService = $auditTrailService;
    }


    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
                if(!empty($params['version']) && $params['version']=='v3')
                $result = $this->auditTrailService->getSpiV3Details($params);
                    else
                $result = $this->auditTrailService->getSpiV6Details($params);
            //return $this->getResponse()->setContent(Json::encode($result));
            return new ViewModel(array(
                'result' => $result,
                'params' => $params,
            ));
        }

    }
}
