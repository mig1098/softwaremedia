<?php
// @codingStandardsIgnoreStart
/**
 * StoreFront Consulting Kount Magento Extension
 *
 * PHP version 5
 *
 * @category  SFC
 * @package   SFC_Kount
 * @copyright 2009-2013 StoreFront Consulting, Inc. All Rights Reserved.
 *
 */
// @codingStandardsIgnoreEnd

class SFC_Kount_EnsController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
        // Total
        $iTotalSuccess = 0;
        $iTotalFailures = 0;

        try {

            // Log
            Mage::log('==== ENS Url Callback ====', Zend_Log::INFO, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            // Validate Ip addresses
            $sIpAddress = Mage::helper('core/http')->getRemoteAddr();
            if (!Mage::getStoreConfig('kount/account/test')
                && ($sIpAddress != SFC_Kount_Helper_EnsHandler::IPADDRESS_1
                    && $sIpAddress != SFC_Kount_Helper_EnsHandler::IPADDRESS_2)
            ) {
                Mage::throwException('Invalid ENS Ip Address.');
            }

            // Helper
            $oPathHelper = new SFC_Kount_Helper_Paths();

            // Is Kount enabled?
            if (!Mage::getStoreConfig('kount/account/enabled')) {
                Mage::log('Kount not enabled by system configuration, skipping RIS update.', Zend_Log::INFO,
                    SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
            }

            // Validate Kount settings
            if (!$oPathHelper->validateConfig()) {
                Mage::throwException('Kount settings not configured, skipping Ris inquiry.');
            }

            // Retrieve the XML sent in the HTTP POST request to the ResponseHandler
            $sResponse = $GLOBALS['HTTP_RAW_POST_DATA'];

			$rResponse = file_get_contents('php://input');
			
            // Handler
            $oHandler = new SFC_Kount_Helper_EnsHandler();

            // Parse Xml
            $oParser = new Mage_Xml_Parser();
            
            Mage::log('ENS REQUEST',NULL,'kount-log.log');
            Mage::log($sResponse,NULL,'kount-log.log');
            Mage::log($rResponse,NULL,'kount-log.log');
            $aEvents = $oParser->loadXML($sResponse)->xmlToArray();
            if (empty($aEvents) || !is_array($aEvents)) {
                Mage::throwException('Unable to parse Xml.');
            }

            // Validate
            if (!isset($aEvents['events']['_value']) || !isset($aEvents['events']['_attribute']['merchant'])) {
                Mage::throwException('Invalid Xml.');
            }

            // Validate merchant Id
            if (!$oHandler->validateStoreForMerchantId($aEvents['events']['_attribute']['merchant'])) {
                Mage::throwException('Invalid Merchant Id in event Xml.');
            }

            // Process, more than one?
            if (isset($aEvents['events']['_value']['event']['name'])) {
                $oHandler->handleEvent($aEvents['events']['_value']['event']);
            }
            else {
                foreach ($aEvents['events']['_value']['event'] as $aEvent) {
                    try {
                        $oHandler->handleEvent($aEvent);

                        // Total success
                        $iTotalSuccess++;
                    }
                    catch (Exception $e) {
                        // Log
                        Mage::log('Failed to hand ENS event!  Error message: ', Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);
                        Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

                        // Total failures
                        $iTotalFailures++;
                    }
                }
            }

            // Success
            $iTotalSuccess = isset($aEvents['events']['_attribute']['total']) ? intval(($aEvents['events']['_attribute']['total'])) : 0;

        }
        catch (Exception $e) {

            // Log
            Mage::log($e->getMessage(), Zend_Log::ERR, SFC_Kount_Helper_Paths::KOUNT_LOG_FILE);

            // Total failures
            $iTotalFailures = 1;
        }

        // Build xml response
        $xmlResponse = <<<EXML
<?xml version="1.0" encoding="UTF-8"?>
<eventResponse successes="$iTotalSuccess" failures="$iTotalFailures">
</eventResponse>
EXML;

        // Response
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-Type', 'text/xml')
            ->setBody($xmlResponse);
    }

}