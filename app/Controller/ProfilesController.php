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
class ProfilesController extends AppController {
	public $uses = array('Profile','User');

	public function AppUpdate() {
		try {
			$this->autoRender = false;
			$this->response->type('json');

			if ($this->request->is('post') == false) {
				throw new ForbiddenException();
			}

			$rawdata = $this->request->input('json_decode',true);
			if (empty($rawdata)) {
				throw new Exception("data format error !",900);
			}

			if (array_key_exists("login", $rawdata) == false || 
					array_key_exists("profile", $rawdata) == false) {
					throw new Exception("required data missing !", 900);
			}

			$login = $rawdata['login'];
			$profile = $rawdata['profile'];

			if (array_key_exists("email", $login) == false || 
					array_key_exists("auto_login_token", $login) == false) {
				throw new Exception("required login field missing !", 900);
			}
			$email = $login['email'];
			$auto_login_token = $login['auto_login_token'];

			$conditions = array('email' => $email, 'auto_login_token' => $auto_login_token);
			$user = $this->User->find('first',array('conditions' => $conditions));
			if (empty($user)) {
				throw new ForbiddenException("please login first !");
			}
			// var_dump($user);
			$oldprofile = $user['Profile'];
			$user = $user['User'];
			$userid = $user['id'];

			if (array_key_exists("nick_name", $profile) == false ||
					array_key_exists("sex", $profile) == false ||
						array_key_exists("birth_date", $profile) == false ||
							array_key_exists("height", $profile) == false ||
								array_key_exists("waist", $profile) == false) {
				throw new Exception("required profile field missing !",900);
			}
			// var_dump($profile);
			
			$profile['user_id'] = $userid;
			
			// var_dump($profile);

			$this->User->Profile->clear();
			$this->User->Profile->set($oldprofile);
			$this->User->Profile->set($profile);
			$this->User->Profile->save();

			$newprofile = $this->User->Profile->read();
			$newprofile = $newprofile['Profile'];
			//var_dump($newprofile);
			
			// $result_body = array('code' => '0','message' => 'update success !');
			// $result_body['profile'] = $newprofile;
			// var_dump($result_body);
			// $return_body = array();
			// $return_body['result'] = $result_body;
			// var_dump($return_body);

			$return_body = array('result' => array('code' => '0', 'message' => 'update success !'));
			$return_body['result']['profile'] = $newprofile;			
			$this->response->body(json_encode($return_body));
			// $this->response->body(json_encode(array('result' => array('code' => '0', 
			// 														  'message' => 'update success !', 
			// 														  'profile' => array('id' => $newprofile['id'],
			// 														  					 'user_id' => $newprofile['user_id'],
			// 														  					 'nick_name' => $newprofile['nick_name'],
			// 														  					 'last_name' => $newprofile['last_name'],
			// 														  					 'first_name' => $newprofile['first_name'],
			// 														  					 'height' => $newprofile['height'],
			// 														  					 'waist' => $newprofile['waist'],
			// 														  					 'sex' => $newprofile['sex'],
			// 														  					 'birth_date' => $newprofile['birth_date'])))));
		} catch (Exception $e) {
			$this->response->body(json_encode(array('error' => array('code' => $e->getCode(), 'message' => $e->getMessage()))));
		}
	}

}
