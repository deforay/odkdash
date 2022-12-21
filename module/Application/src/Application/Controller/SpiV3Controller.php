<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Json\Json;

class SpiV3Controller extends AbstractActionController
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
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        $testingPointResult = $this->odkFormService->getAllTestingPointType();
        $levelNamesResult = $this->odkFormService->getSpiV3FormUniqueLevelNames();

        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $this->odkFormService->getAllSubmissionsDetails($param);
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
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $allSubmissions = $this->odkFormService->exportAllSubmissions($params);

            $viewModel = new ViewModel(array('allSubmissions' => $allSubmissions));

            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function approveStatusAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->approveFormStatus($params);
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
        /** @var \Laminas\Http\Request $request */
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
            $formData = $this->odkFormService->getFormData($id,'yes');
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
        /** @var \Laminas\Http\Request $request */
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
            //\Zend\Debug\Debug::dump($result['facilityInfo']->fName);die;
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
            $auditRoundWiseData = $this->odkFormService->getAuditRoundWiseData($params);
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
            $zeroCounts = $this->odkFormService->getZeroQuestionCounts($params);
            $spiV3Labels = $this->odkFormService->getSpiV3FormLabels();
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
            $allSubmissions = $this->odkFormService->getAllApprovedSubmissionLocation($params);
            $configData = $this->commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('allSubmissions' => $allSubmissions,'configData'=>$configData))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function manageFacilityAction()
    {
        /** @var \Laminas\Http\Request $request */
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

    public function spirtv3DatewiseAction()
    {
        
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $perflast30 = $this->odkFormService->getPerformanceLast30Days($params);
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
            $testingVolume = $this->odkFormService->getAllApprovedTestingVolume($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('testingVolume' => $testingVolume))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function viewDataAction()
    {
        /** @var \Laminas\Http\Request $request */
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
            
            $result = $this->odkFormService->getTestingPointTypeNamesByType($params);
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
            // $result = $this->odkFormService->getAuditRoundWiseDataChart($params);
            $configData = $this->commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('configData' => $configData))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function exportAsPdfAction()
    {
        
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $this->odkFormService->getAllSubmissionsDetails($param);
            // $spiderResult = $this->odkFormService->getAuditRoundWiseDataChart($param);
            // $pieResult = $this->odkFormService->getPerformancePieChart($param);
            $configData = $this->commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result, 'configData' => $configData, 'argument' => $param))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function duplicateAction()
    {
        $result = $this->odkFormService->getAllDuplicateSubmissionsDetails();
        return new ViewModel(array('result' => $result));
    }

    public function removeAuditAction()
    {
        /** @var \Laminas\Http\Request $request */
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
            $result = $this->odkFormService->addDownloadData($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function downloadFilesAction()
    {
        
        $result = $this->odkFormService->getDownloadFilesRow();
        return new ViewModel(array('result' => $result));
    }

    public function getDistrictByProvinceAction()
    {
        /** @var \Laminas\Http\Request $request */
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
        /** @var \Laminas\Http\Request $request */
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
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $this->odkFormService->getAllValidateSpiv3Details($param);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    public function addSpiv3ValidateDataAction()
    {
        /** @var \Laminas\Http\Request $request */
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
