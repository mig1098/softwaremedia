<?php
class OCM_Fulfillment_Model_Warehouse_Ingram extends OCM_Fulfillment_Model_Warehouse_Abstract
{

    const INGRAM_SKU_ATTR = 'ingram_micro_usa';
    const INGRAM_PRICE_ATTR = 'cust_cost';
    const INGRAM_QTY_ATTR = 'avail_stock_flag';
    const INGRAM_PRICE_NODE = 'price';
    
    protected $_collection;

    public function _construct() {
        parent::_construct();
    }
    
    protected function _getQty($item) {
        
        $qty = 0;
        
        // add up all warehouse data for complete qty
        foreach ($item->branch as $warehouse) {
            $qty += (int) $warehouse->availability;
        }
        return $qty;
        
    }
    
    protected function _getRequest($xml) {
    
    	echo $xml;

        $content_length=strlen($xml);
 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_URL, 'https://newport.ingrammicro.com'); 
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $ch_result = curl_exec($ch);
        
        curl_close($ch);
		print_r($ch_result);
		die();
        return $ch_result;
    }

    protected function _getHeader() {
        return '
        <TransactionHeader>
        	<SenderID>MD</SenderID>
			<ReceiverID>YOU</ReceiverID>
			<CountryCode>MD</CountryCode>
			<LoginID>r7yebe4ewR</LoginID>
			<Password>SpeJa2reC3</Password>
			<TransactionID>12345</TransactionID>
		</TransactionHeader>
        ';
    }
    
    protected function _loadProduct($sku) {
        
        
        if($this->getData($sku)) return;

        //flush data if too many products are stored
        if(count($this->getData()) > 100) $this->reset();
        
        $product = Mage::getModel('catalog/product');
        $id = $product->getIdBySku($sku);
        $product->load($id);
       
        $xml_builder = '<PNARequest> <Version>2.0</Version>';
        $xml_builder .= $this->_getHeader();
        $xml_builder .='
        <PNAInformation SKU="' . $product->getData(self::INGRAM_SKU_ATTR) . '" Quantity="1"/>
        </PNARequest>
        ';  

        $result = $this->_getRequest($xml_builder);
        $xml = new SimpleXMLElement($result);

        $this->setData($sku,$xml->pnaresponse->priceandavailability);
        return $this;
    }
    
    public function loadCollectionArray($collection) {

        $collection->addAttributeToSelect(self::INGRAM_SKU_ATTR);
        $cloned_collection = clone $collection;
        
        $xml_builder = '<PNARequest>';
        $xml_builder .= $this->_getHeader();
        //$xml_builder .= '<Detail>';
         
        foreach ($cloned_collection as $product) {
            if($ingram_sku = $product->getData(self::INGRAM_SKU_ATTR)) {
                
                $this->_collection[$ingram_sku] = array('id'=>$product->getId(),'price'=>'','qty'=>'');
            
                $xml_builder .= '<PNAInformation ManufacturerPartNumber="' . $ingram_sku . '" Quantity="1"/>';
            }
        }
          
        $xml_builder .= '
   
        </PNARequest>
        ';  

        try {
            //Mage::log($xml_builder,null,'techdata.log');
            
            $result = $this->_getRequest($xml_builder);
            
            //Mage::log($result,null,'techdata.log');
            
            $xml = new SimpleXMLElement($result);
            
            foreach($xml->priceandavailability as $item) {
                $ingram_sku = (string) $item->attributes()->sku;
                $this->_collection[ $ingram_sku ]['price'] = (string) str_replace('$','',$item->{ self::INGRAM_PRICE_NODE });
                $this->_collection[ $ingram_sku ]['qty'] = $this->_getQty($item);
            }
            
        } catch (Exception $e) {
            
            Mage::log($e->getMessage(),null,'techdata.log');
            
        }
        

        $this->setData('collection_array',$this->_collection);
        return $this;
        
    }

    public function getQty($sku){
        
        $this->_loadProduct($sku);
        
        if(isset($this->getData($sku)->branch->availability)) {
            return $this->_getQty($this->getData($sku));
        }
        return 0;
    }

    public function getPrice($sku){
        
        $this->_loadProduct($sku);

        if(isset($this->getData($sku)->{ self::INGRAM_PRICE_NODE })) {
        
            return preg_replace('/[\$,]/', '', (string) $this->getData($sku)->{ self::INGRAM_PRICE_NODE });
        }
        return;
    }
    
    
}
