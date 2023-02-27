<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Json\Json;

class DashboardV5Controller extends AbstractActionController
{

    private $odkFormService = null;

    public function __construct($odkFormService)
    {
        $this->odkFormService = $odkFormService;
    }

    public function indexAction()
    {
        $params = array();
        $perf1 = $this->odkFormService->getPerformanceV5($params);
        $perflast30 = $this->odkFormService->getPerformanceLast30DaysV5('');
        $perflast180 = $this->odkFormService->getPerformanceLast180DaysV5();
        $allSubmissions = $this->odkFormService->getAllApprovedSubmissionsV5();
        $testingVolume = $this->odkFormService->getAllApprovedTestingVolumeV5('');
        $rawSubmissions = $this->odkFormService->getAllSubmissionsV5();
        //$auditRoundWiseData = $this->odkFormService->getAuditRoundWiseData('');
        //$zeroCounts = $this->odkFormService->getZeroQuestionCounts();
        //$spiV3Labels = $this->odkFormService->getSpiV3FormLabels();
        $spiV5auditRoundNo = $this->odkFormService->getSpiV5FormAuditNo();
        $levelNamesResult = $this->odkFormService->getSpiV5FormUniqueLevelNames();
        $testingPointResult = $this->odkFormService->getAllTestingPointTypeV5();
        
        // print_r($perflast30);die;
        return new ViewModel(array(
            'perf1' => $perf1,
            'perflast30' => $perflast30,
            'perflast180' => $perflast180,
            'allSubmissions' => $allSubmissions,
            'testingVolume' => $testingVolume,
            'rawSubmissions' => $rawSubmissions,
            //'auditRoundWiseData' => $auditRoundWiseData,
            //'spiV3Labels' => $spiV3Labels,
            //'zeroCounts' => $zeroCounts,
            'spiV5auditRoundNo' => $spiV5auditRoundNo,
            'testingPointResult' => $testingPointResult,
            'levelNamesResult' => $levelNamesResult
        ));
    }

}
