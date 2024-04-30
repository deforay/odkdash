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
    private $provinceService = null;

    public function __construct($facilityService, $odkFormService,$provinceService)
    {
        $this->facilityService = $facilityService;
        $this->odkFormService = $odkFormService;
        $this->provinceService = $provinceService;
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
        }else{
            $provinceResult = $this->provinceService->getAllActiveProvinces();
            return new ViewModel(array(
                'provinceResult' => $provinceResult,
            ));
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
            $provinceResult = $this->provinceService->getAllActiveProvinces();
            return new ViewModel(array(
                'result' => $result,
                'provinceResult' => $provinceResult,
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
        $provinceResult = $this->provinceService->getAllActiveProvinces();
        return new ViewModel(array(
            'facilityName' => $result,
            'provinces' => $provinceResult
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

    public function uploadFacilityAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->facilityService->uploadFacility($params);
            if(empty($result)){
                return $this->redirect()->toRoute("upload-facility");
            }else{
                // Build query string for the result parameters
                $query = http_build_query([
                    'total' => $result['total'],
                    'notAdded' => $result['notAdded'],
                    'option' => $result['option']
                ]);
                $url = '/facility/upload-facility?' . $query;
                // Redirect to the generated URL
                return $this->redirect()->toUrl($url);
            }
        }    
    }

    public function checkProvinceDistrictAction()
    {
       $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            $provinceResult = $this->provinceService->checkProvinceDistrict($params);
            return $this->getResponse()->setContent(Json::encode($provinceResult));

        }   
    }
}
