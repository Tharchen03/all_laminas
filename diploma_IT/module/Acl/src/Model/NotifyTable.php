<?php
namespace Acl\Model;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Expression;
use zend\Db\TableGateway\TableGateway;

class NotifyTable extends AbstractTableGateway
{
	protected $table = 'sys_notify'; //tablename
	
	public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }	

	/**
	 * Return All records of table
	 * @return Array
	 */
	public function getAll()
	{
	    $adapter = $this->adapter;
	    $sql = new Sql($adapter);
	    $select = $sql->select();
	    $select->from(array('n'=>$this->table))	          
	           ->join(array('f'=>'sys_notification'), 'f.id = n.notification', array('route', 'action', 'key'));	   
		$select->order('n.id desc');
	    
	    $selectString = $sql->getSqlStringForSqlObject($select);
	    $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE)->toArray();
	    return $results;
	}

    /**
	 * Return records of given id
	 * @param Int $id
	 * @return Array
	 */
	public function get($param)
	{  
        $where = ( is_array($param) )? $param: array('n.id' => $param);
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from(array('n'=>$this->table))
		       ->join(array('f'=>'sys_notification'), 'f.id = n.notification', array('route', 'action', 'key'))	
		       ->where($where)
			   ->order('n.id desc');
		
		$selectString = $sql->getSqlStringForSqlObject($select);
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE)->toArray();
		return $results;
	}

	/**
     * Return column value of given where condition | id
     * @param Int|array $parma
     * @param String $column
     * @return String | Int
     */
    public function getColumn($param, $column)
    {         
		$where = ( is_array($param) )? $param: array('id' => $param);
		$fetch = array($column);
		$adapter = $this->adapter;       
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from($this->table);
		$select->columns($fetch);
		$select->where($where);

		$selectString = $sql->getSqlStringForSqlObject($select);
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE)->toArray();          
		
		foreach ($results as $result):
			$columns =  $result[$column];
		endforeach; 
		
		return $columns;       
    }
    /**
	 * Save record
	 * @param String $array
	 * @return Int
	 */
	public function save($data)
	{
	    
		if ( !is_array($data) ) $data = $data->toArray();
		$id = isset($data['id']) ? (int)$data['id'] : 0;
		
		if ( $id > 0 )
		{
			$result = ($this->update($data, array('id'=>$id)))?$id:0;
		} else {
			$this->insert($data);
			$result = $this->getLastInsertValue(); 
		}	    	    
		return $result;	 	     
	}

	/**
     *  Return Boolean
     *  @param int $id
     *  @return true | false
     */
	public function remove($id)
	{
		return $this->delete(array('id' => $id));
	}
	
	/**
	* check particular row is present in the table 
	* with given column and its value
	* 
	*/
	public function isPresent($column, $value)
	{
		$column = $column; $value = $value;
		$resultSet = $this->select(function(Select $select) use ($column, $value){
			$select->where(array($column => $value));
		});
		
		$resultSet = $resultSet->toArray();
		return (sizeof($resultSet)>0)? TRUE:FALSE;
	} 

	/**
	 * Return Min value of the column
	 * @param Array $where
	 * @param String $column
	 * @return String | Int
	 */
	public function getMin($where = NULL, $column)
	{
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from($this->table);
		$select->columns(array(
				'min' => new Expression('MIN('.$column.')')
		));
		if($where!=NULL){
			$select->where($where);
		}
		$selectString = $sql->getSqlStringForSqlObject($select);
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE)->toArray();
	
		foreach ($results as $result):
		$column =  $result['min'];
		endforeach;
	
		return $column;
	}
	
	/**
	 * Return max value of the column
	 * @param Array $where
	 * @param String $column
	 * @return String | Int
	 */
	public function getMax($where=NULL, $column)
	{
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from($this->table);
		$select->columns(array(
				'max' => new Expression('MAX('.$column.')')
		));
		if($where!=NULL){
			$select->where($where);
		}
		$selectString = $sql->getSqlStringForSqlObject($select);
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE)->toArray();
	
		foreach ($results as $result):
		$column =  $result['max'];
		endforeach;
	
		return $column;
	}
	/**
	 * LIMIT 3 ROWS FOR NOTIFICATION SHOW
	 * Return records of given id
	 * @param Int $id
	 * @return Array
	 */
	public function getLimit($param)
	{  
        $where = ( is_array($param) )? $param: array('n.id' => $param);
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from(array('n'=>$this->table))
		       ->join(array('f'=>'sys_notification'), 'f.id = n.notification', array('route', 'action', 'key','description'))	
		       ->where($where)
			   ->order('n.id desc')
			   ->limit(4);
		
		$selectString = $sql->getSqlStringForSqlObject($select);
		//echo $selectString;exit;
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE)->toArray();
		return $results;
	}
	/**
	 * Return Count value of the column
	 * @param Array $where
	 * @return String | Int
	 */
	public function getCount($where = NULL)
	{
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$select = $sql->select();
		$select->from($this->table)
			->columns(array('count' => new Expression('COUNT(*)')));
		
		if($where != NULL):
			$select->where($where);
		endif;
		
		$selectString = $sql->getSqlStringForSqlObject($select);
		$results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE)->toArray();
		
		foreach($results as $row);		
		return $row['count'];
	}
}
