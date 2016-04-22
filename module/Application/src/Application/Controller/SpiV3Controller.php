<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
class SpiV3Controller extends AbstractActionController
{

    public function indexAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $result = $odkFormService->getAllSubmissionsDetails($param);
            return $this->getResponse()->setContent(Json::encode($result));
        }
          
        //return new ViewModel(array(
        //                           'allSubmissions' => $allSubmissions,
        //                           ));
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

    public function exportAction()
    {
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $allSubmissions = $odkFormService->getAllSubmissions('DESC');
        
        $viewModel = new ViewModel(array('allSubmissions' => $allSubmissions));
        
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function approveStatusAction(){
        $request = $this->getRequest();                
         if ($request->isPost()) {
            $params = $request->getPost();
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $result= $odkFormService->approveFormStatus($params['id']);
            $viewModel = new ViewModel(array(
                        'result' => $result
                    ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function downloadPdfAction(){
        
        $id = ($this->params()->fromRoute('id'));
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $formData = $odkFormService->getFormData($id);
        
        $viewModel = new ViewModel(array('formData' => $formData));
        
        $viewModel->setTerminal(true);
        return $viewModel;
        
    }
    
    public function editAction()
    {
        $request = $this->getRequest();
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $odkFormService->updateSpiForm($params);
            return $this->redirect()->toRoute("spi-v3-form");
        } else {
            $id = $this->params()->fromRoute('id');
            $result = $odkFormService->getFormData($id);
            
                return new ViewModel(array(
                    'formData' => $result,
                ));
        }
       
    }
}

