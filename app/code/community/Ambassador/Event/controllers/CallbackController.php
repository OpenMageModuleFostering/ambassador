<?php

class Ambassador_Event_CallbackController extends Mage_Core_Controller_Front_Action
{

	public function installAction ()
	{
		$api_key  = $_POST['api_key'];
		$username = $_POST['username'];
		$portal_url = $_POST['portal_url'];
		$token    = $_POST['token'];
		$mode = $_POST['mode'];

		if ($mode === 'dev') {
			$ambassador_url = 'https://dev.getambassador.com/';
		} else {
			$ambassador_url = 'https://getambassador.com/';
		}

		// check if api key & username are correct

		$data = array(
			'api_key'  => $api_key,
			'token'    => $token
			);

		// verify if token is valid
		$result = $this->curl_request($ambassador_url.'callback/magento_verify_token', $data);

		if ($result === true) {

			$variable = Mage::getModel('core/variable')->loadByCode('getambassador_api_key');

			if (is_null($variable->getValue('plain'))) {

				// save api key to DB
				$variable = Mage::getModel('core/variable');

				$variable_data = array(
					'code' => 'getambassador_api_key',
					'name' => 'getAmbassador Api Key',
					'plain_value' => $api_key,
					'html_value' => ''
					);

				$variable->setData($variable_data);

			} else {

				$variable->setData('plain_value', $api_key);
			}

			try {
				$variable->save();

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			// save username to DB
			$variable->cleanModelCache();
			$variable = Mage::getModel('core/variable')->loadByCode('getambassador_username');
			$variableData = $variable->getData();

			if (empty($variableData)) {

				$variable->cleanModelCache();
				$variable = Mage::getModel('core/variable');

				$variable_data = array(
					'code' => 'getambassador_username',
					'name' => 'getAmbassador Username',
					'plain_value' => $username,
					'html_value' => ''
					);

				$variable->setData($variable_data);

			} else {

				$variable->setData('plain_value', $username);
			}

			try {
				$variable->save();

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			// save portal URL to DB
			$variable->cleanModelCache();
			$variable = Mage::getModel('core/variable')->loadByCode('getambassador_portal_url');
			$variableData = $variable->getData();

			if (empty($variableData)) {

				$variable->cleanModelCache();
				$variable = Mage::getModel('core/variable');

				$variable_data = array(
					'code' => 'getambassador_portal_url',
					'name' => 'getAmbassador Portal URL',
					'plain_value' => $portal_url,
					'html_value' => ''
					);

				$variable->setData($variable_data);

			} else {

				$variable->setData('plain_value', $portal_url);
			}

			try {
				$variable->save();

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			// save portal link label to DB
			$variable->cleanModelCache();
			$variable = Mage::getModel('core/variable')->loadByCode('getambassador_portal_link_label');
			$variableData = $variable->getData();

			if (empty($variableData)) {

				$variable->cleanModelCache();
				$variable = Mage::getModel('core/variable');

				$variable_data = array(
					'code' => 'getambassador_portal_link_label',
					'name' => 'getAmbassador Portal Link Label',
					'plain_value' => 'Affiliate Program',
					'html_value' => ''
					);

				$variable->setData($variable_data);
			}

			try {
				$variable->save();

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			// save add_affiliate_program_link
			$variable->cleanModelCache();
			$variable = Mage::getModel('core/variable')->loadByCode('getambassador_add_affiliate_program_link');
			$variableData = $variable->getData();

			if (empty($variableData)) {

				$variable->cleanModelCache();
				$variable = Mage::getModel('core/variable');

				$variable_data = array(
					'code' => 'getambassador_add_affiliate_program_link',
					'name' => 'getAmbassador Add Affiliate Program Link',
					'plain_value' => '1',
					'html_value' => ''
					);

				$variable->setData($variable_data);
			}

			try {
				$variable->save();

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			// save mode
			$variable->cleanModelCache();
			$variable = Mage::getModel('core/variable')->loadByCode('getambassador_mode');
			$variableData = $variable->getData();

			if (empty($variableData)) {

				$variable->cleanModelCache();
				$variable = Mage::getModel('core/variable');

				$variable_data = array(
					'code' => 'getambassador_mode',
					'name' => 'getAmbassador Mode',
					'plain_value' => $mode,
					'html_value' => ''
					);

				$variable->setData($variable_data);
			} else {

				$variable->setData('plain_value', $mode);
			}

			try {
				$variable->save();

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			// save portal link mode
			$variable->cleanModelCache();
			$variable = Mage::getModel('core/variable')->loadByCode('getambassador_portal_link_mode');
			$variableData = $variable->getData();

			if (empty($variableData)) {

				$variable->cleanModelCache();
				$variable = Mage::getModel('core/variable');

				$variable_data = array(
					'code' => 'getambassador_portal_link_mode',
					'name' => 'getambassador Portal Link Mode',
					'plain_value' => 'iframe',
					'html_value' => ''
					);

				$variable->setData($variable_data);
			}

			try {
				$variable->save();

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			// save default auto_create
			$variable->cleanModelCache();
			$variable = Mage::getModel('core/variable')->loadByCode('getambassador_auto_create_ambassador');
			$variableData = $variable->getData();

			if (empty($variableData)) {

				$variable->cleanModelCache();
				$variable = Mage::getModel('core/variable');

				$variable_data = array(
					'code' => 'getambassador_auto_create_ambassador',
					'name' => 'getambassador Automatically Create Ambassador',
					'plain_value' => '0',
					'html_value' => ''
					);

				$variable->setData($variable_data);
			}

			try {
				$variable->save();

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			// save default is_approved
			$variable->cleanModelCache();
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

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			echo json_encode(true);

		} else {

			echo json_encode(false);
		}
	}

	public function integrateAction ()
	{
		$api_key      = $_POST['api_key'];
		$token        = $_POST['token'];
		$campaign_id  = $_POST['campaign_id'];
		$snippet_type = $_POST['snippet_type'];

		$mode = Mage::getModel('core/variable')->loadByCode('getambassador_mode')->getValue('plain');

		if ($mode === 'dev') {
			$ambassador_url = 'https://dev.getambassador.com/';
		} else {
			$ambassador_url = 'https://getambassador.com/';
		}

		$data = array(
			'api_key'  => $api_key,
			'token'    => $token
			);

		// verify if token is valid
		$result = $this->curl_request($ambassador_url.'callback/magento_verify_token', $data);

		if ($result === true) {

			// save campaign ID to DB
			$current_campaign_id = Mage::getModel('core/variable')->loadByCode('getambassador_active_campaign');
			$variableData = $current_campaign_id->getData();

			if (empty($variableData)) {

				$current_campaign_id->cleanModelCache();
				$current_campaign_id = Mage::getModel('core/variable');

				$variable_data = array(
					'code' => 'getambassador_active_campaign',
					'name' => 'getAmbassador Active Campaign',
					'plain_value' => $campaign_id,
					'html_value' => ''
					);

				$current_campaign_id->setData($variable_data);

			} else {

				$current_campaign_id->setData('plain_value', $campaign_id);

			}

			try {
				$current_campaign_id->save();

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			// save snippet type to DB
			$current_campaign_id->cleanModelCache();
			$current_snippet_type = Mage::getModel('core/variable')->loadByCode('getambassador_snippet_type');
			$variableData = $current_snippet_type->getData();

			if (empty($variableData)) {

				$current_snippet_type->cleanModelCache();
				$current_snippet_type = Mage::getModel('core/variable');
				$variable_data = array(
					'code' => 'getambassador_snippet_type',
					'name' => 'getAmbassador Snippet Type',
					'plain_value' => $snippet_type,
					'html_value' => ''
					);

				$current_snippet_type->setData($variable_data);

			} else {

				$current_snippet_type->setData('plain_value', $snippet_type);
			}

			try {
				$current_snippet_type->save();

			} catch (Exception $e) {

				echo json_encode($e->getMessage());
			}

			echo json_encode(true);

		} else {

			echo json_encode(false);
		}
	}

	private function curl_request($url, $data)
	{
		$data = http_build_query($data);

		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($curl_handle);
		curl_close($curl_handle);

		return json_decode($result);
	}
}

?>