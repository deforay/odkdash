<?php

namespace Application\Controller;

use Zend\Config\Config;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class FacilityController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $facilityService= $this->getServiceLocator()->get('FacilityService');
            $result = $facilityService->getAllFacilities($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $facilityService= $this->getServiceLocator()->get('FacilityService');
            $result = $facilityService->addFacility($params);
            return $this->redirect()->toRoute("spi-facility");
        }
    }
    
    public function editAction() {
        $request = $this->getRequest();
        $facilityService= $this->getServiceLocator()->get('FacilityService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $facilityService->updateFacility($params);
            return $this->redirect()->toRoute("spi-facility");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $facilityService->getFacility($id);
            //\Zend\Debug\Debug::dump($result);
            //die;
            return new ViewModel(array(
                'result' => $result,
            ));
        }
    }
    
    public function facilityListAction(){
        $request = $this->getRequest();
        
        if ($request->isGet()) {
            $val = $request->getQuery('search');
            //\Zend\Debug\Debug::dump($val);
        //die;
            $facilityService= $this->getServiceLocator()->get('FacilityService');
            $result = $facilityService->getFacilityList($val);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
}
