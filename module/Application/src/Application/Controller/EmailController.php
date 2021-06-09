<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class EmailController extends AbstractActionController
{


    private $facilityService = null;
    private $odkFormService = null;
    private $commonService = null;

    public function __construct($facilityService, $odkFormService, $commonService)
    {
        $this->facilityService = $facilityService;
        $this->odkFormService = $odkFormService;
        $this->commonService = $commonService;
    }

    public function indexAction()
    {
        $configData = $this->commonService->getGlobalConfigDetails();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $facilityService = $this->getServiceLocator()->get('FacilityService');
            $tempId = $facilityService->addEmail($params);
            if ($tempId > 0) {
                $ids = '';
                if (isset($params['audits']) && count($params['audits']) > 0) {
                    $idArray = array();
                    for ($au = 0; $au < count($params['audits']); $au++) {
                        $idArray[] = $params['audits'][$au];
                    }
                    $auditIds = implode("#", $idArray);
                    $ids = base64_encode($tempId . "#" . $auditIds);
                }
                return $this->redirect()->toUrl("/email/index/" . $ids);
            } else {
                return $this->redirect()->toRoute("email");
            }
        }

        $pdfIds = '';
        $ids = $this->params()->fromRoute('id');
        $splitIds = explode("#", base64_decode($ids));
        if (count($splitIds) > 1) {
            $pdfIds = $ids;
            $ids = '';
        }
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $facilityService = $this->getServiceLocator()->get('FacilityService');
        $result = $odkFormService->getAllFacilityNames();
        $facilityResult = $facilityService->getFacilityProfileByAudit($ids);
        
        return new ViewModel(array(
            'facilityName' => $result,
            'facilityResult' => $facilityResult,
            'ids' => $pdfIds
        ));
    }

    public function emailV5Action()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $facilityService = $this->getServiceLocator()->get('FacilityService');
            $tempId = $facilityService->addEmailV5($params);
            if ($tempId > 0) {
                $ids = '';
                if (isset($params['audits']) && count($params['audits']) > 0) {
                    $idArray = array();
                    for ($au = 0; $au < count($params['audits']); $au++) {
                        $idArray[] = $params['audits'][$au];
                    }
                    $auditIds = implode("#", $idArray);
                    $ids = base64_encode($tempId . "#" . $auditIds);
                }
                return $this->redirect()->toUrl("/email/email-v5/" . $ids);
            } else {
                return $this->redirect()->toRoute("email");
            }
        }

        $pdfIds = '';
        $ids = $this->params()->fromRoute('id');
        $splitIds = explode("#", base64_decode($ids));
        if (count($splitIds) > 1) {
            $pdfIds = $ids;
            $ids = '';
        }
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $facilityService = $this->getServiceLocator()->get('FacilityService');
        
        $result = $odkFormService->getAllFacilityNamesV5();
        $facilityResult = $facilityService->getFacilityProfileByAuditV5($ids);
        //var_dump($facilityResult);die;
        return new ViewModel(array(
            'facilityName' => $result,
            'facilityResult' => $facilityResult,
            'ids' => $pdfIds
        ));
    }

    public function emailV6Action()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $facilityService = $this->getServiceLocator()->get('FacilityService');
            $tempId = $facilityService->addEmailV6($params);
            if ($tempId > 0) {
                $ids = '';
                if (isset($params['audits']) && count($params['audits']) > 0) {
                    $idArray = array();
                    for ($au = 0; $au < count($params['audits']); $au++) {
                        $idArray[] = $params['audits'][$au];
                    }
                    $auditIds = implode("#", $idArray);
                    $ids = base64_encode($tempId . "#" . $auditIds);
                }
                return $this->redirect()->toUrl("/email/email-v6/" . $ids);
            } else {
                return $this->redirect()->toRoute("email");
            }
        }

        $pdfIds = '';
        $ids = $this->params()->fromRoute('id');
        $splitIds = explode("#", base64_decode($ids));
        if (count($splitIds) > 1) {
            $pdfIds = $ids;
            $ids = '';
        }
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $facilityService = $this->getServiceLocator()->get('FacilityService');
        
        $result = $odkFormService->getAllFacilityNamesV6();
        $facilityResult = $facilityService->getFacilityProfileByAuditV6($ids);
        // var_dump($facilityResult);die;
        return new ViewModel(array(
            'facilityName' => $result,
            'facilityResult' => $facilityResult,
            'ids' => $pdfIds
        ));
    }

    public function getFacilitiesAuditsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $result = $odkFormService->getFacilitiesAudits($params);
            $viewModel = new ViewModel(array(
                'result' => $result
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function getFacilitiesV5AuditsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $result = $odkFormService->getFacilitiesAuditsV5($params);
           // var_dump($result);die;
            $viewModel = new ViewModel(array(
                'result' => $result
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function getFacilitiesV6AuditsAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $result = $odkFormService->getFacilitiesAuditsV6($params);
        //    var_dump($result);die;
            $viewModel = new ViewModel(array(
                'result' => $result
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function downloadPdfAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $odkFormService = $this->getServiceLocator()->get('OdkFormService');
            $commonService = $this->getServiceLocator()->get('CommonService');
            $result = $odkFormService->getFormData($params['auditId']);
            $configData = $commonService->getGlobalConfigDetails();
            $viewModel = new ViewModel(array(
                'formData' => $result,
                'configData' => $configData,
                'tempId' => $params['tempId']
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}
