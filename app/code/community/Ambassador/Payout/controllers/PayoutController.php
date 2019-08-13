<?php

class Ambassador_Payout_PayoutController extends Mage_Core_Controller_Front_Action
{
	public function creditAction ()
	{
		$api_key = $_POST['api_key'];
		$customer_email = $_POST['email'];
		$amount = $_POST['amount'];
		$comment = $_POST['comment'];
		$website_id = Mage::app()->getStore()->getWebsiteId();
		$magento_api_key = Mage::getModel('core/variable')->loadByCode('getambassador_api_key')->getValue('plain');

		if ($magento_api_key === $api_key) {

			try {

				if (!Mage::helper('core')->isModuleEnabled('Enterprise_CustomerBalance')) {
					echo json_encode(array('type' => 'error', 'message' => 'Store Credit disabled. Enterprise_CustomerBalance module is required.'));
					exit;
				}

				$customer = Mage::getModel('customer/customer');

				if ($customer_email) {

					$customer->setWebsiteId($website_id)->loadByEmail($customer_email);

					if (is_null($customer->getEntityId())) {
						echo json_encode(array('type' => 'error', 'message' => 'Unknown customer email.'));
						exit;
					}

					$data = array(
						'website_id' => $website_id,
						'amount_delta' => $amount,
						'comment' => $comment,
						'additional_info' => $comment
					);

					$customer->setCustomerBalanceData($data);
					$customer->save();

					if ($data = $customer->getCustomerBalanceData()) {
						if (!empty($data['amount_delta'])) {
							$balance = Mage::getModel('enterprise_customerbalance/balance')
								->setCustomer($customer)
								->setWebsiteId($data['website_id'])
								->setAmountDelta($data['amount_delta'])
								->setComment($data['comment'])
							;
							if (isset($data['notify_by_email']) && isset($data['store_id'])) {
								$balance->setNotifyByEmail(true, $data['store_id']);
							}
							$balance->save();
						}
					}
				}

			} catch (Exception $e) {

				echo json_encode(array('type' => 'exception', 'message' => $e->getMessage()));
				exit;
			}

			echo json_encode(true);
			exit;

		} else {

			echo json_encode(array('type' => 'error', 'message' => 'Invalid API key.'));
			exit;
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