<?php

namespace Certification;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface {

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Certification\Model\ProviderTable' => function($sm) {
                    $tableGateway = $sm->get('ProviderTableGateway');
                    $table = new \Certification\Model\ProviderTable($tableGateway);
                    return $table;
                },
                'ProviderTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Provider());
                    return new TableGateway('provider', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\TrainerTable' => function($sm) {
                    $tableGateway = $sm->get('TrainerTableGateway');
                    $table = new \Certification\Model\TrainerTable($tableGateway);
                    return $table;
                },
                'TrainerTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Trainer());
                    return new TableGateway('trainer', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\TrainingOrganizationTable' => function($sm) {
                    $tableGateway = $sm->get('TrainingOrganizationTableGateway');
                    $table = new \Certification\Model\TrainingOrganizationTable($tableGateway);
                    return $table;
                },
                'TrainingOrganizationTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\TrainingOrganization());
                    return new TableGateway('training_organization', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\TrainingTable' => function($sm) {
                    $tableGateway = $sm->get('TrainingTableGateway');
                    $table = new \Certification\Model\TrainingTable($tableGateway);
                    return $table;
                },
                'TrainingTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Training());
                    return new TableGateway('training', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\WrittenExamTable' => function($sm) {
                    $tableGateway = $sm->get('WrittenExamTableGateway');
                    $table = new \Certification\Model\WrittenExamTable($tableGateway);
                    return $table;
                },
                'WrittenExamTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\WrittenExam());
                    return new TableGateway('written_exam', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\PracticalExamTable' => function($sm) {
                    $tableGateway = $sm->get('PracticalExamTableGateway');
                    $table = new \Certification\Model\PracticalExamTable($tableGateway);
                    return $table;
                },
                'PracticalExamTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\PracticalExam());
                    return new TableGateway('practical_exam', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\ExamAdminTable' => function($sm) {
                    $tableGateway = $sm->get('ExamAdminTableGateway');
                    $table = new \Certification\Model\ExamAdminTable($tableGateway);
                    return $table;
                },
                'ExamAdminTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\ExamAdmin());
                    return new TableGateway('exam_admin_by', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\CertificationTable' => function($sm) {
                    $tableGateway = $sm->get('CertificationTableGateway');
                    $table = new \Certification\Model\CertificationTable($tableGateway);
                    return $table;
                },
                'CertificationTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Certification());
                    return new TableGateway('certification', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\CertificationIssuerTable' => function($sm) {
                    $tableGateway = $sm->get('CertificationIssuerTableGateway');
                    $table = new \Certification\Model\CertificationIssuerTable($tableGateway);
                    return $table;
                },
                'CertificationIssuerTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\CertificationIssuer());
                    return new TableGateway('certification_issuer', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\RecertificationTable' => function($sm) {
                    $tableGateway = $sm->get('RecertificationTableGateway');
                    $table = new \Certification\Model\RecertificationTable($tableGateway);
                    return $table;
                },
                'RecertificationTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Recertification());
                    return new TableGateway('recertification', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\ExaminationTable' => function($sm) {
                    $tableGateway = $sm->get('ExaminationTableGateway');
                    $table = new \Certification\Model\ExaminationTable($tableGateway);
                    return $table;
                },
                'ExaminationTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Examination());
                    return new TableGateway('examination', $dbAdapter, null, $resultSetPrototype);
                },
                'Certification\Model\CertificationMail\Table' => function($sm) {
                    $tableGateway = $sm->get('CertificationMailTableGateway');
                    $table = new \Certification\Model\CertificationMailTable($tableGateway);
                    return $table;
                },
                'CertificationMailTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\CertificationMail());
                    return new TableGateway('certification_mail', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

}
