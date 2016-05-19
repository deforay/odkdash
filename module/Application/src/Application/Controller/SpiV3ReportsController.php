<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SpiV3ReportsController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function facilityReportAction()
    {
        $odkFormService = $this->getServiceLocator()->get('OdkFormService');
        $allSubmissions = $odkFormService->getAllApprovedSubmissions();        
        $rawSubmissions = $odkFormService->getAllSubmissions();
        $pendingCount = $odkFormService->getSpiV3PendingCount();
        $spiV3auditRoundNo = $odkFormService->getSpiV3FormAuditNo();
        
        return new ViewModel(array('allSubmissions' => $allSubmissions,
                                   'rawSubmissions' => $rawSubmissions,
                                   'pendingCount' => $pendingCount,
                                   'spiV3auditRoundNo'=>$spiV3auditRoundNo));
    }


}

