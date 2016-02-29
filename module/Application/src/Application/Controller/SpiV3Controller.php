<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SpiV3Controller extends AbstractActionController
{

    public function indexAction()
    {
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $allSubmissions = $odkFormService->getAllSubmissions();        
        return new ViewModel(array(
                                   'allSubmissions' => $allSubmissions,
                                   ));
    }

    public function printAction()
    {
        
        $id = ($this->params()->fromRoute('id'));
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $formData = $odkFormService->getFormData($id);
        
        $viewModel = new ViewModel(array('formData' => $formData));
        
        $viewModel->setTerminal(true);
        return $viewModel;
    }


}

