<?php

/**
 * Affiliate Observer
 *
 * @category   Ambassador
 * @package    Ambassador_Event
 * @author     getAmbassador.com
 */
class Ambassador_Affiliate_Model_Observer extends Varien_Event_Observer
{
	/**
	 * Embeds iframe in content
	 *
	 * @param   Varien_Event_Observer $observer
	 * @return  Ambassador_Event
	 */
	public function embedIframe($observer)
	{
		if (Mage::app()->getRequest()->getParam('getambassador_affiliate_program')) {

			$block = Mage::app()->getLayout()->createBlock(
				'Mage_Core_Block_Template',
				'ambassador_affiliate_program',
				array('template' => "ambassador/sso/affiliate_program.phtml")
			);

			$block->assign('portal_url', Mage::getModel('core/variable')->loadByCode('getambassador_portal_url')->getValue('plain'));

			Mage::app()->getLayout()->getBlock('customer_account_navigation')->setActive('customer/affiliate');

			Mage::app()->getLayout()->getBlock('content')->unsetChildren();
			Mage::app()->getLayout()->getBlock('content')->insert($block);
		}

		return $this;
	}
}