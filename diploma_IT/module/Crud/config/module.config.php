<?php
namespace Crud;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
	
    'router' => [
        'routes' => [      
            'crd' => [
            		'type'    => Segment::class,
            		'options' => [ 
            				'route'    => '/crd[/:action[/:id]]',
            				'constraints' => [
            						'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
            						'id'     	 => '[a-zA-Z0-9_-]*',
            				],
            				'defaults' => [
            						'controller' => Controller\IndexController::class,
            						'action'   => 'index',
            				],
            		],
            ],
			'reg' => [
				'type'    => Segment::class,
				'options' => [
						'route'       => '/reg[/:action][/:id]',
						'constraints' => [
								'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id'     	 => '[a-zA-Z0-9_-]*',
						],        		
						'defaults' => [
								'controller' => Controller\CrudController::class,
								'action'     => 'view',
						],
				],
				'may_terminate' => true,
				'child_routes'  => [
					'defaults' => [
						'type'      => Segment::class,
						'options'   => [
							'route' => '/[:controller[/:action][/:id]]',
							'constraints' => [
								'controller'  => '[a-zA-Z][a-zA-Z0-9_-]*',
								'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id'      => '[0-9]*',
							],
							'defaults' => [
							],
						],
					],
					'paginator' => [
						'type' => Segment::class,
						'options' => [
							'route' => '/[page/:page]',
							'constraints' => [
								'action'  => '[a-zA-Z][a-zA-Z0-9_-]*',
								'id'      => '[0-9]*', 
							],
							'defaults' => [
								'__NAMESPACE__' => 'Crud\Controller',
								'controller' => Controller\CrudController::class,
							],
						],
					],
				],
			],
			
		],
	],	
	'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view/',
        ],
		'display_exceptions' => true,
    ],
];
