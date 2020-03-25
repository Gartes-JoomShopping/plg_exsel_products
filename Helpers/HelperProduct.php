<?php
	/**
	 * @package     Helpers
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	namespace ExselProducts\Helpers ;
	
	/**
	 * @package     ExselProducts\Helpers
	 *
	 * @since       version
	 */
	class HelperProduct
	{
		private $app;
		private $db;
		public static $instance;
		/**
		 * helper constructor.
		 * @throws \Exception
		 * @since 3.9
		 */
		private function __construct ( $options = array() )
		{
			$this->app = \JFactory::getApplication();
			$this->db = \JFactory::getDbo() ;
			return $this;
		}#END FN
		/**
		 * @param array $options
		 * @return helper
		 * @throws \Exception
		 * @since 3.9
		 */
		public static function instance ( $options = array() )
		{
			if( self::$instance === null )
			{
				self::$instance = new self( $options );
			}
			return self::$instance;
		}#END FN
		
		/**
		 * Найти товары по имени
		 * @since version
		 */
		public function getProductListInDb (){
			$Setting = $this->app->input->get('Setting' , [] , 'ARRAY') ;
			$SendData = $this->app->input->get('SendData' , [] , 'ARRAY') ;
			
			$query = $this->db->getQuery(true);
			$query->select([
				$this->db->quoteName('product_id') ,
				$this->db->quoteName('name_ru-RU') ,
				$this->db->quoteName('manufacturer_code') ,
				$this->db->quoteName('date_modify') ,
				$this->db->quoteName('product_price') ,
				$this->db->quoteName('min_price') ,
			]);
			$query->from('#__jshopping_products');
			
			$productNameArr = [] ;
			foreach ($SendData as $item)
			{
				$productNameArr[] = $this->db->quote( $item['Наименование'] ) ;
			}#END FOREACH
			$query->where( $this->db->quoteName('name_ru-RU') . ' IN ( '. implode(',' , $productNameArr ).' ) ' ) ;
			$this->db->setQuery($query);
			return $this->db->loadObjectList('name_ru-RU');
			
		}
		
		/**
		 * Обновить цены в массиве товаров
		 * @param $findRes
		 *
		 * @return mixed
		 *
		 * @since version
		 */
		public function mergeProduct($findRes){
			$Setting = $this->app->input->get('Setting' , [] , 'ARRAY') ;
			$SendData = $this->app->input->get('SendData' , [] , 'ARRAY') ;
			$jdata= new \JDate();
			$now = $jdata->toSql();
			
			$is_price_alias = $Setting['worksheet']['is_price_alias'] ;
			$manufacturer_code_rewrite = $Setting['worksheet']['manufacturer_code_rewrite'] ;
			foreach ($SendData as $item)
			{
				$name = $item['Наименование'] ;
				if( !isset( $findRes[$name] ) ) { continue ; }#END IF
				$price = $item[$is_price_alias] ;
				$findRes[$name]->product_price = $price ;
				$findRes[$name]->min_price  = $price ;
				if( $manufacturer_code_rewrite )
				{
					$findRes[$name]->manufacturer_code  = $item['Код'] ;
				}#END IF
				$findRes[$name]->date_modify  = $now ;
				
			}#END FOREACH
			return $findRes ;
		}
		
		public function updateProductPrice ( $prodArr  ){
			
			foreach ( $prodArr as $item)
			{
				$query = $this->db->getQuery(true);
				// Поля для обновления
				$fields = array(
					$this->db->quoteName('manufacturer_code') . ' = ' . $this->db->quote( $item->manufacturer_code ) ,
					$this->db->quoteName('date_modify') . ' = ' . $this->db->quote( $item->date_modify ) ,
					$this->db->quoteName('product_price') . ' = ' . $this->db->quote( $item->product_price ) ,
					$this->db->quoteName('min_price') . ' = ' . $this->db->quote( $item->min_price ) ,
				);
				// Условия обновления
				$conditions = array(
					$this->db->quoteName('product_id') . ' = '  . $this->db->quote($item->product_id)
				);
				$query->update( $this->db->quoteName('#__jshopping_products') )
					->set($fields)
					->where($conditions);
//				echo $query->dump();
				// Устанавливаем и выполняем запрос
				$this->db->setQuery($query);
				$this->db->execute();
			}#END FOREACH
			return true ;
		}
	
		
	}
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	