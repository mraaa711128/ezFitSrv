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
class ScalesController extends AppController {
	public $uses = array('Scale');

	public function ConnectionCheck() {
		try {
			$this->autoRender = false;
			$this->response->type('json');

			if ($this->request->is('post') == false) {
				throw new ForbiddenException();
			}

			$checkinfo = $this->request->input('json_decode',true);
			if (empty($checkinfo)) {
				throw new Exception("check info format error !", 900);
			}
			//var_dump($checkinfo);

			if (array_key_exists("scale_mac_addr", $checkinfo) == false &&
					array_key_exists("scale_uuid", $checkinfo) == false) {
				throw new Exception("scale_mac_addr or scale_uuid is missing !",900);
			}

			//var_dump($scale_mac_addr);

			if (array_key_exists("scale_mac_addr", $checkinfo)) {
				$scale_mac_addr = $checkinfo['scale_mac_addr'];
				$scale = $this->Scale->findByScaleMacAddr($scale_mac_addr);
			} elseif (array_key_exists("scale_uuid", $checkinfo)) {
				$scale_uuid = $checkinfo['scale_uuid'];
				$scale = $this->Scale->findByScaleUuid($scale_uuid);
			}
			if (empty($scale)) {
				throw new ForbiddenException("Scale Not Found !");
			} else {
				$scale = $scale['Scale'];
				if (array_key_exists("local_ip_addr", $checkinfo)) {
					$scale['local_ip_addr'] = $checkinfo['local_ip_addr'];
					$scale['ip_addr'] = $_SERVER['REMOTE_ADDR'];
				}
				$this->Scale->clear();
				$this->Scale->set($scale);
				$this->Scale->save();
				$newscale = $this->Scale->read();
				$newscale = $newscale['Scale'];

				$return_body = array('result' => array('code' => '0', 'message' => 'connection success !'));
				$return_body['result']['scale'] = $newscale;
				$this->response->body(json_encode($return_body));
			}
		} catch (Exception $e) {
			$this->response->body(json_encode(array('error' => array('code' => $e->getCode(), 'message' => $e->getMessage()))));
		}
	}
}
