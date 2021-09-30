<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Json\Json;

class SpiV6Controller extends AbstractActionController
{


    private $commonService = null;
    private $odkFormService = null;

    public function __construct($odkFormService, $commonService)
    {
        $this->odkFormService = $odkFormService;
        $this->commonService = $commonService;
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $testingPointResult = $this->odkFormService->getAllSpiV6TestingPointType();
        //echo "ww";die;
        $levelNamesResult = $this->odkFormService->getSpiV3FormUniqueLevelNames();
        //var_dump($levelNamesResult);die;
        if ($request->isPost()) {
            
            $param = $request->getPost();
            $result = $this->odkFormService->getAllSpiV6SubmissionsDetails($param);
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
        $formData = $this->odkFormService->getFormData($id);

        $viewModel = new ViewModel(array('formData' => $formData));

        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function exportAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $allSubmissions = $this->odkFormService->exportAllV6Submissions($params);
            // print_r($allSubmissions);die;
            $viewModel = new ViewModel(array('allSubmissions' => $allSubmissions));

            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function exportSAndDAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $allSubmissions = $this->odkFormService->exportSAndDV6Submissions($params);
            // print_r($allSubmissions);die;
            $viewModel = new ViewModel(array('allSubmissions' => $allSubmissions));

            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function approveStatusAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->approveSpiV6FormStatus($params);
            $viewModel = new ViewModel(array(
                'result' => $result
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function downloadPdfAction()
    {
        
        $configData = $this->commonService->getGlobalConfigDetails();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getSpiV6FormData($params['auditId']);
            $viewModel = new ViewModel(array(
                'formData' => $result,
                'configData' => $configData,
                'tempId' => $params['tempId']
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        } else {
            $id = ($this->params()->fromRoute('id'));
            $formData = $this->odkFormService->getSpiV6FormData($id,'yes');
            //echo "<pre>";
            //print_r($formData);die;
            $viewModel = new ViewModel(array('formData' => $formData, 'configData' => $configData));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function correctiveActionPdfAction()
    {
        $id = ($this->params()->fromRoute('id'));
        $formData = $this->odkFormService->getFormData($id,'yes');
        $configData = $this->commonService->getGlobalConfigDetails();
        $viewModel = new ViewModel(array('formData' => $formData, 'configData' => $configData));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function editAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $this->odkFormService->updateSpiV6Form($params);
            return $this->redirect()->toUrl("/spi-v6/manage-facility");
        } else {
            $id = $this->params()->fromRoute('id');
            $facilitiesResult = $this->odkFormService->getAllFacilityNames();
            $result = $this->odkFormService->getSpiV6FormData($id);
            $provinceList = $this->odkFormService->getSpiV6FormUniqueLevelNames();
            $districtList = $this->odkFormService->getSpiV3FormUniqueDistrict();
            return new ViewModel(array(
                'formData' => $result,
                'facilities' => $facilitiesResult,
                'provinceList' => $provinceList,
                'districtList' => $districtList
            ));
        }
    }

    public function auditPerformanceAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $auditRoundWiseData = $this->odkFormService->getAuditRoundWiseDataV6($params);
            $perf1 = $this->odkFormService->getPerformanceV6($params);
            //var_dump($perf1);die;
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('auditRoundWiseData' => $auditRoundWiseData, 'perf1' => $perf1))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function auditPerformanceSectionZeroAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $auditRoundWiseData = $this->odkFormService->getAuditRoundWiseSectionS0DataV6($params);
            //var_dump('ss');die;
            $perf1 = $this->odkFormService->getPerformanceV6($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('auditRoundWiseData' => $auditRoundWiseData, 'perf1' => $perf1))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function auditPerformanceSectionZeroProtocolAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $auditRoundWiseData = $this->odkFormService->getAuditRoundWiseSectionD0DataV6($params);
            //var_dump('ss');die;
            $perf1 = $this->odkFormService->getPerformanceV6($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('auditRoundWiseData' => $auditRoundWiseData, 'perf1' => $perf1))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function worstPerformanceAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $zeroCounts = $this->odkFormService->getZeroQuestionCountsV6($params);
            $spiV3Labels = $this->odkFormService->getSpiV6FormLabels();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('zeroCounts' => $zeroCounts, 'spiV3Labels' => $spiV3Labels, 'limitBar' => $params['limitBar']))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function auditLocationsAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $allSubmissions = $this->odkFormService->getAllApprovedSubmissionLocationV6($params);
            $configData = $this->commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('allSubmissions' => $allSubmissions,'configData'=>$configData))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function manageFacilityAction()
    {
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $this->odkFormService->getAllSubmissionsDatasV6($param);
            return $this->getResponse()->setContent(Json::encode($result));
        } else {
            $result = $this->odkFormService->getPendingFacilityNamesV6();

            return new ViewModel(array(
                'pendingFacilityName' => $result,
            ));
        }
    }

    public function mergeFacilityNameAction()
    {
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            
            $result = $this->odkFormService->mergeFacilityName($params);
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

    public function deleteAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $result = $this->odkFormService->deleteAuditDataV6($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function spirtv6DatewiseAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $perflast30 = $this->odkFormService->getPerformanceLast30DaysV6($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('perflast30' => $perflast30))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function testingVolumeDatewiseAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $testingVolume = $this->odkFormService->getAllApprovedTestingVolumeV6($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('testingVolume' => $testingVolume))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function viewDataV6Action()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getViewDataDetailsV6($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $source = '';
        $roundno = '';
        $drange = '';
        $level = '';
        if ($this->params()->fromQuery('source')) {
            $source = $this->params()->fromQuery('source');
        }
        if ($this->params()->fromQuery('level')) {
            $level = $this->params()->fromQuery('level');
        }
        if ($this->params()->fromQuery('roundno')) {
            $roundno = $this->params()->fromQuery('roundno');
        }
        if ($this->params()->fromQuery('drange')) {
            $drange = $this->params()->fromQuery('drange');
        }
        $testingPointResult = $this->odkFormService->getAllTestingPointTypeV6();
        $levelNamesResult = $this->odkFormService->getSpiV6FormUniqueLevelNames();
        return new ViewModel(array(
            'source' => $source,
            'roundno' => $roundno,
            'drange' => $drange,
            'level' => $level,
            'testingPointResult' => $testingPointResult,
            'levelNamesResult' => $levelNamesResult
        ));
    }

    

    public function viewDataSectionZeroV6Action()
    {
       // echo "ss";die;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getViewDataS0DetailsV6($params);
            //var_dump($result);die;
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $source = '';
        $roundno = '';
        $drange = '';
        $level = '';
        if ($this->params()->fromQuery('source')) {
            $source = $this->params()->fromQuery('source');
        }
        if ($this->params()->fromQuery('level')) {
            $level = $this->params()->fromQuery('level');
        }
        if ($this->params()->fromQuery('roundno')) {
            $roundno = $this->params()->fromQuery('roundno');
        }
        if ($this->params()->fromQuery('drange')) {
            $drange = $this->params()->fromQuery('drange');
        }
        $testingPointResult = $this->odkFormService->getAllTestingPointTypeV6();
        $levelNamesResult = $this->odkFormService->getSpiV6FormUniqueLevelNames();
        return new ViewModel(array(
            'source' => $source,
            'roundno' => $roundno,
            'drange' => $drange,
            'level' => $level,
            'testingPointResult' => $testingPointResult,
            'levelNamesResult' => $levelNamesResult
        ));
    }

    public function viewDataSectionZeroProtocolV6Action()
    {
       //echo "ss";die;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getViewDataD0DetailsV6($params);
            //var_dump($result);die;
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $source = '';
        $roundno = '';
        $drange = '';
        $level = '';
        if ($this->params()->fromQuery('source')) {
            $source = $this->params()->fromQuery('source');
        }
        if ($this->params()->fromQuery('level')) {
            $level = $this->params()->fromQuery('level');
        }
        if ($this->params()->fromQuery('roundno')) {
            $roundno = $this->params()->fromQuery('roundno');
        }
        if ($this->params()->fromQuery('drange')) {
            $drange = $this->params()->fromQuery('drange');
        }
        $testingPointResult = $this->odkFormService->getAllTestingPointTypeV6();
        $levelNamesResult = $this->odkFormService->getSpiV6FormUniqueLevelNames();
        return new ViewModel(array(
            'source' => $source,
            'roundno' => $roundno,
            'drange' => $drange,
            'level' => $level,
            'testingPointResult' => $testingPointResult,
            'levelNamesResult' => $levelNamesResult
        ));
    }

    

    public function getTestingPointTypeNamesAction()
    {
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            
            $result = $this->odkFormService->getTestingPointTypeNamesByTypeV6($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function downloadSpiderChartAction()
    {
        
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $result = $this->odkFormService->getAuditRoundWiseDataChartV6($params);
            $configData = $this->commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result, 'configData' => $configData))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function downloadSectionZeroSpiderChartAction()
    {   
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $result = $this->odkFormService->getAuditRoundWiseS0DataChartV6($params);
            $configData = $this->commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result, 'configData' => $configData))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function downloadSectionZeroProtocolSpiderChartAction()
    {   
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $result = $this->odkFormService->getAuditRoundWiseD0DataChartV6($params);
            $configData = $this->commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result, 'configData' => $configData))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function exportAsPdfAction()
    {
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $this->odkFormService->getAllSpiV6SubmissionsDetails($param);
            
           
            $configData = $this->commonService->getGlobalConfigDetails();
            
            $pieResult = $this->odkFormService->getSpiV6PerformancePieChart($param);
            $spiderResult = $this->odkFormService->getSpiV6AuditRoundWiseDataChart($param);
            $s0Result = $this->odkFormService->getSpiV6AuditRoundWiseS0DataChart($param);
            $d0Result = $this->odkFormService->getSpiV6AuditRoundWiseD0DataChart($param);
           // var_dump($spiderResult);die;
            //echo "<pre>";
            //print_r(array('result' => $result, 'configData' => $configData, 'argument' => $param, 'spiderResult' => $spiderResult, 'pieResult' => $pieResult));
            //die;
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result, 'configData' => $configData, 'argument' => $param, 'spiderResult' => $spiderResult, 'pieResult' => $pieResult,'s0Result' => $s0Result,'d0Result' => $d0Result))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function duplicateAction()
    {
        $request = $this->getRequest();
        $result = $this->odkFormService->getAllV6DuplicateSubmissionsDetails();
        return new ViewModel(array('result' => $result));
    }

    public function removeAuditAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $result = $this->odkFormService->removeAuditV6($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function saveDownloadDataAction()
    {
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->addV6DownloadData($params);
           // var_dump($result);die;
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function downloadFilesAction()
    {
        $request = $this->getRequest();
        
        $result = $this->odkFormService->getV6DownloadFilesRow();
        
        return new ViewModel(array('result' => $result));
    }

    public function getDistrictByProvinceAction()
    {
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getDistrictData($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result, 'params' => $params))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function validateSpiv3DataAction()
    {
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $this->odkFormService->validateSPIV3File($param);
            return $this->redirect()->toUrl("/spi-v3/validate-spiv3-data");
        } else {
            $tokenResults = $this->odkFormService->getSpiV3FormUniqueTokens();
            return new ViewModel(array('tokenResults' => $tokenResults));
        }
    }
    public function validateSpiv3DetailsAction()
    {
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $this->odkFormService->getAllValidateSpiv3Details($param);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    public function addSpiv3ValidateDataAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $result = $this->odkFormService->addValidateSpiv3Data($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}
