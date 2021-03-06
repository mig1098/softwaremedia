<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_List_Response
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
{
    // ########################################

    public function processSuccess(array $response, array $responseParams = array())
    {
        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'ebay_item_id' => $this->createEbayItem($response['ebay_item_id'])->getId(),
        );

        $data = $this->appendStatusHiddenValue($data);
        $data = $this->appendStatusChangerValue($data, $responseParams);

        $data = $this->appendOnlineBidsValue($data);
        $data = $this->appendOnlineQtyValues($data);
        $data = $this->appendOnlinePriceValues($data);
        $data = $this->appendOnlineInfoDataValues($data);

        $data = $this->appendOutOfStockValues($data);
        $data = $this->appendItemFeesValues($data, $response);
        $data = $this->appendStartDateEndDateValues($data, $response);
        $data = $this->appendGalleryImagesValues($data, $response, $responseParams);

        $data = $this->appendSpecificsReplacementValues($data);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = json_encode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data)->save();

        $this->updateVariationsValues(false);
    }

    // ########################################

    public function markAsPotentialDuplicate()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        $additionalData['last_failed_action_data'] = array(
            'native_request_data' => $this->getRequestData()->getData(),
            'previous_status' => $this->getListingProduct()->getStatus(),
            'action' => Ess_M2ePro_Model_Listing_Product::ACTION_LIST,
            'request_time' => Mage::helper('M2ePro')->getCurrentGmtDate(),
        );

        $this->getListingProduct()->addData(array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED,
            'additional_data' => json_encode($additionalData),
        ))->save();

        $this->getEbayListingProduct()->updateVariationsStatus();
    }

    protected function appendSpecificsReplacementValues($data)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        $tempKey = 'variations_specifics_replacements';
        unset($data['additional_data'][$tempKey]);

        $requestData = $this->getRequestData()->getData();
        if (!isset($requestData[$tempKey])) {
            return $data;
        }

        $data['additional_data'][$tempKey] = $requestData[$tempKey];
        return $data;
    }

    // ########################################
}