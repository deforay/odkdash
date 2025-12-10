<?php

namespace Application\Controller;


use Laminas\View\Model\ViewModel;
use Application\Service\CommonService;
use Application\Service\ProvinceService;
use Laminas\Mvc\Controller\AbstractActionController;

class DistrictController extends AbstractActionController
{


    private $provinceService = null;

    public function __construct(ProvinceService $provinceService)
    {
        $this->provinceService = $provinceService;
    }


    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->provinceService->getAllDistrictDetails($params);
            return $this->getResponse()->setContent(CommonService::jsonEncode($result));
        }
    }

    public function addAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            $this->provinceService->addDistrict($params);
            return $this->redirect()->toRoute("district");
        } else {
            $provinceResult = $this->provinceService->getAllActiveProvinces();
            return new ViewModel(array(
                'provinceResult' => $provinceResult
            ));
        }
    }

    public function editAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->provinceService->updateDistrict($params);
            return $this->redirect()->toRoute("district");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $this->provinceService->getProvince($id);
            $provinceResult = $this->provinceService->getAllActiveProvinces();
            return new ViewModel(array(
                'result' => $result,
                'provinceResult' => $provinceResult
            ));
        }
    }
}
