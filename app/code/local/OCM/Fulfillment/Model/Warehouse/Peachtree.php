<?php
/*
 * This is were values for internal are returned
 */
class OCM_Fulfillment_Model_Warehouse_Peachtree extends OCM_Fulfillment_Model_Warehouse_Abstract
{	
    public function _construct() {
        parent::_construct();
    }
    
	public function getQty($sku){
		$product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
	

		return (int)$product->getPtQty();
	}
	
    public function getPrice($sku) {
		$product_id=Mage::getModel('catalog/product')->getIdBySku($sku);
		$product = Mage::getModel('catalog/product')->load($product_id);
        return $product->getPtAvgCost();
    }

    public function updatePriceQtyFromCsv($file_path=null, $headers=array()) {
        
        chmod('../media/peachtree.csv',0777);
        //if (!$file_path) {
            $file_path = Mage::getBaseDir() . '/media/peachtree.csv';
        //}
        
        if (!count($headers)) {
            $headers = array(
                'sku',
                'skip',
                'qty',
                'cost',
                'skip'
            ); 
        }
        
        if (($file = fopen($file_path, "r")) == FALSE) {
            Mage::log('count not open file '. $file_path,null,'peachtreeimport.log');
            return false;
        }

        $model = Mage::getModel('catalog/product');
		$stock_model = Mage::getModel('cataloginventory/stock_item');
		
		$count = 0;
        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
            
            $line = array_combine($headers, $data);
            $product_id = $model->getIdBySku( $line['sku'] );
            
            try {
            	$model = Mage::getModel('catalog/product');
            	$stock_model = Mage::getModel('cataloginventory/stock_item');
                $product = $model->load($product_id);
                
                if(!$product_id) {
                    throw new Exception('Failed to load: '.$line['sku']);
                } else {
	                //Mage::log('Loaded: '.$line['sku'] . $product->getId() . " " . $product->getUrlKey(),null,'peachtreeimport.log');
                }
				
				//Update any values older than 23 hours
				
                $target = time() - (60 * 60 * 23);
                Mage::log(strtotime($product->getData('peachtree_updated')) . "->" . $target,null,'peachtreeimport.log');
                $updated = $product->getData('peachtree_updated');
                if (strtotime($updated) > $target && !empty($updated)) {
                	//Mage::log("Skipped -> " . $product->getId() . " -> " . $line['sku'],null,'peachtreeimport.log');
                	continue;
					
				}
				
				$count++;
		
				//Mage::log($line['sku'],null,'peachtreeimport.log');
                $product->setData('peachtree_updated',now());
                $product->setData('pt_qty',$line['qty']);
                $product->setData('pt_avg_cost',$line['cost']);
                
                $subItems = $product->getSubstitutionProducts();
           
	           

				$price_array = array();
	           $qty = 0;
	           
	           // Ingram MUST be the end of the array for this to work
	           foreach (array('techdata','synnex','ingram') as $warehouse_name) {
	           	    
	                   if($product->getData($warehouse_name.'_qty') > 0) {
	                       $price_array[ $product->getData($warehouse_name.'_price') ] = true;
	                       $qty += $product->getData($warehouse_name.'_qty');
	                   }	               
	           }

			   $qty += $line['qty'];
			   if ($line['qty']<1) {
	               ksort($price_array);
	               reset($price_array);
	               $lowest_cost = key($price_array);
	               $product->setData('cost',$lowest_cost);
	           } else {
	               $product->setData('cost',$line['cost']);
	           }
	           $product->setData('peachtree_updated',now());
	           
	           $stock_model->loadByProduct($product->getId());
	           
	           foreach($subItems as $item) {
		       		foreach (array('techdata','synnex','ingram') as $warehouse_name) {	
		       			$prod = Mage::getModel('catalog/product')->load($item->getId());
		       			$qty+=$prod->getData($warehouse_name.'_qty');
		       			//Mage::log("QTY " . $prod->getSku() . '-' . $warehouse_name . ": " . $prod->getData($warehouse_name.'_qty'), null, "fullfillment.log");
		       		}
		       		$qty+=$item->getData('pt_qty');
	           }
			   
			   $stock_model->setData('qty',$qty);
	           if($qty) $stock_model->setData('is_in_stock',1);
           
                $product->save();
                //$stock_model->save();
                
                if ($count > 500) {
                	Mage::log('Break',null,'peachtreeimport.log');
                	break;
                }
                
            } catch (Exception $e) {
               Mage::log($e->getMessage() . $product->getId() . ' ' . $product->getUrlKey(),null,'peachtreeimport.log');
            }
        }
        fclose($file);
        
    }

}
