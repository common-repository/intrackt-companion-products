<?php
namespace Intrackt\CompanionProducts;

/*
 * load the source for any required classes or files
 */
require_once( INTRACKT_COMPANIONPRODUCTS_PLUGIN_DIR . 'class.Intrackt-Companion-Products-PageLog.php' );
require_once( INTRACKT_COMPANIONPRODUCTS_PLUGIN_DIR . 'class.Intrackt-Companion-Products-Actions.php' );
require_once(\ABSPATH.'/wp-admin/includes/template.php');

/*
 * The PageMain class defines and processes the CompanionProducts main page
 */
class PageMain {

    /*
     * Have we instantiated the class-- this is a singleton and does not produce children
     */
	private static $initiated = false;
    
    /*
     * Init class
     */
	public static function init() {
        
        //PageLog::updateTestLog("PageMain::init executed");
        
		if ( ! self::$initiated ) {
    		self::$initiated = true;

		}
        
	}
    
    /*
     * display some html in the section
     */
    public static function page01Section01Text() {

        ?>
        <h3 style='font-size: 1.15em;'>Main Settings</h3>
        <?php
    
    }
    
    /*
     * validate input values
     */
    public static function optionsValidate($input) {

        /*
         * Get the options
         */
        $options = \get_option('intrackt_companionproducts');
        
        $option=\trim($input['intrackt_companionproducts_companionprice']);
        if (in_array($option,array('show','zero','hide'))) {
            $options['companionprice'] =$option;
        }
        
        $option=\trim($input['intrackt_companionproducts_keepcompanion']);
        if (in_array($option,array(0,1))) {
            $options['keepcompanion'] =$option;
        }
        
        $option=\trim($input['intrackt_companionproducts_duplicateskus']);
        if (in_array($option,array(0,1))) {
            $options['duplicateskus'] =$option;
        }
        
        $option=\trim($input['intrackt_companionproducts_companioncategory']);
        $categories = apply_filters('intrackt_companionproducts_possiblecompanioncategories',get_terms('product_cat', array('hide_empty' => false)));        
        foreach ($categories as $category) {
            if ($category->slug==$option) {
                $options['companioncategory']=$option;
                break;
            }
        }

        
        /*
         * update options
         */
        \update_option('intrackt_companionproducts',$options);

    }

    /*
     * What to do with the companion product price
     */
    public static function optionsCompanionPrice() {
    
        /*
         * create the input widget
         */
        $options = \get_option('intrackt_companionproducts');
        echo "<select id='intrackt_companionproducts_companionprice' name='intrackt_companionproducts_options[intrackt_companionproducts_companionprice]' style='width: 260px;'>".
                "<option value='show' ".(($options['companionprice']=='show')?'selected ':'').">Show Price</option>".
                "<option value='zero' ".(($options['companionprice']=='zero')?'selected ':'').">Set price in order to zero</option>".
                "<option value='hide' ".(($options['companionprice']=='hide')?'selected ':'').">Set price in order to zero and hide it</option>".
            "</select>";

    }

    /*
     * What to do with the companion when copying a product
     */
    public static function optionsKeepCompanion() {
    
        /*
         * create the input widget
         */
        $options = \get_option('intrackt_companionproducts');
        echo "<select id='intrackt_companionproducts_keepcompanion' name='intrackt_companionproducts_options[intrackt_companionproducts_keepcompanion]' style='width: 260px;'>".
                "<option value='0' ".(($options['keepcompanion']=='0')?'selected ':'').">Do not copy companion product</option>".
                "<option value='1' ".(($options['keepcompanion']=='1')?'selected ':'').">Copy companion product</option>".
            "</select>";

    }

    /*
     * Are duplicate skus permitted
     */
    public static function optionsDuplicateSkus() {
    
        /*
         * create the input widget
         */
        $options = \get_option('intrackt_companionproducts');
        echo "<select id='intrackt_companionproducts_duplicateskus' name='intrackt_companionproducts_options[intrackt_companionproducts_duplicateskus]' style='width: 260px;'>".
                "<option value=0 ".(($options['duplicateskus']==0)?'selected ':'').">No</option>".
                "<option value=1 ".(($options['duplicateskus']==1)?'selected ':'').">Yes</option>".
            "</select>";

    }
    
    private static function buildHierachy(&$categories,$prefix,&$hierachy,$parentId) {
        
        /*
         * loop through all categories
         */
        foreach ($categories as $i=>$category) {
            
            // look at only those matching the parent
            if ($category->parent == $parentId) {
                
                // add this one to the hierarchy
                $hierachy[strtolower($prefix.$category->name)]=array(
                    'name'=>$prefix.$category->name,
                    'slug'=>$category->slug
                    );

                // update the prefix
                $newPrefix = apply_filters('intrackt_companionproducts_possiblecategoriesdisplay',$prefix.$category->name.'->',$prefix,$category->name);        
                
                // remember my id
                $myId=$category->term_id;
                
                // remove me from the list
                unset($categories[$i]);
                
                // process my children
                self::buildHierachy($categories,$newPrefix,$hierachy,$myId);
                
            }
        }
    }

    /*
     * case insensitive compate
     */
    //public static function compareLower($a,$b) {
    //    return strcasecmp($a,$b);
    //}

