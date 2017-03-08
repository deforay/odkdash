<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class SpiV3Controller extends AbstractActionController
{

    public function indexAction(){
        $request = $this->getRequest();
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $testingPointResult=$odkFormService->getAllTestingPointType();
        $levelNamesResult=$odkFormService->getSpiV3FormUniqueLevelNames();
        
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $odkFormService->getAllSubmissionsDetails($param);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        return new ViewModel(array(
            'testingPointResult' => $testingPointResult,
            'levelNamesResult' => $levelNamesResult
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

    public function exportAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
        $params = $request->getPost();
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $allSubmissions = $odkFormService->exportAllSubmissions($params);

        $viewModel = new ViewModel(array('allSubmissions' => $allSubmissions));

        $viewModel->setTerminal(true);
        return $viewModel;
        }
    }

    public function approveStatusAction(){
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

    public function downloadPdfAction(){
        $commonService = $this->getServiceLocator()->get('CommonService');
        $configData = $commonService->getGlobalConfigDetails();
        $request = $this->getRequest();                
        if ($request->isPost()) {
            $params = $request->getPost();
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $result = $odkFormService->getFormData($params['auditId']);
            $viewModel = new ViewModel(array(
                'formData' => $result,
                'configData'=>$configData,
                'tempId' => $params['tempId']
                ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }else{
            $id = ($this->params()->fromRoute('id'));
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $formData = $odkFormService->getFormData($id);
            $viewModel = new ViewModel(array('formData' => $formData,'configData'=>$configData));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function correctiveActionPdfAction(){
        $id = ($this->params()->fromRoute('id'));
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $commonService = $this->getServiceLocator()->get('CommonService');
        $formData = $odkFormService->getFormData($id);
        $configData = $commonService->getGlobalConfigDetails();
        $viewModel = new ViewModel(array('formData' => $formData,'configData'=>$configData));
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
            $perf1 = $odkFormService->getPerformance($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('auditRoundWiseData' => $auditRoundWiseData,'perf1' => $perf1))
                      ->setTerminal(true);
            return $viewModel;
        }
    }

    public function worstPerformanceAction(){
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

    public function manageFacilityAction(){
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

    public function mergeFacilityNameAction(){
        if($this->getRequest()->isPost()){
            $params=$this->getRequest()->getPost();
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $result = $odkFormService->mergeFacilityName($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }

    public function mapAction(){
        return new ViewModel();
    }

    public function deleteAction(){
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        if($this->getRequest()->isPost()){
            $params=$this->getRequest()->getPost();
            $result=$odkFormService->deleteAuditData($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                      ->setTerminal(true);
            return $viewModel;
        }
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
    
    public function testingVolumeDatewiseAction(){
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        if($this->getRequest()->isPost()){
            $params=$this->getRequest()->getPost();
            $testingVolume = $odkFormService->getAllApprovedTestingVolume($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('testingVolume' => $testingVolume))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function viewDataAction(){
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $odkFormService->getViewDataDetails($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $source = '';
        $roundno = '';
        $drange = '';
        $level = '';
        if($this->params()->fromQuery('source')){
          $source = $this->params()->fromQuery('source');
        }
        if($this->params()->fromQuery('level')){
          $level = $this->params()->fromQuery('level');
        }
        if($this->params()->fromQuery('roundno')){
          $roundno = $this->params()->fromQuery('roundno');
        }
        if($this->params()->fromQuery('drange')){
          $drange = $this->params()->fromQuery('drange');
        }
        $testingPointResult=$odkFormService->getAllTestingPointType();
        return new ViewModel(array(
            'source' => $source,
            'roundno' => $roundno,
            'drange' => $drange,
            'level'=>$level,
            'testingPointResult' => $testingPointResult
        ));
    }
    
    public function getTestingPointTypeNamesAction(){
        if($this->getRequest()->isPost()){
            $params=$this->getRequest()->getPost();
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $result = $odkFormService->getTestingPointTypeNamesByType($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    public function downloadSpiderChartAction(){
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $commonService = $this->getServiceLocator()->get('CommonService');
        if($this->getRequest()->isPost()){
            $params=$this->getRequest()->getPost();
            $result=$odkFormService->getAuditRoundWiseDataChart($params);
            $configData = $commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result,'configData'=>$configData))
                      ->setTerminal(true);
            return $viewModel;
        }
    }
    public function exportAsPdfAction()
    {
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $commonService = $this->getServiceLocator()->get('CommonService');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $odkFormService->getAllSubmissionsDetails($param);
            $spiderResult = $odkFormService->getAuditRoundWiseDataChart($param);
            $pieResult = $odkFormService->getPerformancePieChart($param);
            $configData = $commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result,'configData'=>$configData,'argument'=>$param,'spiderResult'=>$spiderResult,'pieResult'=>$pieResult))
                      ->setTerminal(true);
            return $viewModel;
        }
    }
}

