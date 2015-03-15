<?php
session_start();
$remove_defaults_widgets = array(
    'dashboard_incoming_links' => array(
        'page'    => 'dashboard',
        'context' => 'normal'
    ),
    'dashboard_right_now' => array(
        'page'    => 'dashboard',
        'context' => 'normal'
    ),
    'dashboard_recent_drafts' => array(
        'page'    => 'dashboard',
        'context' => 'side'
    ),
    'dashboard_quick_press' => array(
        'page'    => 'dashboard',
        'context' => 'side'
    ),
    'dashboard_plugins' => array(
        'page'    => 'dashboard',
        'context' => 'normal'
    ),
    'dashboard_primary' => array(
        'page'    => 'dashboard',
        'context' => 'side'
    ),
    'dashboard_secondary' => array(
        'page'    => 'dashboard',
        'context' => 'side'
    ),
    'dashboard_recent_comments' => array(
        'page'    => 'dashboard',
        'context' => 'normal'
    )
);

$custom_dashboard_widgets = array(
    
    'Customer-Conversion-Tracker-widget ' => array(
        'title' => 'Customer Conversion Tracker',
        'callback' => 'dashboardWidgetContent'
    )
   
);

  function dashboardWidgetContent()
{
 $wdw = new Wptuts_Dashboard_Widgets();
 echo '<div id="tabs">';
 echo '<strong><span class="blue">Last Updates: </span>'.get_option('last_updated_tracker').'</strong>'; 
 $wdw->output_report();
 echo '<div class="more-data">  <div class="follow-us">Follow us on Twitter 
    <span><a target="_blank" href="https://twitter.com/ecomm_tools"> <img src="'.plugins_url().'/Customer-Conversion-Tracker-Lite/assets/images/twt.png"></a></span></div><h2 class="chart-head"><a target="_blank" href="http://codecanyon.net/item/customer-conversion-tracker/10341635?WT.oss_phrase=&WT.oss_rank=15&WT.z_author=EcommerceTools&WT.ac=search_thumb">Download the full version</a></h2>
<span>Display conversion data over a custom time period.</span>
<span>View data as a graph.</span>
<span>Export custom data as a CSV.</span></div></div>';

}


