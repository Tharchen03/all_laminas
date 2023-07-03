<?php
namespace Crud\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\MvcEvent;
use Interop\Container\ContainerInterface;
use Administration\Model as Administration;
use Acl\Model as Acl;
use Crud\Model as Crud;
class CrudController extends AbstractActionController
{
	private $_container;
	protected $_table; 		// database table 
    protected $_user; 		// user detail
    protected $_login_id; 	// logined user id
    protected $_login_role; // logined user role
    protected $_author; 	// logined user id
    protected $_created; 	// current date to be used as created dated
    protected $_modified; 	// current date to be used as modified date
    protected $_config; 	// configuration details
    protected $_dir; 		// default file directory
    protected $_id; 		// route parameter id, usally used by crude
    protected $_auth; 		// checking authentication
    protected $_highest_role;// highest user role
    protected $_lowest_role;// loweset user role
	protected $_safedataObj; // safedata controller plugin
    
	public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }
	/**
	 * Laminas Default TableGateway
	 * Table name as the parameter
	 * returns obj
	 */
	public function getDefaultTable($table)
	{
		$this->_table = new TableGateway($table, $this->_container->get('Laminas\Db\Adapter\Adapter'));
		return $this->_table;
	}

   /**
    * User defined Model
    * Table name as the parameter
    * returns obj
    */
    public function getDefinedTable($table)
    {
        $definedTable = $this->_container->get($table);
        return $definedTable;
    }
	
	/**
	* initial set up
	* general variables are defined here
	*/
	public function init()
	{
		$this->_auth = new AuthenticationService;
		if(!$this->_auth->hasIdentity()):
			$this->flashMessenger()->addMessage('error^ You dont have right to access this page!');
   	        $this->redirect()->toRoute('auth', array('action' => 'login'));
		endif;
		if(!isset($this->_config)) {
			$this->_config = $this->_container->get('Config');
		}
		if(!isset($this->_user)) {
		    $this->_user = $this->identity();
		}
		if(!isset($this->_login_id)){
			$this->_login_id = $this->_user->id;  
		}
		if(!isset($this->_login_role)){
			$this->_login_role = $this->_user->role;  
		}
		if(!isset($this->_highest_role)){
			$this->_highest_role = $this->getDefinedTable(Acl\RolesTable::class)->getMax($column='id');  
		}
		if(!isset($this->_lowest_role)){
			$this->_lowest_role = $this->getDefinedTable(Acl\RolesTable::class)->getMin($column='id'); 
		}
		if(!isset($this->_author)){
			$this->_author = $this->_user->id;  
		}
		
		$this->_id = $this->params()->fromRoute('id');
		
		$this->_created = date('Y-m-d H:i:s');
		$this->_modified = date('Y-m-d H:i:s');
		
		$this->_safedataObj = $this->safedata();
		$this->_connection = $this->_container->get('Laminas\Db\Adapter\Adapter')->getDriver()->getConnection();
		
		//$this->_permissionObj =  $this->PermissionPlugin();
		//$this->_permissionObj->permission($this->getEvent());	
	}
	/**
	 * index Action of MasterController
	 */
    public function indexAction()
    {  
    	$this->init(); 
		
        return new ViewModel([
			'title' => 'Crud Menu',
		]);
	}
	/**
	 *   View action
	 */
	public function viewAction()
	{
		$this->init();
		$crud = $this->getDefinedTable(Crud\CrudTable::class)->getAll();
		$paginator = new \Laminas\Paginator\Paginator(new \Laminas\Paginator\Adapter\ArrayAdapter($crud));
			
		$page = 1;
		if ($this->params()->fromRoute('page')) $page = $this->params()->fromRoute('page');
		$paginator->setCurrentPageNumber((int)$page);
		$paginator->setItemCountPerPage(20);
		$paginator->setPageRange(8);
        return new ViewModel(array(
			'title'            => 'Crud',
			'paginator'        => $paginator,
			'page'             => $page,
		)); 
	}
	/**
	 * add student type
	 */
    public function addcrudAction()
    {
		$this->init();
		$page = $this->_id;
		if($this->getRequest()->isPost()){
            $form = $this->getRequest()->getPost();
			$data = array(
				'name'  => $form['name'],
				'description'    => $form['description'],
				'videolink'          => $form['videolink'],
				'image'         => $form['image'],
				'status'        =>1,
				'author'         => $this->_author,
				'created'        => $this->_created,
				'modified'       => $this->_modified
			);
			$result = $this->getDefinedTable(Crud\CrudTable::class)->save($data);
			if($result):
				$this->flashMessenger()->addMessage("success^ Successfully added new location type."); 	             
			else:
				$this->flashMessenger()->addMessage("error^ Failed to add new location type.");	 	             
			endif;
			return $this->redirect()->toRoute('reg/paginator', array('action' => 'view', 'page'=>$this->_id, 'id'=>'0'));
		}
		$ViewModel = new ViewModel([
			'title'        => 'add crud',
			'page'         => $page,
		]); 
		$ViewModel->setTerminal(True);
		return $ViewModel;
	}
	/**
	 *  editstudent Action
	 **/
	public function editcrudAction()
	{
		$this->init();	
		if($this->getRequest()->isPost()){
			$form = $this->getRequest()->getPost();
			$data = array(	
				'id'=>$this->_id,
					'name' => $form['name'],
					'description' => $form['description'],
					'videolink'         => $form['videolink'],
					'image'          => $form['image'],
					'status'         =>  1,
					'author'         => $this->_author,
					'created'        => $this->_created,
					'modified'       => $this->_modified
			);
			$data = $this->_safedataObj->rteSafe($data);
			$result = $this->getDefinedTable(Crud\CrudTable::class)->save($data);	
			if($result > 0):
				$this->flashMessenger()->addMessage("success^ New Crud successfully added");
			else:
				$this->flashMessenger()->addMessage("error^ Failed to add new Crud");
			endif;
			return $this->redirect()->toRoute('reg',array('action' => 'view'));			 
		}
		$ViewModel = new ViewModel([
			   'title'      	=> 'Edit Crud',
			   'crud' 	=> $this->getDefinedTable(Crud\CrudTable::class)->getAll(),
			]);		
		$ViewModel->setTerminal(True);
		return $ViewModel;
	}


	public function deletecrudAction()
	{
		$this->init();	

		if($this->getRequest()->isPost()){
			$form = $this->getRequest()->getPost();
			$data = array(	
				'id'=>$this->_id
			);
			//$data = $this->_safedataObj->rteSafe($data);
			$result = $this->getDefinedTable(Crud\CrudTable::class)->remove($data);	
			if($result > 0):
				$this->flashMessenger()->addMessage("success^ New crud successfully deteleted");
			else:
				$this->flashMessenger()->addMessage("error^ Failed to detelet crud");
			endif;
			return $this->redirect()->toRoute('reg',array('action' => 'view'));			 
		}
		$ViewModel = new ViewModel([
			   'title'      	=> 'Delete crud',
			   'crud' 	=> $this->getDefinedTable(Crud\CrudTable::class)->get($this->_id),
			]);		
		$ViewModel->setTerminal(True);
		return $ViewModel;
	}
	
}