    /*
     * What to do with the companion when copying a product
     */
    public static function optionsCompanionCategory() {
    
        /*
         * get the currently selected option
         */
        $options = \get_option('intrackt_companionproducts');
        $catSlug=$options['companioncategory'];
        
        /*
         * get categories, build hierachy, and sort results
         */
        $hierachy=array();
        $categories = apply_filters('intrackt_companionproducts_possiblecompanioncategories',get_terms('product_cat', array('hide_empty' => false)));        
        self::buildHierachy($categories,'',$hierachy,0);
        ksort($hierachy);
        //PageLog::updateTestObjectLog("\$hierachy",$hierachy);
        
        /*
         * create the input widget
         */
        echo "<select id='intrackt_companionproducts_companioncategory' name='intrackt_companionproducts_options[intrackt_companionproducts_companioncategory]' >";
        foreach ($hierachy as $category)
            echo "<option value='{$category['slug']}' ".(($category['slug']==$catSlug)?'selected ':'').">{$category['name']}</option>";
        echo "</select>";

    }

    /*
     * Define Options
     */
    public static function optionsDefine() {
        
        //PageLog::updateTestLog("PageMain::optionsDefine: start");
        
        /*
         * get options and set this one to the default if not set
         */
        $options = \get_option('intrackt_companionproducts');
        if (!array_key_exists('companionprice',$options)) {
            $options['companionprice']='show';
        }
        if (!array_key_exists('keepcompanion',$options)) {
            $options['keepcompanion']='0';
        }
        if (!array_key_exists('duplicateskus',$options)) {
            $options['duplicateskus']=0;
        }
        if (!array_key_exists('companioncategory',$options)) {
            $options['companioncategory']=0;
        }
        \update_option('intrackt_companionproducts',$options);

        /*
         * Define the options form
         */
        \register_setting( 'intrackt_companionproducts_options', 'intrackt_companionproducts_options', array( 'Intrackt\CompanionProducts\PageMain', 'optionsValidate' ) );
        
        \add_settings_section('intrackt_companionproducts_p01s01', '', array( 'Intrackt\CompanionProducts\PageMain', 'page01Section01Text' ), 'intrackt_companionproducts_p01');
        \add_settings_field('intrackt_companionproducts_companionprice', 'Companion product price:', array( 'Intrackt\CompanionProducts\PageMain', 'optionsCompanionPrice' ), 'intrackt_companionproducts_p01', 'intrackt_companionproducts_p01s01');
        \add_settings_field('intrackt_companionproducts_keepcompanion', 'Duplicating product:', array( 'Intrackt\CompanionProducts\PageMain', 'optionsKeepCompanion' ), 'intrackt_companionproducts_p01', 'intrackt_companionproducts_p01s01');
        \add_settings_field('intrackt_companionproducts_duplicateskus', 'Permit duplicate product skus:', array( 'Intrackt\CompanionProducts\PageMain', 'optionsDuplicateSkus' ), 'intrackt_companionproducts_p01', 'intrackt_companionproducts_p01s01');
        \add_settings_field('intrackt_companionproducts_companioncategory', 'Category that defines companion products:', array( 'Intrackt\CompanionProducts\PageMain', 'optionsCompanionCategory' ), 'intrackt_companionproducts_p01', 'intrackt_companionproducts_p01s01');
        
    }

    /*
     * Process page
     */
    public static function processPage() {
        
    }

    /*
     * Display the page
     */
    public static function displayPage() {
        
        ?>

        <div class="wrap">
        <h2 style="display: none">       <h2>
        <h1>Intrackt Companion Products</h1>
        <!--
        - options form
        -->
        <form action="options.php" method="post" id="intrackt_options_form">
            
        <?php

        /*
         * Define and display settings
         */
        \settings_fields('intrackt_companionproducts_options');
        \do_settings_sections('intrackt_companionproducts_p01');
        
        //echo "<p> </p>";
        //echo "<div style='background-color: #f8f8f8; padding: 8px; border-color: black; border-width: 2px; border-style: solid;{$showBooster}'>";
        //\do_settings_sections('intrackt_companionproducts_p02');
        //echo "</div>";

        ?>

        <p> </p>
        <input name="Submit" type="submit" value="<?esc_attr_e('Save Changes','Intrackt'); ?>" />
        </form>
        </div>
        <script>
            /*
             * control all offer options form client-side logic
             */
            document.addEventListener("DOMContentLoaded", processCompanionProductsOptionsForm);
            function processCompanionProductsOptionsForm() {
                
                /*
                 * handle unload with changes made
                 */
                {
                    /*
                     * the form
                     */
                    myForm=document.getElementById('intrackt_options_form');
                    
                    /*
                     * intercept submits so that they do not count
                     */
                    myForm.addEventListener('submit',settingsSubmitted);
                    
                    /*
                     * intercept all changes
                     */
                    formElements=myForm.elements;
                    for (i=0;i<formElements.length;i++)
                        formElements[i].addEventListener('change',handleChanges);
                    
                    /*
                     * the unload handler itself
                     */
                    window.addEventListener("beforeunload",handleUnload);
                }
              2  
            }
            
            /*
             * intercept submit event
             */
            var formSubmitted=false;
            function settingsSubmitted(e) {
                formSubmitted=true;
            }
            
            /*
             * intercept all changes
             */
            var formChanges=false;
            function handleChanges(e) {
                formChanges=true;
            }
            
            /*
             * Handle unload of page
             */
            function handleUnload(e) {
                
                /*
                 * if form being submitted, skip unload message
                 */
                if (formSubmitted) return undefined;
                
                /*
                 * if no changes
                 */
                if (!formChanges) return undefined;
                
                /*
                 * we need to warn the user
                 */
                returnMsg="You have made changes to your settings.  Are you sure you want to abandon those changes?";
                (e||window.event).returnValue=returnMsg;
                return returnMsg;
            }
            
        </script>
        <?php
    }    
    
 }

/*
 * Process any forms on this page
 */
if (isset($_POST['companionproductsaction'])) {
    PageMain::processPage();
}
