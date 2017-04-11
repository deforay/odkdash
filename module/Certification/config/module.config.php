<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Certification;

return array(
    'router' => array(
        'routes' => array(
            'certification' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/certification[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Certification\Controller\Certification',
                        'action' => 'index',
                    ),
                ),
            ),
             'certification-issuer' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/certification-issuer[/:action][/:certification_issuer_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'certification_issuer_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Certification\Controller\CertificationIssuer',
                        'action' => 'index',
                    ),
                ),
            ),
             'recertification' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/recertification[/:action][/:recertification_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'recertification_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Certification\Controller\Recertification',
                        'action' => 'index',
                    ),
                ),
            ),
             
            'exam-admin' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/exam-admin[/:action][/:exam_admin_by_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'exam_admin_by_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Certification\Controller\ExamAdmin',
                        'action' => 'index',
                    ),
                ),
            ),
            
             
            'written-exam' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/written-exam[/:action][/:id_written_exam]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id_written_exam' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Certification\Controller\WrittenExam',
                        'action' => 'index',
                    ),
                ),
            ),
            'practical-exam' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/practical-exam[/:action][/:practice_exam_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'practice_exam_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Certification\Controller\PracticalExam',
                        'action' => 'index',
                    ),
                ),
            ),
            'provider' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/provider[/][:action][/:certification_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'certification_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Certification\Controller\Provider',
                        'action' => 'index',
                    ),
                ),
            ),
           'trainer' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/trainer[/:action][/:trainer_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'trainer_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Certification\Controller\Trainer',
                        'action' => 'index',
                    ),
                ),
            ),
            'training' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/training[/:action][/:training_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'training_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Certification\Controller\Training',
                        'action' => 'index',
                    ),
                ),
            ),
//            
              'training-organization' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/training-organization[/:action][/:training_organization_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'training_organization_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Certification\Controller\TrainingOrganization',
                        'action' => 'index',
                    ),
                ),
            ),
//              'certification-mail' => array(
//                'type' => 'segment',
//                'options' => array(
//                    'route' => '/certification-mail[/:action][/:training_organization_id]',
//                    'constraints' => array(
//                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
//                        'training_organization_id' => '[0-9]+',
//                    ),
//                    'defaults' => array(
//                            'controller' => 'Certification\Controller\Email',
//                        'action' => 'index',
//                    ),
//                ),
//            ), 
//              
      ),
        
        
       
   ),
    'controllers' => array(
        'invokables' => array(
            'Certification\Controller\Certification' => "Certification\Controller\CertificationController",
            'Certification\Controller\CertificationIssuer' => "Certification\Controller\CertificationIssuerController",
            'Certification\Controller\ExamAdmin' => "Certification\Controller\ExamAdminController",
            'Certification\Controller\PracticalExam' => "Certification\Controller\PracticalExamController",
            'Certification\Controller\Provider' => "Certification\Controller\ProviderController",
            'Certification\Controller\Recertification' => "Certification\Controller\RecertificationController",
            'Certification\Controller\Trainer' => "Certification\Controller\TrainerController",
            'Certification\Controller\TrainingCertificate' => "Certification\Controller\TrainingCertificateController",
            'Certification\Controller\Training' => "Certification\Controller\TrainingController",
            'Certification\Controller\TrainingOrganization' => "Certification\Controller\TrainingOrganizationController",
            'Certification\Controller\TypeCertification' => "Certification\Controller\TypeCertificationController",
            'Certification\Controller\TypeExam' => "Certification\Controller\TypeExamController",
            'Certification\Controller\TypeTraining' => "Certification\Controller\TypeTrainingController",
            'Certification\Controller\WrittenExam' => "Certification\Controller\WrittenExamController",
            
            
        ),
        
       
    ),
    'view_manager' => array(
                   
        'template_path_stack' => array(
           __DIR__ . '/../view',
        ),
    ),
        // Placeholder for console routes
);
