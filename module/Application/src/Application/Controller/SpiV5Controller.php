<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Json\Json;

class SpiV5Controller extends AbstractActionController
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
        $testingPointResult = $this->odkFormService->getAllSpiV5TestingPointType();
        $levelNamesResult = $this->odkFormService->getSpiV3FormUniqueLevelNames();

        if ($request->isPost()) {
            
            $param = $request->getPost();
            $result = $this->odkFormService->getAllSpiV5SubmissionsDetails($param);
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
            
            $allSubmissions = $this->odkFormService->exportAllV5Submissions($params);
echo $allSubmissions;die;
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
            $result = $this->odkFormService->approveSpiV5FormStatus($params);
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
            $result = $this->odkFormService->getFormData($params['auditId']);
            $viewModel = new ViewModel(array(
                'formData' => $result,
                'configData' => $configData,
                'tempId' => $params['tempId']
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        } else {
            $id = ($this->params()->fromRoute('id'));
            $formData = $this->odkFormService->getSpiV5FormData($id);
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
        $formData = $this->odkFormService->getFormData($id);
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
            $this->odkFormService->updateSpiForm($params);
            return $this->redirect()->toUrl("/spi-v3/manage-facility");
        } else {
            $id = $this->params()->fromRoute('id');
            $facilitiesResult = $this->odkFormService->getAllFacilityNames();
            $result = $this->odkFormService->getFormData($id);
            $provinceList = $this->odkFormService->getSpiV3FormUniqueLevelNames();
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
            $auditRoundWiseData = $this->odkFormService->getAuditRoundWiseDataV5($params);
            $perf1 = $this->odkFormService->getPerformance($params);
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
            $zeroCounts = $this->odkFormService->getZeroQuestionCountsV5($params);
            $spiV3Labels = $this->odkFormService->getSpiV5FormLabels();
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
            $allSubmissions = $this->odkFormService->getAllApprovedSubmissionLocationV5($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('allSubmissions' => $allSubmissions))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function manageFacilityAction()
    {
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $this->odkFormService->getAllSubmissionsDatas($param);
            return $this->getResponse()->setContent(Json::encode($result));
        } else {
            $result = $this->odkFormService->getPendingFacilityNames();

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
            $result = $this->odkFormService->deleteAuditData($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function spirtv5DatewiseAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $perflast30 = $this->odkFormService->getPerformanceLast30DaysV5($params);
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
            $testingVolume = $this->odkFormService->getAllApprovedTestingVolumeV5($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('testingVolume' => $testingVolume))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function viewDataAction()
    {
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getViewDataDetails($params);
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
        $testingPointResult = $this->odkFormService->getAllTestingPointType();
        $levelNamesResult = $this->odkFormService->getSpiV3FormUniqueLevelNames();
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
            
            $result = $this->odkFormService->getTestingPointTypeNamesByTypeV5($params);
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
            $result = $this->odkFormService->getAuditRoundWiseDataChartV5($params);
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
            $result = $this->odkFormService->getAllSpiV5SubmissionsDetails($param);
            
            $spiderResult = $this->odkFormService->getSpiV5AuditRoundWiseDataChart($param);
            //var_dump($result);die;
            $pieResult = $this->odkFormService->getSpiV5PerformancePieChart($param);
            $configData = $this->commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result, 'configData' => $configData, 'argument' => $param, 'spiderResult' => $spiderResult, 'pieResult' => $pieResult))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function duplicateAction()
    {
        $request = $this->getRequest();
        
        $result = $this->odkFormService->getAllDuplicateSubmissionsDetails();
        return new ViewModel(array('result' => $result));
    }

    public function removeAuditAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $result = $this->odkFormService->removeAudit($params);
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
            $result = $this->odkFormService->addV5DownloadData($params);
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
        
        $result = $this->odkFormService->getV5DownloadFilesRow();
        
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
