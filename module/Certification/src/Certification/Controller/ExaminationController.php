<?php
namespace Certification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
 use Certification\Model\Examination;          
use Certification\Form\ExaminationForm;
 
class ExaminationController extends AbstractActionController {
    
   protected $examinationTable;
     public function getExaminationTable()
     {
         if (!$this->examinationTable) {
             $sm = $this->getServiceLocator();
             $this->examinationTable = $sm->get('Certification\Model\ExaminationTable');
         }
         return $this->examinationTable;
     }
    public function indexAction()
     {
     
     $paginator = $this->getExaminationTable()->fetchAll(true);
     // set the current page to what has been passed in query string, or to 1 if none set
     $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
     // set the number of items per page to 10
     $paginator->setItemCountPerPage(10);

     return new ViewModel(array(
         'paginator' => $paginator
     ));
     }
    
     public function addAction() {
         
     }
}


