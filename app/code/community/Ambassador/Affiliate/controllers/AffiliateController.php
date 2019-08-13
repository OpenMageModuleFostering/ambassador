<?php

class Ambassador_Affiliate_AffiliateController extends Mage_Core_Controller_Front_Action
{
	public function indexAction ()
	{
		if (Mage::app()->getRequest()->getParam('sso')) {

			$this->_forward('index', 'account', 'customer', array('getambassador_affiliate_program' => true));

		} else {

			$portal_link_mode = Mage::getModel('core/variable')->loadByCode('getambassador_portal_link_mode')->getValue('plain');
			$portal_url = Mage::getModel('core/variable')->loadByCode('getambassador_portal_url')->getValue('plain');
			$mode = Mage::getModel('core/variable')->loadByCode('getambassador_mode')->getValue('plain');

			if ($mode === 'dev') {
				$ambassador_url = 'http://getambassador.dev/';
			} else {
				$ambassador_url = 'https://getambassador.com/';
			}

			$username = Mage::getModel('core/variable')->loadByCode('getambassador_username')->getValue('plain');
			$api_key = Mage::getModel('core/variable')->loadByCode('getambassador_api_key')->getValue('plain');
			$response_type = 'json';
			$mbsy_token = '';
			$mbsy_email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
			$first_name = urlencode(Mage::getSingleton('customer/session')->getCustomer()->getFirstname());
			$last_name =  urlencode(Mage::getSingleton('customer/session')->getCustomer()->getLastname());
			$mbsy_signature = sha1($api_key.$mbsy_email);
			$mbsy_email = urlencode($mbsy_email);

			// Build and make company/token API call
			$url = $ambassador_url.'api/v2/'.$username.'/'.$api_key.'/'.$response_type.'/company/token';
			$curl_handle = curl_init();
			curl_setopt($curl_handle, CURLOPT_URL, $url);
			curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($curl_handle, CURLOPT_POST, FALSE);
			curl_setopt($curl_handle, CURLOPT_FAILONERROR, FALSE);
			curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, FALSE);
			$response = curl_exec($curl_handle);
			curl_close($curl_handle);

			$response = json_decode($response, TRUE);

			// Grab token from response
			$mbsy_token = $response['response']['data']['token'];

			if ($portal_link_mode === 'iframe' && !$this->detect_ie()) {
				$current_url = Mage::helper('core/url')->getCurrentUrl();
				$return_url = urlencode(str_replace('/sso', '', $current_url).'?sso=1');

			} else {
				$return_url = urlencode($portal_url);
			}

			$url = $portal_url."/sso/login?token=$mbsy_token&email=$mbsy_email&first_name=$first_name&last_name=$last_name&signature=$mbsy_signature&return_url=$return_url";

			$this->_redirectUrl($url);

		}
	}

	private function detect_ie ()
	{
		if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
			return true;
		} else {
			return false;
		}
	}
}