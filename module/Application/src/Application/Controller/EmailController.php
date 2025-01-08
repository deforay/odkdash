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

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();

            $tempId = $this->facilityService->addEmail($params);
            if ($tempId > 0) {
                $ids = '';
                if (isset($params['audits']) && !empty($params['audits'])) {
                    $idArray = [];
                    $counter = count($params['audits']);
                    for ($au = 0; $au < $counter; $au++) {
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
        $result = $this->odkFormService->getAllFacilityNames();
        $facilityResult = $this->facilityService->getFacilityProfileByAudit($ids);

        return new ViewModel(array(
            'facilityName' => $result,
            'facilityResult' => $facilityResult,
            'ids' => $pdfIds
        ));
    }

    public function emailV5Action()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();

            $tempId = $this->facilityService->addEmailV5($params);
            if ($tempId > 0) {
                $ids = '';
                if (isset($params['audits']) && !empty($params['audits'])) {
                    $idArray = [];
                    $counter = count($params['audits']);
                    for ($au = 0; $au < $counter; $au++) {
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

        $result = $this->odkFormService->getAllFacilityNamesV5();
        $facilityResult = $this->facilityService->getFacilityProfileByAuditV5($ids);
        //var_dump($facilityResult);die;
        return new ViewModel(array(
            'facilityName' => $result,
            'facilityResult' => $facilityResult,
            'ids' => $pdfIds
        ));
    }

    public function emailV6Action()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();

            $tempId = $this->facilityService->addEmailV6($params);
            if ($tempId > 0) {
                $ids = '';
                if (isset($params['audits']) && !empty($params['audits'])) {
                    $idArray = [];
                    $counter = count($params['audits']);
                    for ($au = 0; $au < $counter; $au++) {
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

        $result = $this->odkFormService->getAllFacilityNamesV6();
        $facilityResult = $this->facilityService->getFacilityProfileByAuditV6($ids);
        // var_dump($facilityResult);die;
        return new ViewModel(array(
            'facilityName' => $result,
            'facilityResult' => $facilityResult,
            'ids' => $pdfIds
        ));
    }

    public function getFacilitiesAuditsAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getFacilitiesAudits($params);
            $viewModel = new ViewModel(array(
                'result' => $result
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function getFacilitiesV5AuditsAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getFacilitiesAuditsV5($params);
            $viewModel = new ViewModel(array(
                'result' => $result
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function getFacilitiesV6AuditsAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getFacilitiesAuditsV6($params);
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
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getFormData($params['auditId']);
            $configData = $this->commonService->getGlobalConfigDetails();
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
