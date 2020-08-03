<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Json\Json;

class SpiV3ReportsController extends AbstractActionController
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
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $param = $request->getPost();
            $result = $this->odkFormService->getAllApprovedSubmissionsTable($param);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $spiV3auditRoundNo = $this->odkFormService->getSpiV3FormAuditNo();
        //
        //$allSubmissions = $this->odkFormService->getAllApprovedSubmissions();        
        //$rawSubmissions = $this->odkFormService->getAllSubmissions();
        $pendingCount = $this->odkFormService->getSpiV3PendingCount();
        $levelNamesResult = $this->odkFormService->getSpiV3FormUniqueLevelNames();
        //$spiV3auditRoundNo = $this->odkFormService->getSpiV3FormAuditNo();
        //
        return new ViewModel(array(
            'pendingCount' => $pendingCount, 'spiV3auditRoundNo' => $spiV3auditRoundNo,
            'levelNamesResult' => $levelNamesResult
        ));
    }

    public function exportFacilityReportAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $result = $this->odkFormService->exportFacilityReport($params);
            $viewModel = new ViewModel(array('result' => $result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}
