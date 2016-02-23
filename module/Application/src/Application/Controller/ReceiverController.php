<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ReceiverController extends AbstractActionController
{

    public function indexAction()
    {        
        $viewModel = new ViewModel();
        //$viewModel->setVariables(array('key' => 'value'))
        
        $jsonData = file_get_contents('php://input');
        $this->var_error_log( $jsonData );        
        
        
        $viewModel->setTerminal(true);
        return $viewModel;        
    }

    
    public function var_error_log( $object=null ){
        ob_start();                    // start buffer capture
        var_dump( $object );           // dump the values
        $contents = ob_get_contents(); // put the buffer into a variable
        ob_end_clean();                // end capture
        error_log( $contents );        // log contents of the result of var_dump( $object )
    }
 
}

