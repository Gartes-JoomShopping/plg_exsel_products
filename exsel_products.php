<?php


/**
* @package Joomla
* @subpackage JoomShopping
; @author Nevigen.com
* @website https://nevigen.com/
* @email support@nevigen.com
* @copyright Copyright © Nevigen.com. All rights reserved.
* @license Proprietary. Copyrighted Commercial Software
* @license agreement https://nevigen.com/license-agreement.html
**/

defined('_JEXEC') or die;
	
	error_reporting( E_ALL );
	
	

class plgJshoppingAdminExsel_Products extends JPlugin {
	
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
	private $similarProducts = array();
	private $post = array();
	private $j3 = false;
	private $addon;
	private $app ;
	private $_AjaxSetting ;
	private $Helper;
	/**
	 * plgJshoppingAdminExsel_Products constructor.
     * @since 3.9
	 */
	/*public function __construct ()
	{
				try
						{
							// Code that may throw an Exception or Error.
//							ooooooooooiiiiooooo();
						}
						catch (Exception $e)
						{
						   // Executed only in PHP 5, will not be reached in PHP 7
						   echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
						   die(__FILE__ .' '. __LINE__ );
						}
						catch (Throwable $e)
						{
							// Executed only in PHP 7, will not match in PHP 5
							echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
							echo'<pre>';print_r( $e );echo'</pre>'.__FILE__.' '.__LINE__;
							die(__FILE__ .' '. __LINE__ );
						}
	}*/
	
	/**
	 * index.php?option=com_jshopping
	 * @param $controller
	 *
	 *
	 * @since version 3.9
	 */
	function onAfterGetControllerAdmin( $controller ) {
		
		// die(__FILE__ .' '. __LINE__ );
	}
	
	function onBeforeDisplayOptionsPanel(&$access) {
//		die(__FILE__ .' '. __LINE__ );
	
		
	}
	
	function onBeforeDisplayInfo(&$access) {
		die(__FILE__ .' '. __LINE__ );
	}
	
	/**
	 * index.php?option=com_jshopping
	 * @param $access
	 * @since version
	 */
	function onBeforeAdminCheckAccessController(&$access) {}
	
	private $AjaxResult = [
	        'updateProductRow' => 0 ,
    ];
	
	private function addFilePrice (   ){
		JLoader::registerNamespace('ExselProducts\Helpers',JPATH_PLUGINS.'/jshoppingadmin/exsel_products/Helpers',$reset=false,$prepend=false,$type='psr4');
		$this->Helper = \ExselProducts\Helpers\HelperProduct::instance();
		$findRes = $this->Helper->getProductListInDb();
		
		if( !count( $findRes ) ) { return $this->AjaxResult ; }#END IF
		$mergeArr = $this->Helper->mergeProduct($findRes) ;
		$this->AjaxResult['updateProductRow'] = count( $mergeArr ) ;
		
		$res = $this->Helper->updateProductPrice( $mergeArr ) ;
		return $this->AjaxResult ;
	}
	
