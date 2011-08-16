<?php
class ShareIt_Model_UsersMapper {
	public $table;
	
	public function __construct()
	{
		$this->table = new ShareIt_Model_DbTable_Users();
	}
	
	public static function getAuthAdapter(array $params)
	{
		$authAdapter = new Zend_Auth_Adapter_DbTable(
			Zend_Db_Table::getDefaultAdapter(),
			"users",
			"email",
			"password"
		);
		$authAdapter->setIdentity($params['email'])
			->setCredential(sha1($params['password']));
		
		return $authAdapter;
    }
}
