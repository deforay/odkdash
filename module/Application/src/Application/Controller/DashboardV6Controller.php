<?php



namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;


class DashboardV6Controller extends AbstractActionController
{

    private $odkFormService = null;
    private $provinceService = null;

    public function __construct($odkFormService, $provinceService)
    {
        $this->odkFormService = $odkFormService;
        $this->provinceService = $provinceService;
    }

    public function indexAction()
    {
        $params = [];
        $perf1 = $this->odkFormService->getPerformanceV6($params);
        $perflast30 = $this->odkFormService->getPerformanceLast30DaysV6('');
        $perflast180 = $this->odkFormService->getPerformanceLast180DaysV6();
        $allSubmissions = $this->odkFormService->getAllApprovedSubmissionsV6();
        $highVolumeSites = $this->odkFormService->getHighVolumeSites();
        $testingVolume = $this->odkFormService->getAllApprovedTestingVolumeV6('');
        $rawSubmissions = $this->odkFormService->getAllSubmissionsV6();
        //$auditRoundWiseData = $this->odkFormService->getAuditRoundWiseData('');
        //$zeroCounts = $this->odkFormService->getZeroQuestionCounts();
        //$spiV3Labels = $this->odkFormService->getSpiV3FormLabels();
        $spiV6auditRoundNo = $this->odkFormService->getSpiV6FormAuditNo();
        //$levelNamesResult = $this->odkFormService->getSpiV6FormUniqueLevelNames();
        $testingPointResult = $this->odkFormService->getAllTestingPointTypeV6();
        $locationResult = $this->provinceService->getAllMappedLocations();
        //$provinceResult = $this->provinceService->getAllActiveProvinces();
        return new ViewModel(array(
            'perf1' => $perf1,
            'perflast30' => $perflast30,
            'perflast180' => $perflast180,
            'allSubmissions' => $allSubmissions,
            'testingVolume' => $testingVolume,
            'rawSubmissions' => $rawSubmissions,
            'highVolumeSites' => $highVolumeSites,
            //'auditRoundWiseData' => $auditRoundWiseData,
            //'spiV3Labels' => $spiV3Labels,
            //'zeroCounts' => $zeroCounts,
            'spiV6auditRoundNo' => $spiV6auditRoundNo,
            'testingPointResult' => $testingPointResult,
            'locationResult' => $locationResult
        ));
    }
}
