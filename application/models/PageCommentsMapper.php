<?php
class ShareIt_Model_PageCommentsMapper {
	public $table;
	
	public function __construct()
	{
		$this->table = new ShareIt_Model_DbTable_PageComments();
	}
	
	public function getListing($pageId)
	{
		return $this->table->getAdapter()->
			select()->
			from("page_comments")->
			joinLeft("users", "user_id=users.id", array('user' => "email"))->
			where("page_id=?", array($pageId))->
			query()->
			fetchAll();
	}
	
	public function save($pageId, array $data)
	{
		$auth = ShareIt_Controller_Front::getAuthObject();
		return $this->table->insert(array(
			'page_id' => $pageId,
			'user_id' => $auth->getIdentity()->id,
			'content' => $data['content'],
			'created' => new Zend_Db_Expr("NOW()")
			)
		);
	}
}
