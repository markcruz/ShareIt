<?php
class ShareIt_Model_PageFilesMapper {
	public $table;
	
	public function __construct()
	{
		$this->table = new ShareIt_Model_DbTable_PageFiles();
	}
	
	public function getDetail($pageId)
	{
		return $this->table->
			select()->
			where("page_id=?", array($pageId))->
			query()->
			fetch();
	}
	
	public function save($pageId, array $data)
	{
		return $this->table->insert(array(
			'page_id' => $pageId,
			'last_revision' => 0,
			'created' => new Zend_Db_Expr("NOW()")
			)
		);
	}
	
	public function getLastRevision($pageFileId)
	{
		return $this->table->getAdapter()->
			select()->
			from("page_files", "last_revision")->
			where("id=?", array($pageFileId))->
			query()->
			fetchColumn();
	}
	
	public function increaseLastRevision($pageFileId)
	{
		return $this->table->update(
			array('last_revision' => new Zend_Db_Expr("last_revision + 1")),
			array("id=?" => $pageFileId)
		);
	}
}
