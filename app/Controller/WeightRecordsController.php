<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppController', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class WeightRecordsController extends AppController {
	public $uses = array('WeightRecord','User');

	public function QueryRecordsById($userid, $token, $date, $limitday) {
		try {
			$this->autoRender = false;

			if ($this->request->is('get') == false) {
				throw new ForbiddenException();
			}

			$user = $this->User->findById($userid);
			if (empty($user)) {
				throw new Exception("user does not exist !", 404);
			}
			$user = $user['User'];
			if ($user['auto_login_token'] != $token) {
				throw new ForbiddenException();
			}
			//var_dump($date);

			$limitval = (int)$limitday;
			if ($limitval <= 0) {
				$weightrecords = $this->WeightRecord->find('all', 
					array('conditions' => 
						  array('AND' => 
							  	array('WeightRecord.user_id' => $user['id'],
									  'WeightRecord.date <=' => $date)),
						  'order' => 
						  array('WeightRecord.date' => 'DESC',
							   	'WeightRecord.time' => 'DESC')));
			} else {
				$date = date('Y-m-d',strtotime('-' . $limitval . ' days', strtotime($date)));
				//var_dump($date);
				$weightrecords = $this->WeightRecord->find('all', 
					array('conditions' => 
						  array('AND' => 
							  	array('WeightRecord.user_id' => $user['id'],
									  'WeightRecord.date >=' => $date)),
						  'order' => 
						  array('WeightRecord.date' => 'DESC',
							   	'WeightRecord.time' => 'DESC'),
						  'limit' => $limitval));
			}
			if (empty($weightrecords) == true) {
				throw new Exception("there is no records !",404);
			}
			//$weightrecords = $weightrecords[0];
			//var_dump($weightrecords);

			$this->response->body(json_encode(array('result' => $weightrecords)));
			//$this->response->send();
		} catch (Exception $e) {
			$this->response->body(json_encode(array('error' => array('code' => $e->getCode(), 'message' => $e->getMessage()))));
			//$this->response->send();
		}
	}
}

?>