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
class WeightRawdatasController extends AppController {
	public $uses = array('WeightRawdata','User','Profile','WeightRecord','Scale');

	public function Upload() {
		try {
			$this->autoRender = false;

			if ($this->request->is('post') == false) {
				throw new ForbiddenException();
			}
			//var_dump($this->request->data);
			$rawdata = $this->request->input('json_decode',true);

			//var_dump($rawdata);
			if (empty($rawdata) == true) {
				throw new Exception("upload data format error !", 900);
			}
			if (array_key_exists("ScaleMacAddr", $rawdata) == false || 
					array_key_exists("UserId", $rawdata) == false || 
						array_key_exists("Weight", $rawdata) == false ||
							array_key_exists("Resistor", $rawdata) == false) {
				throw new Exception("scalemacaddr or userid or weight or resistor data missing !",900);
			}

			$scale = $this->Scale->findByScaleMacAddr($rawdata['ScaleMacAddr']);
			if (empty($scale) == true) {
				throw new Exception("scale device does not match !", 404);
			}
			$scale = $scale['Scale'];

			$user = $this->User->findById($rawdata['UserId']);
			if (empty($user) == true) {
				throw new Exception("user does not exist !", 404);
			}
			$profile = $user['Profile'];
			$user = $user['User'];

			$date = date('Y-m-d');
			$time = date('h:i:s');

			$this->WeightRawdata->create();
			$this->WeightRawdata->set(array('scale_mac_addr' => $scale['scale_mac_addr'],
											'user_id' => $user['id'],
											'date' => $date,
											'time' => $time,
											'weight' => $rawdata['Weight'],
											'resistor' => $rawdata['Resistor']));
			$this->WeightRawdata->save();
			
			$height = (float)$profile['height'];
			$age = (float)(date('Y') - date('Y', strtotime($profile['birth_date'])));
			$sex = (float)$profile['sex'];
			$weight = (float)$rawdata['Weight'];
			$resistor = (float)$rawdata['Resistor'];

			$fat = 0.0039*($height)^2 - 1.7678*$height + (0.1*$age + 16.2) + 9.5*$sex + (-0.0069*($weight)^2 + 1.5387*$weight - 54.209) + 165.9; //last value should be dR
			//(0.0039*H*H-1.7678*H) + (0.1*Y+16.2)+9.5*S+((-0.0069)*W*W+1.5387*W-54.209)+165.9
			$bmi = $weight / ($height/100)^2;

			$this->WeightRecord->create();
			$this->WeightRecord->set(array('user_id' => $user['id'],
										   'date' => $date,
										   'time' => $time,
										   'weight' => $weight,
										   'fat' => $fat,
										   'bmi' => $bmi));
			$this->WeightRecord->save();

			$scale['ip_addr'] = $_SERVER['REMOTE_ADDR'];
			$this->Scale->clear();
			$this->Scale->save($scale);

			$this->response->body(json_encode(array('result' => array('code' => '0', 'message' => 'upload success !'))));
			//$this->response->send();
		} catch (Exception $e) {
			$this->response->body(json_encode(array('error' => array('code' => $e->getCode(), 'message' => $e->getMessage()))));
			//$this->response->send();
		}
	}

}
