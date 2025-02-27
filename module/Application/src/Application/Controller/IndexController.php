<?php



namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Json\Json;

class IndexController extends AbstractActionController
{

    private $odkFormService = null;
    private $commonService = null;

    public function __construct($odkFormService, $commonService)
    {
        $this->odkFormService = $odkFormService;
        $this->commonService = $commonService;
    }

    public function indexAction()
    {
        $allSubmissions = $this->odkFormService->getAllApprovedSubmissionsV6();
        $testingVolume = $this->odkFormService->getAllApprovedTestingVolumeV6('');
        $this->layout()->setTemplate('layout/home');
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'allSubmissions' => $allSubmissions,
            'testingVolume' => $testingVolume
        ));
        return $viewModel;
    }

    public function auditLocationsAction()
    {

        if ($this->getRequest()->isPost()) {
            $configData = $this->commonService->getGlobalConfigDetails();
            $params = $this->getRequest()->getPost();
            $allSubmissions = $this->odkFormService->getAllApprovedSubmissionLocationV6($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array(
                'allSubmissions' => $allSubmissions,
                'configData' => $configData
            ))
                ->setTerminal(true);
            return $viewModel;
        }
    }

    public function auditPerformanceAction()
    {

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $auditRoundWiseData = $this->odkFormService->getAuditRoundWiseDataV6($params);
            $perf1 = $this->odkFormService->getPerformanceV6($params);
            //var_dump($perf1);die;
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('auditRoundWiseData' => $auditRoundWiseData, 'perf1' => $perf1))
                ->setTerminal(true);
            return $viewModel;
        }
    }
}
