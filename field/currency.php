<?php
	/**
	 * @package     ${NAMESPACE}
	 * @subpackage
	 *
	 * @copyright   A copyright
	 * @license     A "Slug" license name e.g. GPL2
	 */
	
	defined('JPATH_BASE') or die;
	
	use Joomla\Utilities\ArrayHelper;
	
	JFormHelper::loadFieldClass('List');
	
	class JFormFieldCurrency extends JFormFieldList
	{
		/**
		 * The form field type.
		 *
		 * @var		string
		 * @since	3.7.0
		 */
		public $type = 'Currency';
		/**
		 * Method to get a list of options for a list input.
		 *
		 * @return	array  An array of JHtml options.
		 *
		 * @since   3.7.0
		 */
		protected function getInput()
		{
			
			$all_currency = JSFactory::getAllCurrency(); // array currency objects
			
			
			if( $this->name == 'jform[params][currency_default]' )
			{
				$_default = [
					'currency_code' => 'Нет' ,
					'currency_id' => ''
				];
				array_unshift($all_currency, $_default );
				
			}#END IF
			
			$lists = JHTML::_('select.genericlist'
				, $all_currency
				, $this->name
				,'class = "inputbox"'
				,'currency_id'
				,'currency_code'
				, $this->value
			);
			
			/*echo'<pre>';print_r( $this->value );echo'</pre>'.__FILE__.' '.__LINE__;
			echo'<pre>';print_r( $all_currency );echo'</pre>'.__FILE__.' '.__LINE__;
			die(__FILE__ .' '. __LINE__ );*/
			/*
			
			echo'<pre>';print_r( $all_currency );echo'</pre>'.__FILE__.' '.__LINE__;
			die(__FILE__ .' '. __LINE__ );
			
			$options = [ 1,2,3 ] ;
			$options = array_merge(parent::getOptions(), $options);*/
			return $lists;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	