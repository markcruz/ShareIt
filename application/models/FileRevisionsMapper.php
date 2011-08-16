<?php
class ShareIt_Model_FileRevisionsMapper {
	public $table;
	
	public function __construct()
	{
		$this->table = new ShareIt_Model_DbTable_FileRevisions();
	}
	
	public function getDetail($id)
	{
		return $this->table->
			select()->
			where("id=?", array($id))->
			query()->
			fetch();
	}
	
	public function getListing($fileId)
	{
		return $this->table->getAdapter()->
			select()->
			from("file_revisions")->
			joinLeft("users", "user_id=users.id", array('user' => "email"))->
			joinLeft("file_statuses", "file_revisions.status_id=file_statuses.id", array('fileStatus' => "name"))->
			where("file_id=?", array($fileId))->
			order("id DESC")->
			query()->
			fetchAll();
	}
	
	public function save($pageFileId, $filename)
	{
		$mapper = new ShareIt_Model_PageFilesMapper();
		$lastRevision = $mapper->getLastRevision($pageFileId);
		
		$auth = ShareIt_Controller_Front::getAuthObject();
		$content = file_get_contents($filename);
		return $this->table->insert(array(
			'file_id' => $pageFileId,
			'user_id' => $auth->getIdentity()->id,
			'size' => filesize($filename),
			'mimetype' => mime_content_type($filename),
			'filename' => pathinfo($filename, PATHINFO_BASENAME),
			'content' => $content,
			'revision' => $lastRevision + 1,
			'status_id' => 2, // completed (this status is fixed and cannot be removed)
			'created' => new Zend_Db_Expr("NOW()")
			)
		);
	}
}
