<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Json\Json;

class SpiV6ReportsController extends AbstractActionController
{

    private $odkFormService = null;

    public function __construct($odkFormService)
    {
        $this->odkFormService = $odkFormService;
    }


    public function indexAction()
    {
        return new ViewModel();
    }

    public function facilityReportAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $this->odkFormService->getAllApprovedV6FormSubmissionsTable($param);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $spiV3auditRoundNo = $this->odkFormService->getSpiV6FormAuditNo();
        
        $pendingCount = $this->odkFormService->getSpiV6PendingCount();
        $levelNamesResult = $this->odkFormService->getSpiV6FormUniqueLevelNames();
        $testingPointResult = $this->odkFormService->getAllSpiV6TestingPointType();
        return new ViewModel(array(
            'testingPointResult' => $testingPointResult,
            'pendingCount' => $pendingCount,
            'spiV3auditRoundNo' => $spiV3auditRoundNo,
            'levelNamesResult' => $levelNamesResult
        ));
    }

    public function exportFacilityReportAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $result = $this->odkFormService->exportV6FacilityReport($params);
            $viewModel = new ViewModel(array('result' => $result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}
