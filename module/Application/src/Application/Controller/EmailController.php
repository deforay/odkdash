<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class EmailController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $commonService = $this->getServiceLocator()->get('CommonService');
            $commonService->addEmail($params);
        }
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $result = $odkFormService->getAllFacilityNames();
        return new ViewModel(array(
            'facilityName' => $result
        ));
    }
    
    public function getFacilitiesAuditsAction() {
        $request = $this->getRequest();                
        if ($request->isPost()) {
            $params = $request->getPost();
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $result= $odkFormService->getFacilitiesAudits($params);
            $viewModel = new ViewModel(array(
                    'result' => $result
                ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}