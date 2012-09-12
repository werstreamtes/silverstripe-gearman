<?php


/**
 *
 * Used to interface with the gearman service
 * 
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class GearmanService {
	
	public function __call($method, $args) {
		$name = preg_replace("/[^\w_]/","",Director::baseFolder() .'_handle');
		$val = get_include_path();
		require_once 'Net/Gearman/Client.php';
		
		$client = new Net_Gearman_Client('localhost:4730');
		$set = new Net_Gearman_Set;
		
		array_unshift($args, $method);
		$task = new Net_Gearman_Task($name, $args, null, Net_Gearman_Task::JOB_BACKGROUND);
		$set->addTask($task);
		$client->runSet($set);
	}
	
	public function handleCall($args) {
		if (!count($args)) {
			return;
		}
		$workerImpl = ClassInfo::implementorsOf('GearmanHandler');
		$workers = array();
		
		$method = array_shift($args);
		
		foreach ($workerImpl as $type) {
			$obj = Injector::inst()->get($type);
			if ($obj->getName() == $method) {
				call_user_func_array(array($obj, $method), $args);
			}
		}
	}
}
