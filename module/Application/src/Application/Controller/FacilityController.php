<?php

namespace Application\Controller;

use Laminas\Config\Config;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class FacilityController extends AbstractActionController
{

    private $facilityService = null;
    private $odkFormService = null;

    public function __construct($facilityService, $odkFormService)
    {
        $this->facilityService = $facilityService;
        $this->odkFormService = $odkFormService;
    }

    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $result = $this->facilityService->getAllFacilities($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $result = $this->facilityService->addFacility($params);
            return $this->redirect()->toRoute("spi-facility");
        }
    }

    public function editAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->facilityService->updateFacility($params);
            return $this->redirect()->toRoute("spi-facility");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $this->facilityService->getFacility($id);
            //\Zend\Debug\Debug::dump($result);
            //die;
            return new ViewModel(array(
                'result' => $result,
            ));
        }
    }

    public function facilityListAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isGet()) {
            $val = $request->getQuery('search');
            //\Zend\Debug\Debug::dump($val);
            //die;
            
            $result = $this->facilityService->getFacilityList($val);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function getFacilityNameAction()
    {
        $layout = $this->layout();
        $layout->setTemplate('layout/modal');
        
        $result = $this->odkFormService->getAllFacilityNames();
        return new ViewModel(array(
            'facilityName' => $result
        ));
    }

    public function getTestingPointAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $result = $this->facilityService->getAllTestingPoints($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function getFacilityAuditRoundAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $result = $this->odkFormService->getSpiV3FormFacilityAuditNo($params);
            $viewModel = new ViewModel(array(
                'spiV3auditRoundNo' => $result
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function getProvinceListAction()
    {
        $layout = $this->layout();
        $layout->setTemplate('layout/modal');
        
        
        $result = $this->odkFormService->getAllFacilityNames();
        $provinceResult = $this->facilityService->getProvinceList();
        $districtResult = $this->facilityService->getDistrictList();
        return new ViewModel(array(
            'facilityName' => $result,
            'provinces' => $provinceResult,
            'districts' => $districtResult
        ));
    }

    public function mapProvinceAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $result = $this->facilityService->mapProvince($params);
            $viewModel = new ViewModel(array(
                'result' => $result
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function getFacilityDetailsAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            
            $result = $this->facilityService->getFacilityDetails($params);
            $viewModel = new ViewModel(array(
                'result' => $result
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function exportFacilityAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $result = $this->facilityService->exportFacility();
            $viewModel = new ViewModel(array(
                'result' => $result
            ));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function searchProvinceListAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isGet()) {
            $val = $request->getQuery('q');
            $result = $this->facilityService->getProvinceData($val);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    public function searchDistrictListAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isGet()) {
            $val = $request->getQuery('q');
            $result = $this->facilityService->getDistrictData($val);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
}
