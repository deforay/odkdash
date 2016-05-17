<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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

    public function approveStatusAction()
    {
        $request = $this->getRequest();                
                 if ($request->isPost()) {
                    $params = $request->getPost();
                    $odkFormService = $this->getServiceLocator()->get('OdkFormService');
                    $result= $odkFormService->approveFormStatus($params);
                    $viewModel = new ViewModel(array(
        'result' => $result
                            ));
                    $viewModel->setTerminal(true);
                    return $viewModel;
                }
    }

    public function downloadPdfAction()
    {
        $id = ($this->params()->fromRoute('id'));
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $formData = $odkFormService->getFormData($id);
        $viewModel = new ViewModel(array('formData' => $formData));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function correctiveActionPdfAction()
    {
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

    public function auditPerformanceAction()
    {
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        if($this->getRequest()->isPost()){
            $params=$this->getRequest()->getPost();
            $auditRoundWiseData=$odkFormService->getAuditRoundWiseData($params);
            $perf1 = $odkFormService->getPerformance();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('auditRoundWiseData' => $auditRoundWiseData,'perf1' => $perf1))
                      ->setTerminal(true);
            return $viewModel;
        }
    }

    public function worstPerformanceAction()
    {
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        if($this->getRequest()->isPost()){
            $params=$this->getRequest()->getPost();
            $zeroCounts = $odkFormService->getZeroQuestionCounts($params);
            $spiV3Labels = $odkFormService->getSpiV3FormLabels();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('zeroCounts' => $zeroCounts,'spiV3Labels' => $spiV3Labels,'limitBar'=>$params['limitBar']))
                        ->setTerminal(true);
            return $viewModel;
        }
    }

    public function auditLocationsAction()
    {
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        if($this->getRequest()->isPost()){
            $params=$this->getRequest()->getPost();
            $allSubmissions = $odkFormService->getAllApprovedSubmissionLocation($params);
            $viewModel = new ViewModel();
                $viewModel->setVariables(array('allSubmissions' => $allSubmissions))
                        ->setTerminal(true);
                return $viewModel;
        }
    }

    public function manageFacilityAction()
    {
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            
            $result = $odkFormService->getAllSubmissionsDatas($param);
            return $this->getResponse()->setContent(Json::encode($result));
        }else{
            $result = $odkFormService->getPendingFacilityNames();
            
                return new ViewModel(array(
                    'pendingFacilityName' => $result,
                ));
        }
    }

    public function mergeFacilityNameAction()
    {
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        if($this->getRequest()->isPost()){
            $params=$this->getRequest()->getPost();
            $result = $odkFormService->mergeFacilityName($params);
            $viewModel = new ViewModel();
                $viewModel->setVariables(array('result' => $result))
                            ->setTerminal(true);
                return $viewModel;
        }
    }

    public function mapAction()
    {
        return new ViewModel();
    }
    
    public function spirtv3DatewiseAction()
    {
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        if($this->getRequest()->isPost()){
            $params=$this->getRequest()->getPost();
            $perflast30 = $odkFormService->getPerformanceLast30Days($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('perflast30' => $perflast30))
                        ->setTerminal(true);
            return $viewModel;
        }
    }


}

