<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class CommonController extends AbstractActionController
{

    private $commonService = null;
    private $odkFormService = null;

    public function __construct($commonService, $odkFormService)
    {
        $this->commonService = $commonService;
        $this->odkFormService = $odkFormService;
    }

    public function indexAction()
    {
        $result = "";
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->commonService->checkFieldValidations($params);
        }
        $viewModel = new ViewModel();
        $viewModel->setVariables(array('result' => $result))
            ->setTerminal(true);

        return $viewModel;
    }
    public function multipleFieldValidationAction()
    {
        $result = "";
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            //\Zend\Debug\Debug::dump($params);die;

            $result = $this->commonService->checkMultipleFieldValidations($params);
        }
        $viewModel = new ViewModel();
        $viewModel->setVariables(array('result' => $result))
            ->setTerminal(true);

        return $viewModel;
    }
    public function auditLocationsAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isGet()) {
            $val = $request->getQuery();
            $spiV3auditRoundNo = $this->odkFormService->getSpiV3FormAuditNo();
            return new ViewModel(array(
                'id' => $val,
                'spiV3auditRoundNo' => $spiV3auditRoundNo
            ));
        }
    }

    public function getAuditLocationBasedOnFormAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $auditRoundNo = [];
            $id = $params['inpId'];
            if (trim($params["formVersion"]) == 'v3') {
                $auditRoundNo = $this->odkFormService->getSpiV3FormAuditNo();
            }
            if (trim($params["formVersion"]) == 'v6') {
                $auditRoundNo = $this->odkFormService->getSpiV6FormAuditNo();
            }
            //\Zend\Debug\Debug::dump($auditRoundNo);die;
        }
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'result' => $auditRoundNo,
            'id' => $id
        ))
            ->setTerminal(true);

        return $viewModel;
    }
}