	/**
	 * Точка входа Ajax
	 * @return mixed
	 *
	 * @throws Exception
	 * @since version
	 */
	public function onAjaxExsel_products  (){
		$this->app = \JFactory::getApplication() ;
	    $this->_AjaxSetting = $this->app->input->get('Setting' , [] , 'ARRAY') ;
	    $_method = $this->_AjaxSetting['Plugin']['method'] ;
		try
		{
			$res = $this->{$_method}();
		} catch (Exception $e)
		{
			// Executed only in PHP 5, will not be reached in PHP 7
			echo 'Выброшено исключение: ', $e->getMessage(), "\n";
			echo '<pre>'; print_r( $e ); echo '</pre>' . __FILE__ . ' ' . __LINE__;
			die(__FILE__ .' '. __LINE__ );
		} catch (Throwable $e)
		{
			// Executed only in PHP 7, will not match in PHP 5
			echo 'Выброшено исключение: ', $e->getMessage(), "\n";
			echo '<pre>'; print_r( $e ); echo '</pre>' . __FILE__ . ' ' . __LINE__;
			die(__FILE__ .' '. __LINE__ );
		}
		return $res ;
	}
	/**
	 * Админ панель - список товаров
	 * index.php?option=com_jshopping&controller=products&category_id=0
	 * @param $view
	 * @since version
	 */
	function onBeforeDisplayListProductsView(&$view) {
		   
	    $doc = JFactory::getDocument();
		$bar = JToolBar::getInstance('toolbar');
//		$pars = $this->PluginSetting();
		
		$plugin = \Joomla\CMS\Plugin\PluginHelper::getPlugin('jshoppingadmin', 'exsel_products');
		$Registry = new \Joomla\Registry\Registry();
		$params = $Registry->loadObject( json_decode( $plugin->params )) ;
  
		$step = $params->get('step' , 100 );
		
		$columnSlug = new stdClass() ;
		$fieldNameColumn = $params->get('field-name' , [] ) ;
		$is_price_alias = false ; 
		foreach ( $fieldNameColumn as $item)
		{
		    $index_column = $item->index_column ;
		    $columnSlug->{$index_column} = $item->alias ;
			
			if( $item->is_price )
			{
				$is_price_alias = $item->alias ;
			}#END IF
		    
		    
		}#END FOREACH
		
		$domain = str_replace( '/administrator/' , '' ,  JURI::base());
	  
		$title = JText::_('ADDON_JSHOPPING_EXSEL_PRODUCTS_LOADPRICE'); //Надпись на кнопке
		$dhtml = "<button id='exsel_products-btn' onclick=\"Joomla.submitbutton('exsel_products.loadPrice');\" class=\"btn btn-small button-options\">";
		$dhtml .= "<span class=\"icon-options\" aria-hidden=\"true\"></span>".$title."</button>"; //HTML кнопки
		$bar->appendButton('Custom', $dhtml, 'list');//давляем ее на тулбар
		
		$expCoreSetting = array(
		        'siteUrlsiteUrl' => $domain ,
			    'domain' => $domain ,
		        'elementBtn' => 'exsel_products-btn' ,
        );
		$doc->addScriptOptions('expCoreSetting'  , $expCoreSetting );
		
		$fileUploadCoreSetting = array(
			'siteUrlsiteUrl' => $domain ,
			'domain' => $domain ,
			'elementBtn' => 'exsel_products-btn' ,
		);
		$doc->addScriptOptions('fileUploadCoreSetting'  , $fileUploadCoreSetting );
		
		
		$fileUploadCoreSetting = json_encode( [
			'DEBAG' => true,
			'Plugin' => [
			        'gorup' => 'jshoppingadmin' ,
                    'name' => 'exsel_products' ,
                    'method' => ''
            ],
			'window' => [
				'head' => 'Загрузить файлы',
			],
			'domain' => $domain,
			'urlHandler' => $domain . '/libraries/GNZ11/assets/js/plugins/jQuery/file_upload/server/php/index_upl.php',
            'upload_dir' => '/price/' ,
            'upload_url' => $domain . '/price/' ,
			'worksheet' => [
				'step' => $step ,
				'columnSlug' => $columnSlug ,
				'is_price_alias' => $is_price_alias ,
				'manufacturer_code_rewrite' => $params->get('manufacturer_code_rewrite' , 0 ) ,
			]
        ]);
		$doc->addScriptDeclaration('
		    window.fileUploadCoreSetting = '.$fileUploadCoreSetting.'
		');
		$doc->addScriptDeclaration("Joomla.submitbutton = function(pressbutton) {
            if(pressbutton=='exsel_products.loadPrice'){
                if( typeof window.expCore === 'undefined'  ){
                    var url = '".$domain."/plugins/jshoppingadmin/exsel_products/asset/js/core.js';
                    var script = document.createElement('script')
                    script.type = 'text/javascript';
                    script.src = url;
                    document.getElementsByTagName('head')[0].appendChild(script);
                }
            }else{
                document.adminForm.task.value=pressbutton;
                submitform(pressbutton);
            }
        }");
	
	}
	
	/**
     * Страница товара
	 * @param $view
	 *
	 *
	 * @since version
	 */
	function onBeforeDisplayEditProductView(&$view) {
//		die(__FILE__ .' '. __LINE__ );
		if ($view->product->parent_id != 0 || !$view->product->product_id){
			return;
		}
		
		$db = JFactory::getDBO();
		$lang = JSFactory::getLang();
		$query = 'SELECT similar.product_similar_id AS product_id, prod.`'.$lang->get('name').'` AS name, prod.'.($this->j3 ? 'image' : 'product_thumb_image').' AS image 
				FROM `#__jshopping_products_similar` AS similar
				LEFT JOIN `#__jshopping_products` AS prod ON prod.product_id=similar.product_similar_id
				WHERE similar.product_id='.$view->product->product_id.' order by similar.id';
		$db->setQuery($query);
		$this->similarProducts = $db->loadObjectList();
	}
	
	/**
     * Страница товара
	 * @param $row
	 * @param $lists
	 * @param $tax_value
	 *
	 *
	 * @since version
	 */
	function onDisplayProductEditTabsEndTab(&$row, &$lists, &$tax_value) {
		
		if (!$this->addon->enable) {
			return;
		}
?>
	    <li><a href="#product_similar" data-toggle="tab"><?php echo JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS') ?></a></li>
<?php
	}
	
	/**
     * Страница товара
	 * @param $pane
	 * @param $row
	 * @param $lists
	 * @param $tax_value
	 * @param $currency
	 *
	 *
	 * @since version
	 */
	function onDisplayProductEditTabsEnd(&$pane, &$row, &$lists, &$tax_value, &$currency) {
		
		if (!$this->addon->enable) {
			return;
		}
		$jshopConfig = JSFactory::getConfig();
		if ($this->j3) {
			echo '<div id="product_similar" class="tab-pane">';
		} else {
			echo $pane->startPanel(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS'), 'product_similar');
		}
?>
	<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS') ?></legend>
		<div id="list_similar">
		<?php
			foreach($this->similarProducts as $row_similar){
				if (!$row_similar->image) $row_similar->image = $jshopConfig->noimage;
			?>      
			<div class="block_related" id="similar_product_<?php print $row_similar->product_id;?>">
				<div class="block_related_inner">
					<div class="name"><?php echo $row_similar->name;?> (ID:&nbsp;<?php print $row_similar->product_id?>)</div>
					<div class="image"><a href="index.php?option=com_jshopping&controller=products&task=edit&product_id=<?php print $row_similar->product_id;?>"><img src="<?php print $jshopConfig->image_product_live_path."/".$row_similar->image?>" width="90" border="0" /></a></div>
					<div style="padding-top:5px;"><input type="button" value="<?php print _JSHOP_DELETE;?>" onclick="similarProducts.delete(<?php print $row_similar->product_id;?>)"></div>
					<input type="hidden" name="similar_products[]" value="<?php print $row_similar->product_id;?>"/>
				</div>
			</div>
			<?php
			}
		?>
		</div>
	</fieldset>
	</div>
	<div class="clr"></div>
	<?php $pkey = 'plugin_template_similar'; if ($this->$pkey){ print $this->$pkey;}?>
	<br/>
	<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo _JSHOP_SEARCH ?></legend>
		<div>
			<input type = "text" size="35" id = "similar_search" value = "" />
			&nbsp;
			<input type = "button" class = "button" value = "<?php echo _JSHOP_SEARCH;?>" onclick="similarProducts.search(0, '<?php echo $row->product_id?>');" />
		</div>
		<br/>
		<div id="list_for_select_similar"></div>
	</fieldset>
	</div>
	<div class="clr"></div>
<?php
		if ($this->j3) {
			echo '</div>';
		} else {
			echo $pane->endPanel();
		}
	}

	function onBeforeDisplaySaveProduct(&$post, &$product) {
		$this->post = $post;
        if (!isset($this->post['similar_products'])) {
			$this->post['similar_products'] = array();
		} else {
			$this->post['similar_products'] = array_unique($this->post['similar_products']);
		}
	}

	function onAfterSaveProductEnd($product_id) {
		$db = JFactory::getDBO();
		
		if ($this->post['edit']) {
			$query = 'DELETE FROM `#__jshopping_products_similar` WHERE product_id = '.$product_id;
			$db->setQuery($query);
			$db->execute();
		}
		
		$values = array();
		foreach($this->post['similar_products'] as $similar_product_id){
			if ($similar_product_id != 0) {
				$values[] = '('.$product_id.','.$similar_product_id.')';
			}
		}
		if (count($values)) {
			$query = 'INSERT INTO `#__jshopping_products_similar` (product_id, product_similar_id) VALUES'.
					implode(',', $values);
			$db->setQuery($query);
			$db->execute();
		}
	}

	function onAfterRemoveProduct(&$cid) {
		die(__FILE__ .' '. __LINE__ );
		$db = JFactory::getDBO();

		foreach($cid as $key => $value){
			$product_id = $db->escape($value);
			$query = 'DELETE FROM `#__jshopping_products_similar` WHERE product_id='.$product_id.' OR product_similar_id='.$product_id;
			$db->setQuery($query);
			$db->execute();
		}
	}

	

	private function _getExtraCondition($control, $where) {
		if ($control['only_published']) {
			$where .= ' AND prod.product_publish = 1';
		}
		if ($control['only_quantity']) {
			$where .= ' AND prod.product_quantity > 0';
		}
		if ($control['only_price']) {
			$where .= ' AND prod.product_price > 0';
		}
		if ($control['only_image']) {
			$where .= ' AND prod.'.($this->j3 ? 'image' : 'product_thumb_image').' <> ""';
		}
		
		return $where;
	}

	private function _getTargetProductsCharacteristic($cid, $control, $tableName, $columnName, $cond) {
		$db = JFactory::getDBO();
		$jshopConfig = JSFactory::getConfig();

		$where = $this->_getExtraCondition($control, ' WHERE 1 ');

		$characteristicSelect = array();
		foreach ($control['characteristic_select'] as $value) {
			$characteristicSelect[] = 'prod.extra_field_'.$value;
		}

		$each = true;
		$eachArr = array();
		$join = $target = '';
		if ($control['products_from'] == 'same_category') {
			$target = 'pr_cat.category_id AS target_id, ';
			$join .= ' LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id) ';
			$where .= ' AND pr_cat.category_id IN (%replaceTarget%)';
		} else if ($control['products_from'] == 'same_manufacturer') {
			$target = 'prod.product_manufacturer_id AS target_id, ';
			$where .= ' AND prod.product_manufacturer_id IN (%replaceTarget%)';
		} else if ($control['products_from'] == 'same_vendor') {
			$target = 'prod.vendor_id AS target_id, ';
			$where .= ' AND prod.vendor_id IN (%replaceTarget%)';
		} else {
			$select = $control[$control['products_from']];
			if (!is_array($select) || !count($select)) {
				JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_FROM_'.$control['products_from']), 'error');
				return false;
			}
			$each = false;
			$joinTarget = '';
			if ($control['products_from'] == 'select_category') {
				$joinTarget .= ' LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id) ';
				$where .= ' AND pr_cat.category_id IN ('.implode(',', $select).')';
			} else if ($control['products_from'] == 'select_manufacturer') {
				$where .= ' AND prod.product_manufacturer_id IN ('.implode(',', $select).')';
			} else if ($control['products_from'] == 'select_vendor') {
				$where .= ' AND prod.vendor_id IN ('.implode(',', $select).')';
			}
			
			$query = 'SELECT DISTINCT prod.product_id, '.implode(', ', $characteristicSelect).' FROM `#__jshopping_products` AS prod '
					.$joinTarget
					.$where;
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			
			$characteristicTarget = array();
			foreach ($rows as $row) {
				$characteristicTarget[$row->product_id] = array();
				foreach ($control['characteristic_select'] as $value) {
					$extra_field = 'extra_field_'.$value;
					if ($row->$extra_field != '') {
						$characteristicTarget[$row->product_id][$value] = $row->$extra_field;
					}
				}
			}
		}
		$query = 'SELECT prod.product_id, '.$target.implode(', ', $characteristicSelect).' FROM `#__jshopping_products` AS prod '
				.$join.
				'WHERE prod.product_id IN ('.implode(',', $cid).')';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$characteristicCid = $eachTarget = $eachProduct = array();
		foreach ($rows as $row) {
			if ($each) {
				$eachTarget[$row->target_id] = $row->target_id;
				if (!isset($eachProduct[$row->product_id])) {
					$eachProduct[$row->product_id] =  array();
				}
				$eachProduct[$row->product_id][] = $row->target_id;
			}
			$characteristicCid[$row->product_id] = array();
			foreach ($control['characteristic_select'] as $value) {
				$extra_field = 'extra_field_'.$value;
				if ($row->$extra_field != '') {
					$characteristicCid[$row->product_id][$value] = $row->$extra_field;
				}
			}
		}

		$ret = array();
		if ($each) {
			$where = str_replace('%replaceTarget%', implode(',', $eachTarget), $where);
			$query = 'SELECT prod.product_id, '.$target.implode(', ', $characteristicSelect).' FROM `#__jshopping_products` AS prod '
					.$join
					.$where;
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$eachTarget = array();
			foreach ($rows as $row) {
				if (!isset($eachTarget[$row->target_id])) {
					$eachTarget[$row->target_id] =  array();
				}
				if (!isset($eachTarget[$row->target_id][$row->product_id])) {
					$eachTarget[$row->target_id][$row->product_id] =  array();
				}
				foreach ($control['characteristic_select'] as $value) {
					$extra_field = 'extra_field_'.$value;
					if ($row->$extra_field != '') {
						$eachTarget[$row->target_id][$row->product_id][$value] = $row->$extra_field;
					}
				}
			}

			foreach ($characteristicCid as $product_id=>$product_characteristics) {
				$countProductCharacteristics = count($product_characteristics);
				if (!$countProductCharacteristics) {
					continue;
				}
				if (isset($eachProduct[$product_id])) {
					$characteristicTarget = array();
					foreach ($eachProduct[$product_id] as $target_id) {
						$characteristicTarget += $eachTarget[$target_id];
					}
					$ret[$product_id] = array();
					foreach ($characteristicTarget as $target_id=>$target_characteristics) {
						$result_array = array_intersect_assoc($product_characteristics, $target_characteristics);
						if ($cond == 'and') {
							if ($countProductCharacteristics == count($result_array)) {
								$ret[$product_id][$target_id] = $target_id;
							}
						} else if (count($result_array)) {
							$ret[$product_id][$target_id] = $target_id;
						}
					}
					shuffle($ret[$product_id]);
				}
			}
		} else {
			foreach ($characteristicCid as $product_id=>$product_characteristics) {
				$countProductCharacteristics = count($product_characteristics);
				if (!$countProductCharacteristics) {
					continue;
				}
				$ret[$product_id] = array();
				foreach ($characteristicTarget as $target_id=>$target_characteristics) {
					$result_array = array_intersect_assoc($product_characteristics, $target_characteristics);
					if ($cond == 'and') {
						if ($countProductCharacteristics == count($result_array)) {
							$ret[$product_id][$target_id] = $target_id;
						}
					} else if (count($result_array)) {
						$ret[$product_id][$target_id] = $target_id;
					}
				}
				shuffle($ret[$product_id]);
			}
		}

		return $ret;
	}

	private function _getTargetProductsPrice($cid, $control, $tableName, $columnName) {
		$db = JFactory::getDBO();
		$jshopConfig = JSFactory::getConfig();

		$allCurrencies = array();
		$rows = JModelLegacy::getInstance('Currencies', 'JshoppingModel')->getAllCurrencies(0);
		foreach ($rows as $currency) {
			$allCurrencies[$currency->currency_id] = $currency->currency_value;
		}

		$where = $this->_getExtraCondition($control, ' WHERE 1 ');

		$each = true;
		$eachArr = array();
		$join = $target = '';
		if ($control['products_from'] == 'same_category') {
			$target = 'pr_cat.category_id AS target_id, ';
			$join .= ' LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id) ';
			$where .= ' AND pr_cat.category_id IN (%replaceTarget%)';
		} else if ($control['products_from'] == 'same_manufacturer') {
			$target = 'prod.product_manufacturer_id AS target_id, ';
			$where .= ' AND prod.product_manufacturer_id IN (%replaceTarget%)';
		} else if ($control['products_from'] == 'same_vendor') {
			$target = 'prod.vendor_id AS target_id, ';
			$where .= ' AND prod.vendor_id IN (%replaceTarget%)';
		} else {
			$select = $control[$control['products_from']];
			if (!is_array($select) || !count($select)) {
				JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_FROM_'.$control['products_from']), 'error');
				return false;
			}
			$each = false;
			$joinTarget = '';
			if ($control['products_from'] == 'select_category') {
				$joinTarget .= ' LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id) ';
				$where .= ' AND pr_cat.category_id IN ('.implode(',', $select).')';
			} else if ($control['products_from'] == 'select_manufacturer') {
				$where .= ' AND prod.product_manufacturer_id IN ('.implode(',', $select).')';
			} else if ($control['products_from'] == 'select_vendor') {
				$where .= ' AND prod.vendor_id IN ('.implode(',', $select).')';
			}
			
			$query = 'SELECT DISTINCT prod.product_id, prod.product_price, prod.currency_id FROM `#__jshopping_products` AS prod '
					.$joinTarget
					.$where;
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			
			$priceTarget = array();
			foreach ($rows as $row) {
				if (isset($allCurrencies[$row->currency_id]) && $allCurrencies[$row->currency_id]) {
					$priceTarget[$row->product_id] = round($row->product_price / $allCurrencies[$row->currency_id], $jshopConfig->decimal_count);
				}
			}
		}
		$query = 'SELECT prod.product_id, prod.product_price, '.$target.'prod.currency_id FROM `#__jshopping_products` AS prod '
				.$join.
				'WHERE prod.product_id IN ('.implode(',', $cid).')';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$priceCid = $eachTarget = $eachProduct = array();
		foreach ($rows as $row) {
			if ($each) {
				$eachTarget[$row->target_id] = $row->target_id;
				if (!isset($eachProduct[$row->product_id])) {
					$eachProduct[$row->product_id] =  array();
				}
				$eachProduct[$row->product_id][] = $row->target_id;
			}
			if (isset($allCurrencies[$row->currency_id]) && $allCurrencies[$row->currency_id]) {
				$priceCid[$row->product_id] = round($row->product_price / $allCurrencies[$row->currency_id], $jshopConfig->decimal_count);
			}
		}
		if ($control['price']['type']=='less' || $control['price']['type']=='less_equal') {
			arsort($priceCid);
		} else {
			asort($priceCid);
		}
		$ret = array();
		if ($each) {
			$where = str_replace('%replaceTarget%', implode(',', $eachTarget), $where);
			$query = 'SELECT prod.product_id, prod.product_price, '.$target.'prod.currency_id FROM `#__jshopping_products` AS prod '
					.$join
					.$where;
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$eachTarget = array();
			foreach ($rows as $row) {
				if (!isset($eachTarget[$row->target_id])) {
					$eachTarget[$row->target_id] =  array();
				}
				if (isset($allCurrencies[$row->currency_id]) && $allCurrencies[$row->currency_id]) {
					$eachTarget[$row->target_id][$row->product_id] = round($row->product_price / $allCurrencies[$row->currency_id], $jshopConfig->decimal_count);
				}
			}

			foreach ($priceCid as $product_id=>$product_price) {
				if (isset($eachProduct[$product_id])) {
					$priceTarget = array();
					foreach ($eachProduct[$product_id] as $target_id) {
						$priceTarget += $eachTarget[$target_id];
					}
					if ($control['price']['type']=='less' || $control['price']['type']=='less_equal') {
						arsort($priceTarget);
					} else {
						asort($priceTarget);
					}
					if ($control['price']['type']=='equal') {
						$ret[$product_id] = array_keys($priceTarget, $product_price);
						continue;
					}
					$resultTarget = array();
					$num = 0;
					foreach ($priceTarget as $target_price) {
						$num++;
						if (	($control['price']['type']=='less' && $product_price <= $target_price)
							||	($control['price']['type']=='less_equal' && $product_price < $target_price)
							||	($control['price']['type']=='greater' && $product_price >= $target_price)
							||	($control['price']['type']=='greater_equal' && $product_price > $target_price)
						) {
							continue;
						}
						$resultTarget = array_slice($priceTarget,$num-1,null,true);
						break;
					}
					$priceTarget = $resultTarget;
					$ret[$product_id] = array_keys($priceTarget);
				}
			}
			
		} else {
			if ($control['price']['type']=='less' || $control['price']['type']=='less_equal') {
				arsort($priceTarget);
			} else {
				asort($priceTarget);
			}
			foreach ($priceCid as $product_id=>$product_price) {
				if ($control['price']['type']=='equal') {
					$ret[$product_id] = array_keys($priceTarget, $product_price);
					continue;
				}
				$resultTarget = array();
				$num = 0;
				foreach ($priceTarget as $target_price) {
					$num++;
					if (	($control['price']['type']=='less' && $product_price <= $target_price)
						||	($control['price']['type']=='less_equal' && $product_price < $target_price)
						||	($control['price']['type']=='greater' && $product_price >= $target_price)
						||	($control['price']['type']=='greater_equal' && $product_price > $target_price)
					) {
						continue;
					}
					$resultTarget = array_slice($priceTarget,$num-1,null,true);
					break;
				}
				$priceTarget = $resultTarget;
				$ret[$product_id] = array_keys($priceTarget);
			}
		}

		return $ret;
	}

	private function _getTargetProducts($cid, $control, $tableName, $columnName, $where) {
		$db = JFactory::getDBO();
		
		$where = $this->_getExtraCondition($control, $where);

		if ($control['products_from'] == 'select_category' || $control['products_from'] == 'select_manufacturer' || $control['products_from'] == 'select_vendor') {
			$select = $control[$control['products_from']];
			if (!is_array($select) || !count($select)) {
				JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_FROM_'.strtoupper($control['products_from'])), 'error');
				return false;
			}
			$join = '';
			if ($control['products_from'] == 'select_category') {
				$join .= ' LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id)';
				$where .= ' AND pr_cat.category_id IN ('.implode(',', $select).')';
			} else if ($control['products_from'] == 'select_manufacturer') {
				$where .= ' AND prod.product_manufacturer_id IN ('.implode(',', $select).')';
			} else if ($control['products_from'] == 'select_vendor') {
				$where .= ' AND prod.vendor_id IN ('.implode(',', $select).')';
			}
			
			$query = 'SELECT DISTINCT prod.product_id FROM `#__jshopping_products` AS prod '
					.$join
					.$where;
			$db->setQuery($query);
			$columns = $db->loadColumn();
			shuffle($columns);
			return $columns;
		} else {
			$join = '';
			if ($control['products_from'] == 'same_category') {
				$result = 'pr_cat.category_id';
				$join .= ' LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id) ';
			} else if ($control['products_from'] == 'same_manufacturer') {
				$result = 'prod.product_manufacturer_id';
			} else if ($control['products_from'] == 'same_vendor') {
				$result = 'prod.vendor_id';
			}
			$query = 'SELECT DISTINCT '.$result.' AS target_id FROM `#__jshopping_products` AS prod '
					.$join
					.'WHERE prod.product_id IN ('.implode(',', $cid).')';
			$db->setQuery($query);
			$rows = $db->loadColumn();
			
			if (!$rows || !is_array($rows) || !count($rows)) {
				$rows = array(0);
			}
			$where .= ' AND '.$result.' IN ('.implode(',', $rows).')';
			
			$query = 'SELECT prod.product_id AS product_id, '.$result.' AS target_id FROM `#__jshopping_products` AS prod '
					.$join
					.$where;
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$temp = array();
			foreach ($rows as $row) {
				$temp[$row->target_id][] = $row->product_id;
			}
			
			$query = 'SELECT prod.product_id AS product_id, '.$result.' AS target_id FROM `#__jshopping_products` AS prod '
					.$join
					.'WHERE prod.product_id IN ('.implode(',', $cid).')';
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			
			$array = array();
			foreach ($rows as $row) {
				if (isset($temp[$row->target_id]) && is_array($temp[$row->target_id])) {
					$columns = $temp[$row->target_id];
					shuffle($columns);
					foreach ($columns as $value) {
						$array[$row->product_id][] = $value;
					}
				}
			}
			
			return $array;
		}
	}

	private function _getCid($control) {
		$select = $control['source_'.$control['products_source']];
		if (!is_array($select) || !count($select)) {
			JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_SOURCE_'.strtoupper($control['products_source'])), 'error');
			return false;
		}
		$join = '';
		$where = '';
		if ($control['products_source'] == 'select_category') {
			$join .= ' LEFT JOIN `#__jshopping_products_to_categories` AS pr_cat USING (product_id)';
			$where .= ' WHERE pr_cat.category_id IN ('.implode(',', $select).')';
		} else if ($control['products_source'] == 'select_manufacturer') {
			$where .= ' WHERE prod.product_manufacturer_id IN ('.implode(',', $select).')';
		} else if ($control['products_source'] == 'select_vendor') {
			$where .= ' WHERE prod.vendor_id IN ('.implode(',', $select).')';
		}
		
		$db = JFactory::getDBO();
		$query = 'SELECT DISTINCT prod.product_id FROM `#__jshopping_products` AS prod '
				.$join
				.$where;
		$db->setQuery($query);
		return $db->loadColumn();
	}

	private function _getExistProducts($cid, $tableName, $columnName) {
		$db = JFactory::getDBO();
		
		$query = 'SELECT product_id, '.$columnName.' AS target_id FROM `'.$tableName.'` WHERE `product_id` IN ('.implode(',', $cid).')';
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$array = array();
		foreach ($rows as $row) {
			$array[$row->product_id][] = $row->target_id;
		}
		
		return $array;
	}

	private function _setProducts($cid, $control) {
		$db = JFactory::getDBO();
		$lang = JSFactory::getLang();

		if ($control['products_type'] == 'similar') {
			$tableName = '#__jshopping_products_similar';
			$columnName = 'product_similar_id';
		} else {
			$tableName = '#__jshopping_products_relations';
			$columnName = 'product_related_id';
		}
		$controlData = $control[$control['control_type']];
		
		$existProducts = $this->_getExistProducts($cid, $tableName, $columnName);

		if ($control['products_from'] == 'select_category' || $control['products_from'] == 'select_manufacturer' || $control['products_from'] == 'select_vendor') {
			$each = false;
		} else {
			$each = 1;
		}

		if ($control['control_type'] == 'price' || $control['control_type'] == 'characteristic') {
			$number = (int)trim($controlData['number']);
			if ($number <= 0) {
				JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_NUMBER'), 'error');
				return false;
			}
			if ($control['control_type'] == 'price') {
				$targetProducts = $this->_getTargetProductsPrice($cid, $control, $tableName, $columnName);
			} else {
				if (!isset($control['characteristic_select']) || !is_array($control['characteristic_select']) || !count($control['characteristic_select'])) {
					JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_CHARACTERISTIC'), 'error');
					return false;
				}
				$targetProducts = $this->_getTargetProductsCharacteristic($cid, $control, $tableName, $columnName, $controlData['cond']);
			}
			if ($targetProducts === false) {
				return false;
			} else if (!$targetProducts) {
				JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_TARGET'));
				return false;
			}
			$values = array();
			foreach ($targetProducts as $product_id=>$target) {
				$num = 0;
				foreach ($target as $targetProductId) {
					$targetProductId = (int)$targetProductId;
					if (!$targetProductId || $targetProductId == $product_id || (isset($existProducts[$product_id]) && in_array($targetProductId,$existProducts[$product_id]))) {
						continue;
					}
					$num++;
					$values[] = '('.$product_id.','.$targetProductId.')';
					if ($num >= $number) {
						break;
					}
				}
			}
			$countNewProduct = count($values);
			if (!$countNewProduct) {
				JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_NEW'));
				return false;
			}
			$query = 'INSERT INTO `'.$tableName.'` (`product_id`, `'.$columnName.'`) VALUES'.
					implode(',', $values);
			$db->setQuery($query);
			$db->execute();
		} else if ($control['control_type'] == 'remove') {
			$targetCid = trim($controlData);
			if ($targetCid) {
				$where = 'AND '.$columnName.' IN ('.$targetCid.')';
			} else {
				$where = '';
			}
			$query = 'DELETE FROM `'.$tableName.'`
					WHERE `product_id` IN ('.implode(',', $cid).')'.
					$where;
			$db->setQuery($query);
			$db->execute();
		} else {
			$number = (int)trim($controlData['number']);
			if ($control['control_type'] == 'text' || $control['control_type'] == 'ean') {
				$word = trim($controlData['word']);
				if (!$word) {
					JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_'.strtoupper($control['control_type'])), 'error');
					return false;
				}
			} else if ($control['control_type'] == 'label') {
				$select = $controlData['select'];
				if (!$select || !is_array($select) || !count($select)) {
					JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_'.strtoupper($control['control_type'])), 'error');
					return false;
				}
			} else if ($control['control_type'] == 'id')  {
				$targetCid = trim($controlData);
				if ($targetCid) {
					$targetCid = explode(',', $targetCid);
				}
				if (!$targetCid || !is_array($targetCid) || !count($targetCid)) {
					JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_ID'), 'error');
					return false;
				} else {
					array_unique($targetCid);
					$number = count($targetCid);
				}
				$each = 0;
			}
			if ($number <= 0) {
				JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_NUMBER'), 'error');
				return false;
			}
			
			if ($control['control_type'] == 'text') {
				$where = ' WHERE prod.`'.$lang->get('name').'` LIKE "%'.$word.'%"';
			} else if ($control['control_type'] == 'ean') {
				$where = ' WHERE prod.product_ean LIKE "%'.$word.'%"';
			} else if ($control['control_type'] == 'random') {
				$where = ' WHERE prod.product_id <> 0';
			} else if ($control['control_type'] == 'label') {
				$array = array();
				foreach ($select as $label_id) {
					$array[] = 'prod.label_id = '.$label_id;
				}
				$where = ' WHERE ('.implode(' OR ', $array).')';
			}
			
			if ($control['control_type'] == 'id') {
				$targetProducts = $targetCid;
			} else {
				$targetProducts = $this->_getTargetProducts($cid, $control, $tableName, $columnName, $where);
			}
			if ($targetProducts === false) {
				return false;
			} else if (!$targetProducts) {
				JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_TARGET'));
				return false;
			}
			if (!$each) {
				$targetCid = array_unique($targetProducts);
				$targetCount = count($targetCid);
			}
			$values = array();
			foreach ($cid as $product_id) {
				if ($each) {
					if (isset($targetProducts[$product_id]) && is_array($targetProducts[$product_id])) {
						$targetCid = array_unique($targetProducts[$product_id]);
						$targetCount = count($targetCid);
					} else {
						$targetCount = 0;
					}
				}
				if (!$targetCount) {
					continue;
				}
				if ($number < $targetCount) {
					$startPos = rand(0, $targetCount-$number);
					$target = array_slice($targetCid, $startPos, $number);
				} else {
					$target = $targetCid;
				}
				foreach ($target as $targetProductId) {
					$targetProductId = (int)$targetProductId;
					if (!$targetProductId || $targetProductId == $product_id || (isset($existProducts[$product_id]) && in_array($targetProductId,$existProducts[$product_id]))) {
						continue;
					}
					$values[] = '('.$product_id.','.$targetProductId.')';
				}
			}
			$countNewProduct = count($values);
			if (!$countNewProduct) {
				JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_NEW'));
				return false;
			}
			$query = 'INSERT INTO `'.$tableName.'` (`product_id`, `'.$columnName.'`) VALUES'.
					implode(',', $values);
			$db->setQuery($query);
			$db->execute();
		}
		JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_PROCESSED_PRODUCTS').count($cid));
		if ($countNewProduct) {
			JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_ADDED_PRODUCTS').$countNewProduct);
		}
		
		return true;
	}

	function onBeforeEditAddons(&$view) {
		die(__FILE__ .' '. __LINE__ );
		
		if ($this->addon->id != $view->row->id) {
			return;
		}
		$app = JFactory::getApplication();
		if (!$app->input->getInt('similar_products')) {
			return;
		}
		$session = JFactory::getSession();
		$cid = $session->get('cid', null, 'similar_products');
		if (!$cid) {
			$cid = $app->input->get('cid', null, 'array');
		} else {
			$session->clear('cid', 'similar_products');
		}
		if (!is_array($cid) || !count($cid)) {
			$cid = array();
		}
		$view->cid = $cid;
		
		JHTML::_('behavior.tooltip');
		$allProductExtraField = JSFactory::getAllProductExtraField();

		if (is_array($this->control) && count($this->control)) {
			$params = $app->input->get('params', null, 'array');
			$params['control'] = $this->control;
			$view->row->setParams($params);
			$view->row->store();
			if (!count($cid)) {
				$cid = $this->_getCid($this->control);
			}
			if (is_array($cid) && count($cid)) {
				$res = $this->_setProducts($cid, $this->control);
			} else if ($cid) {
				JFactory::getApplication()->enqueueMessage(JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_MESSAGE_NOT_CID'), 'warning');
			}
		} else {
			$this->control = (array)$view->params['control'];
		}
		if (!isset($this->control['products_from'])) {
			$this->control['products_from'] = 'select_category';
		}
		if (!isset($this->control['products_source'])) {
			$this->control['products_source'] = 'select_category';
		}
		if (!isset($this->control['control_type'])) {
			$this->control['control_type'] = 'price';
		}
		if (!isset($this->control['characteristic_select'])) {
			$this->control['characteristic_select'] = array();
			if (count($allProductExtraField)) {
				$this->control['characteristic_select'][] = 0;
			}
		}
		
		$view->addTemplatePath(JPATH_COMPONENT_SITE.'/addons/addon_similar_products');
		$view->setLayout('control');
		$view->control = $this->control;

		$view->productsType = array('similar','related');
		$list = array();
		foreach($view->productsType as $value){
			$list[] = JHTML::_('select.option', $value, JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_PRODUCTS_TYPE_'.strtoupper($value)), 'value', 'text');
		}
		$view->productsTypeList = JHTML::_('select.radiolist', $list, 'control[products_type]','class = "inputbox"','value','text', $this->control['products_type'] ? $this->control['products_type'] : 'similar');

		$view->controlType = array('price','id','text','ean','characteristic','label','random','remove');
		$list = array();
		foreach($view->controlType as $value){
			$list[] = JHTML::_('select.option', $value, JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_CONTROL_TYPE_'.strtoupper($value)), 'value', 'text');
		}
		$view->controlTypeList = JHTML::_('select.genericlist', $list, 'control[control_type]','class = "inputbox" size = "1" onchange="similarProducts.selectType(this.value)"','value','text', $this->control['control_type']);

		$view->productsFrom = array('select_category','select_manufacturer','select_vendor','same_category','same_manufacturer','same_vendor');
		$list = array();
		foreach($view->productsFrom as $value){
			$list[] = JHTML::_('select.option', $value, JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_FROM_'.strtoupper($value)), 'value', 'text');
		}
		$view->productsFromList = JHTML::_('select.genericlist', $list, 'control[products_from]','class = "inputbox" size = "1" onchange="similarProducts.selectFrom(this.value)"','value','text', $this->control['products_from']);

		$view->productsSource = array('select_category','select_manufacturer','select_vendor');
		$list = array();
		foreach($view->productsSource as $value){
			$list[] = JHTML::_('select.option', $value, JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_FROM_'.strtoupper($value)), 'value', 'text');
		}
		$view->productsSourceList = JHTML::_('select.genericlist', $list, 'control[products_source]','class = "inputbox" size = "1" onchange="similarProducts.selectSource(this.value)"','value','text', $this->control['products_source']);

        $list = buildTreeCategory(0,1,0);
        $view->categorysSourceList = JHTML::_('select.genericlist', $list,'control[source_select_category][]','size = "10" multiple="multiple"', 'category_id', 'name', $this->control['source_select_category'] );
        $view->categorysTatgetList = JHTML::_('select.genericlist', $list,'control[select_category][]','size = "10" multiple="multiple"', 'category_id', 'name', $this->control['select_category'] );

        $list = JModelLegacy::getInstance('Manufacturers', 'JshoppingModel')->getAllManufacturers(0, 'name', 'asc');
        $view->manufacturersSourceList = JHTML::_('select.genericlist', $list, 'control[source_select_manufacturer][]','size = "10" multiple="multiple"', 'manufacturer_id', 'name', $this->control['source_select_manufacturer']);
        $view->manufacturersTatgetList = JHTML::_('select.genericlist', $list, 'control[select_manufacturer][]','size = "10" multiple="multiple"', 'manufacturer_id', 'name', $this->control['select_manufacturer']);

        $list = JModelLegacy::getInstance('Vendors', 'JshoppingModel')->getAllVendorsNames(true);
        $view->vendorsSourceList = JHTML::_('select.genericlist', $list, 'control[source_select_vendor][]','size = "10" multiple="multiple"', 'id', 'name', $this->control['source_select_vendor']);
        $view->vendorsTatgetList = JHTML::_('select.genericlist', $list, 'control[select_vendor][]','size = "10" multiple="multiple"', 'id', 'name', $this->control['select_vendor']);

        $list = JModelLegacy::getInstance('ProductLabels', 'JshoppingModel')->getList();
        $view->labelsList = JHTML::_('select.genericlist', $list, 'control[label][select][]','size = "4" multiple="multiple"', 'id', 'name', $this->control['label']['select']);

		$view->priceType = array('equal','greater','greater_equal','less','less_equal');
		$list = array();
		foreach($view->priceType as $value){
			$list[] = JHTML::_('select.option', $value, JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_PRICE_TYPE_'.strtoupper($value)), 'value', 'text');
		}
		$view->priceTypeList = JHTML::_('select.genericlist', $list, 'control[price][type]','class = "inputbox" size = "1"','value','text', $this->control['price']['type']);

		$view->characteristicCond = array('and','or');
		$list = array();
		foreach($view->characteristicCond as $value){
			$list[] = JHTML::_('select.option', $value, JText::_('ADDON_JSHOPPING_SIMILAR_PRODUCTS_CONTROL_TYPE_CHARACTERISTIC_COND_'.strtoupper($value)), 'value', 'text');
		}
		$view->characteristicCondList = JHTML::_('select.genericlist', $list, 'control[characteristic][cond]','class = "inputbox" size = "1"','value','text', $this->control['characteristic']['cond']);
		$view->characteristicSelect = array();
		foreach($this->control['characteristic_select'] as $value){
			$view->characteristicSelect[] = JHTML::_('select.genericlist', $allProductExtraField, 'control[characteristic_select][]','class = "inputbox" size = "1"','id','name', $value);
		}
		
	}

}