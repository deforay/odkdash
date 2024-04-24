<?php

namespace Application\Controller;

use Laminas\Config\Config;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class ProvincesController extends AbstractActionController
{


    private $provinceService = null;

    public function __construct($provinceService)
    {
        $this->provinceService = $provinceService;
    }


    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->provinceService->getAllProvinceDetails($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            $this->provinceService->addProvince($params);
            return $this->redirect()->toRoute("provinces");
        }
    }

    public function editAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $this->provinceService->updateProvince($params);
            return $this->redirect()->toRoute("provinces");
        } else {
           
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $this->provinceService->getProvince($id);

            return new ViewModel(array(
                'result' => $result
            ));
        }
    }
}
