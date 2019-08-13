<?php

/**
 * Event Observer
 *
 * @category   Ambassador
 * @package    Ambassador_Event
 * @author     getAmbassador.com
 */
class Ambassador_Event_Model_Observer extends Varien_Event_Observer
{
	public function __construct() {

		parent::__construct();

		$mode = Mage::getModel('core/variable')->loadByCode('getambassador_mode')->getValue('plain');

		if ($mode === 'dev') {
			$this->ambassador_url = 'http://getambassador.dev/';
		} else {
			$this->ambassador_url = 'https://getambassador.com/';
		}
	}

	/**
	 * Calls commission/update API method
	 *
	 * @param   Varien_Event_Observer $observer
	 * @return  Ambassador_Event
	 */
	public function callCommissionUpdate($observer)
	{
		$snippet_type = Mage::getModel('core/variable')->loadByCode('getambassador_snippet_type')->getValue('plain');
		$username = Mage::getModel('core/variable')->loadByCode('getambassador_username')->getValue('plain');
		$api_key = Mage::getModel('core/variable')->loadByCode('getambassador_api_key')->getValue('plain');
		$mode = Mage::getModel('core/variable')->loadByCode('getambassador_mode')->getValue('plain');

		if (empty($username) || empty($api_key)) {
			return $this;
		}

		switch ($observer->getEvent()->getName()) {

			case 'sales_order_invoice_pay':
				$order = $observer->getEvent()->getInvoice()->getOrder();
				$is_approved = 1;
				break;
			case 'sales_order_shipment_save_after':
				$order = $observer->getEvent()->getShipment()->getOrder();
				$is_approved = 1;
				break;
			case 'order_cancel_after':
				$order = $observer->getEvent()->getOrder();
				$is_approved = 2;
				break;
		}

		$transaction_uid = $order->getRealOrderId();

		$api_url = $this->ambassador_url."api/v2/$username/$api_key/json/commission/update";

		// Data for API call
		$data = array(
			'transaction_uid' => $transaction_uid,
			'is_approved' => $is_approved
		);

		$data = http_build_query($data);

		// Call to API via CURL
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $api_url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);
		// Output
		$returnData = json_decode($buffer, true);

		return $this;
	}

	/**
	 * Adds initial code snippet to record commission
	 *
	 * @param   Varien_Event_Observer $observer
	 * @return  Ambassador_Event
	 */
	public function addInitialCodeSnippet($observer)
	{
		$snippet_type = Mage::getModel('core/variable')->loadByCode('getambassador_snippet_type')->getValue('plain');
		$campaign_uid = Mage::getModel('core/variable')->loadByCode('getambassador_active_campaign')->getValue('plain');
		$username     = Mage::getModel('core/variable')->loadByCode('getambassador_username')->getValue('plain');
		$api_key      = Mage::getModel('core/variable')->loadByCode('getambassador_api_key')->getValue('plain');
		$is_approved  = Mage::getModel('core/variable')->loadByCode('getambassador_is_approved')->getValue('plain');

		if (is_null($is_approved)) {
			$is_approved = $this->addMissingCustomVariable('getambassador_is_approved');
		}

		if (empty($username) || empty($api_key) || empty($campaign_uid) || $campaign_uid == 'disabled') {
			return $this;
		}

		switch ($snippet_type) {

			case 'img':
				$template = 'img';
				break;
			default:
				$template = 'ecommerce';
		}

		$block = Mage::app()->getLayout()->createBlock(
			'Mage_Core_Block_Template',
			'ambassador_img',
			array('template' => "ambassador/checkout/$template.phtml")
		);

		$block->assign('mbsy_is_approved', $is_approved);

		Mage::app()->getLayout()->getBlock('content')->append($block);

		return $this;
	}

	/**
	 * SSO logout
	 *
	 * @param   Varien_Event_Observer $observer
	 * @return  Ambassador_Event
	 */
	public function ssoLogout($observer)
	{

		$username = Mage::getModel('core/variable')->loadByCode('getambassador_username')->getValue('plain');
		$api_key = Mage::getModel('core/variable')->loadByCode('getambassador_api_key')->getValue('plain');
		$response_type = 'json';
		$mbsy_token = '';
		$mbsy_email = 'magento@example.com';
		$mbsy_signature = sha1($api_key.$mbsy_email);

		// Build and make company/token API call
		$url = $this->ambassador_url.'api/v2/'.$username.'/'.$api_key.'/'.$response_type.'/company/token';
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

		// Decode json response to array, you'll need to change this if using XML
		$response = json_decode($response, TRUE);

		// Grab token from response
		$mbsy_token = $response['response']['data']['token'];

		$block = Mage::app()->getLayout()->createBlock(
			'Mage_Core_Block_Template',
			'ambassador_sso_logout',
			array('template' => "ambassador/sso/sso_logout.phtml")
		);

		$block->assign('mbsy_token', $mbsy_token);
		$block->assign('mbsy_email', $mbsy_email);
		$block->assign('mbsy_signature', $mbsy_signature);
		$block->assign('portal_url', Mage::getModel('core/variable')->loadByCode('getambassador_portal_url')->getValue('plain'));

		Mage::app()->getLayout()->getBlock('content')->append($block);

		Mage::unregister('ambassador_logout_customer_email');

		return $this;
	}

	/**
	 * Adds link to portal
	 *
	 * @param   Varien_Event_Observer $observer
	 * @return  Ambassador_Event
	 */
	public function addLinkToPortal($observer)
	{
		$addLink = Mage::getModel('core/variable')->loadByCode('getambassador_add_affiliate_program_link')->getValue('plain');

		if ($addLink === '1') {

			$navigation_block = Mage::app()->getLayout()->getBlock('customer_account_navigation');

			if ($navigation_block) {
				$label = Mage::getModel('core/variable')->loadByCode('getambassador_portal_link_label')->getValue('plain');
				Mage::app()->getLayout()->getBlock('customer_account_navigation')->addLink('affiliate_program', 'customer/affiliate', $label);
			}
		}

		return $this;
	}

	public function addMissingCustomVariable($name)
	{
		switch ($name) {

			case 'getambassador_is_approved':

				$variable = Mage::getModel('core/variable')->loadByCode('getambassador_is_approved');
				$variableData = $variable->getData();

				if (empty($variableData)) {

					$variable->cleanModelCache();
					$variable = Mage::getModel('core/variable');

					$variable_data = array(
						'code' => 'getambassador_is_approved',
						'name' => 'getambassador Approve commission while placing order',
						'plain_value' => '0',
						'html_value' => ''
						);

					$variable->setData($variable_data);
				}

				try {
					$variable->save();
				} catch (Exception $e) {}

				return $variable_data['plain_value'];
				break;
		}
	}
}

?>