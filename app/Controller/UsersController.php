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
class UsersController extends AppController {
	public $uses = array('User','Profile','Device');

	Public function AppLogin() {
		try {
			$this->autoRender = false;
			$this->response->type('json');
	
			if ($this->request->is('post') == false) {
				throw new ForbiddenException();
			}

			$login = $this->request->input('json_decode',true);
			if (empty($login)) {
				throw new Exception("login data format error !", 900);
			}

			if (array_key_exists("email", $login) == false ||
					array_key_exists("password", $login) == false) {
				# code...
				throw new Exception("email or password is missing !");
			}

			$conditions = array('email' => $login['email'],'password' => $login['password']);
			$user = $this->User->find('first', array('conditions' => $conditions));
			if (empty($user)) {
				throw new Exception("user_id/password is wrong !", 404);
			} else {
				$user = $user['User'];
				$userid = $user['id'];
				$email = $user['email'];
				$auto_login_token = $user['auto_login_token'];
				$this->response->body(json_encode(array('result' => array('code' => '0', 
																	  	  'message' => 'login success !', 
																	      'userinfo' => array('user_id' => $userid, 
																	      					  'email' => $email, 
																	      					  'auto_login_token' => $auto_login_token)))));
			}
		} catch (Exception $e) {
			$this->response->body(json_encode(array('error' => array('code' => $e->getCode(), 'message' => $e->getMessage()))));
		}
	}

	public function AppRegister() {
		try {
			$this->autoRender = false;
			$this->response->type('json');

			if ($this->request->is('post') == false) {
				throw new ForbiddenException();
			}

			$register = $this->request->input('json_decode',true);
			if (empty($register)) {
				throw new Exception("register data format error !", 900);
			}

			if (array_key_exists("email", $register) == false || 
					array_key_exists("password", $register) == false) {
				throw new Exception("must input user_id/password !", 900);
			}

			$email = $register['email'];
			$password = $register['password'];

			$user = $this->User->findByEmail($email);
			if (empty($user) == false) {
				throw new Exception("email account already exists !", 900);
			} else {
				$auto_login_token = sha1(date('Ymdhis') . $email . $password);
				$this->User->create();
				$this->User->set(array('email' => $email,
										'password' => $password,
										'auto_login_token' => $auto_login_token));
				$this->User->save();
				$userid = $this->User->id;
				// $this->User->Profile->create();
				// $this->User->Profile->set(array('user_id' => $userid));
				// $this->User->Profile->save();
				$this->response->body(json_encode(array('result' => array('code' => '0',
																		  'message' => 'register success !',
																		  'userinfo' => array('user_id' => $userid,
																		  					  'email' => $email,
																		  					  'auto_login_token' => $auto_login_token)))));
			}
		} catch (Exception $e) {
			$this->response->body(json_encode(array('error' => array('code' => $e->getCode(), 'message' => $e->getMessage()))));
		}
	}


}
