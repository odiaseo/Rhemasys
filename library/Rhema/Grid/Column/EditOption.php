<?php
class Rhema_Grid_Column_EditOption extends Rhema_Grid_Option{
	/**
	 * When set for edittype checkbox this value should be a string with two possible values 
	 * separated with a colon (:) - Example editoptions:{value:“Yes:No”} where the first value 
	 * determines the checked property.
	 * When set for edittype select value can be a string, object or function.
	 * If the option is a string it must contain a set of value:label pairs with the
	 *  value separated from the label with a colon (:) and ended with(;). 
	 *  The string should not ended with a (;)- editoptions:{value:“1:One;2:Two”}.
	 * If set as object it should be defined as pair name:value - editoptions:{value:{1:'One';2:'Two'}}
	 * When defined as function - the function should return either formated string or object.
	 * In all other cases this is the value of the input element if defined.
	 * @var string
	 */
	public $value = null;
	/**
	 * This option is valid only for elements of type select - i.e., edittype:select and should 
	 * be the URL to get the AJAX data for the select element. The data is obtained via an 
	 * AJAX call and should be a valid HTML select element with the desired options 
	 * <select><option value='1'>One</option>…</select>. You can use option group.
	 * The AJAX request is called only once when the element is created.
	 * In the inline edit or the cell edit module it is called every time when you edit
	 *  a new row or cell. In the form edit module only once.
	 * @var string
	 */
	public $dataUrl = null;
	/**
	 *This option is relevant only if the dataUrl parameter is set. When the server response 
	 *can not build the select element, you can use your own function to build the select. 
	 *The function should return a string containing the select and options value(s) as 
	 *described in dataUrl option. Parameter passed to this function is the server response
	 * @var unknown_type
	 */
	public $buildSelect = null;
	/**
	 * We pass the element object to this function, if defined. This function is called only 
	 * once when the element is created. Example :
	 * editoptions: { dataInit : function (elem) {
					$(elem).autocomplete();
					}
			}
	 * The event is called only once when the element is created.
	 * In the inline edit or the cell edit module it is called every time when you edit a 
	 * new row or cell. In the form edit module only once if the recreateForm option is set 
	 * to false, or every time if the same option is set to true . 
	 * @var unknown_type
	 */
	public $dataInit = null;
	/**
	 * list of events to apply to the data element; uses $(”#id”).bind(type, [data], fn) to 
	 * bind events to data element. Should be described like this:
	 * … editoptions: { dataEvents: [ { type: 'click', data: { i: 7 }, fn: function(e) { console.log(e.data.i); } },
	 * 								  { type: 'keypress', fn: function(e) { console.log('keypress'); } }
	 *								]
	 *					} 
	 * @var array
	 */
	public $dataEvents = null;
	/**
	 * The option can be string or function. This option is valid only in Form Editing module when
	 *  used with editGridRow method in add mode. If defined the input element is set with this 
	 *  value if only element is empty. If used in selects the text should be provided and not the key. 
	 *  Also when a function is used the function should return value.
	 * @var unknown_type
	 */
	public $defaultValue = null;
 
	
}