<?php
namespace Intrackt\CompanionProducts;

/*
 * load the source for any required classes or files
 */
require_once( INTRACKT_COMPANIONPRODUCTS_PLUGIN_DIR . 'class.Intrackt-Companion-Products-PageLog.php' );

/*
 * The common page has the menus for all intrackt plugins
 */
class Common {

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
     * Display the page
     */
    public static function displayPage() {
        
        /*
         * do this page only once
         */
        global $intractCommonPage;
        
        if ($intractCommonPage==1) return;
        $intractCommonPage=1;
        
        //PageLog::updateTestLog("PageMain::displayPage: start");
        
        ?>

        <div class="wrap">
        <h2 style="display: none">       <h2>
        <h1>Welcome to Intrackt</h1>
        <?php
        
        /*
         * get the links for all the installed intrackt plugins
         * sort them, and display them
         */
        $body=apply_filters('intract_commonpage_body',array());
        ksort($body);
        echo "<div style='display: table'>";
        foreach ($body as $bodyItem) echo $bodyItem;
        echo "</div>";
        
        ?>
        </div>
        <?php
    }    
    
    /*
     * Display the page
     */
    public static function displayBody($body) {
        
        ob_start();
        self::newBodyContent();
        $body['companionproducts']=ob_get_clean();
        return $body;

    }
        
    /*
     * Display the page
     */
    public static function newBodyContent() {
        
        $user=wp_get_current_user();
        $userEmail=$user->user_email;
        
        /*
         * get wordpress site base url
         */
        $siteUrl=site_url();
        
        /*
         * output the initial stuff that is always there.
         */
        echo "<div style='display: table-row'>
            <div style='display: table-cell; padding-right: 10px; padding-top: 10px'><b>Intrackt Companion Products</b></div>
            <div style='display: table-cell; padding-right: 10px; padding-top: 10px'><a href='{$siteUrl}/wp-admin/admin.php?page=intracktcompanionproducts-main'><button type='button'>Settings</button></a></div>
            ";

        /*
         * show log only if developer
         */
        if (substr($userEmail,0,11)=='alabakercom') {
            echo "<div style='display: table-cell; padding-right: 10px; padding-top: 10px'><a href='{$siteUrl}/wp-admin/admin.php?page=intracktcompanionproducts-log'><button type='button'>Log</button></a></div>";
        }
        
        /*
         * show the final stuff that is always there
         */
        echo "</div>";
    }
        
}
