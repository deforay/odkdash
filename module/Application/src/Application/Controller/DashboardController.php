<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Laminas\Session\Container;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Json\Json;

class DashboardController extends AbstractActionController
{

    private $odkFormService = null;

    public function __construct($odkFormService)
    {
        $this->odkFormService = $odkFormService;
    }

    public function indexAction()
    {
        $params = array();
        $perf1 = $this->odkFormService->getPerformance($params);
        $perflast30 = $this->odkFormService->getPerformanceLast30Days('');
        $perflast180 = $this->odkFormService->getPerformanceLast180Days();
        $allSubmissions = $this->odkFormService->getAllApprovedSubmissions();
        $testingVolume = $this->odkFormService->getAllApprovedTestingVolume('');
        $rawSubmissions = $this->odkFormService->getAllSubmissions();
        //$auditRoundWiseData = $this->odkFormService->getAuditRoundWiseData('');
        //$zeroCounts = $this->odkFormService->getZeroQuestionCounts();
        //$spiV3Labels = $this->odkFormService->getSpiV3FormLabels();
        $spiV3auditRoundNo = $this->odkFormService->getSpiV3FormAuditNo();
        $levelNamesResult = $this->odkFormService->getSpiV3FormUniqueLevelNames();
        $testingPointResult = $this->odkFormService->getAllTestingPointType();

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
            'spiV3auditRoundNo' => $spiV3auditRoundNo,
            'testingPointResult' => $testingPointResult,
            'levelNamesResult' => $levelNamesResult
        ));
    }

    public function auditDetailsAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->odkFormService->getAllApprovedSubmissionsDetailsBasedOnAuditDate($params);
            return $this->getResponse()->setContent(Json::encode($result));
        } else {
            $assesmentOfAuditDate = base64_decode($this->params()->fromRoute('id'));
            return new ViewModel(array(
                'assesmentOfAuditDate' => $assesmentOfAuditDate,
            ));
        }
    }
}
