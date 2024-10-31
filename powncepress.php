<?php
/*
Plugin Name: PowncePress
Plugin URI: http://www.itssamuel.com/blog/powncepress
Description: A Sidebar Widget for Pownce. Beware, this requires PHP5, which you probibly have.
Version: 0.3
Author: Samuel Cotterall
Author URI: http://www.itssamuel.com
*/

// Include the Pownce PHP Wrapper, thanks to Jeff Hodsdon (http://jeffhodsdon.com/pownce/)

include_once('pownce.class.php');

function PowncePressStyle(){
	echo
	
		"<style type=\"text/css\">"

		. $tag . "#PowncePressBody {font-family: Georgia, lucida Grande, sans-serif; font-size: 15px;}"
		. $tag.  "#PowncePressBody {background: #d9eaed; color: #433c2a;padding: 10px; }  "
		. $tag . "#PowncePressBody span.date {font-size: 10px; }"  
		. $tag . "#PowncePressBody a {color: #433c2a; text-decoration: none;}"
		. $tag . "#PowncePressBody a:hover {color: #2e8fad}"
		. $tag . "#PowncePressFooter {color: #d9eaed; width: 100%; background: url(\"";  
		
		bloginfo('wpurl'); // Overrides PHP Echo, as to not display the WP-URL
		
		echo "/wp-content/plugins/powncepress/images/pownce.png\") #433c2a no-repeat; background-position: right}"
		. $tag . "#PowncePressFooter img {padding: 10px}"
		. $tag . "#PowncePressFooter img.logo {float: right;}
		#powncepress small {float: right;}
		</style>";}

function PowncePressDispay($args) {
    extract($args);
	$options = get_option('PowncePressDisplay');
	$tag = urlencode($options['tag']);
	$number = urlencode($options['number']);

	$pownceAPIobj = new PowceXML($options['userid']);
	$persons_notes = $pownceAPIobj->get_persons_public_notes_from(null, null, $number);
	
	if($options['style']!='off') {
	
		PowncePressStyle(); 
	
	}
	

	echo $before_widget;

	// Pull latest post from Pownce
	
   	foreach ($persons_notes as $note) {

	    echo

	    "<" . $tag .  " id=\"PowncePressBody\"><a href=" . $note->permalink . ">" . $note->body . "</a><span class=\"date\">&nbsp; " . $note->display_since ."</span></" . $tag .  ">";

	    
	}
	// Insert the Widget's footer
	
	if($options['footer']!='no') {
	
	
	echo "<"  . $tag .  " id=\"PowncePressFooter\">"
	 
	. "<img src=\""
	    . $pownceAPIobj->get_photo('small', $note->sender->user->username)
	."\">
	
	</" . $tag .  ">"; }
	
	// Insert the PowncePress link
	
	if($options['linkback']!='no') {

		echo "<small><a href=\"http://itssamuel.com/blog/powncepress/\" rel=\"PowncePress\">PowncePress</a></small>";
		}
	
  
  echo $after_widget;
}



// PowncePress controls, sets username and all that jazz...

// Controls somewhat based on James Wilson's Digg Widget (http://nothingoutoftheordinary.com/2007/05/31/wordpress-digg-widget/)

function PowncePressControl() {

	$options = get_option('PowncePressDisplay');
	if ( !is_array($options) )
		$options = array(
			'username' => 'samuelcotterall',
			'tag' => 'div',
			'style' => 'on',
			'number' => '1',
			'footer' => 'yes',
			'linkback' => 'yes',
			
		
		);
	if ( $_POST['powncesubmit'] ) {
	
		$options['userid'] = strip_tags(stripslashes($_POST['pownceid']));
		$options['tag'] = strip_tags(stripslashes($_POST['powncetag']));
		$options['style'] = strip_tags(stripslashes($_POST['powncestyle']));
		$options['number'] = strip_tags(stripslashes($_POST['powncenumber']));
		$options['footer'] = strip_tags(stripslashes($_POST['powncefooter']));		
		$options['linkback'] = strip_tags(stripslashes($_POST['powncelinkback']));		
		
		update_option('PowncePressDisplay', $options);
	}

	$userid = htmlspecialchars($options['userid'], ENT_QUOTES);
	$tag = htmlspecialchars($options['tag'], ENT_QUOTES);
	$style = htmlspecialchars($options['style'], ENT_QUOTES);
	$number = htmlspecialchars($options['number'], ENT_QUOTES);
	$footer = htmlspecialchars($options['footer'], ENT_QUOTES);
	$linkback = htmlspecialchars($options['linkback'], ENT_QUOTES);
	

	echo '<p style="text-align:right;"><label for="pownceid">' . __('Username:', 'widgets') . ' <input style="width: 200px;" id="pownceid" name="pownceid" type="text" value="'.$userid.'" /></label></p>';
	echo '<p style="text-align:right;"><label for="powncenumber">' . __('# Posts:', 'widgets') . ' <input style="width: 200px;" id="powncenumber" name="powncenumber" type="text" value="'.$number.'" /></label></p>';
	echo '<p style="text-align:right;"><label for="powncetag">' . __('Tag:', 'widgets') . ' <input style="width: 200px;" id="powncetag" name="powncetag" type="text" value="'.$tag.'" /></label></p>';
	echo '<p style="text-align:right;"><label for="powncestyle">' . __('Style:', 'widgets') . ' <select style="width: 200px;" id="powncestyle" name="powncestyle" value="'.$style.'" /><option value ="on">On</option>
	<option value ="off">Off</option></select></label></p>';
	echo '<p style="text-align:right;"><label for="powncefooter">' . __('Display Footer:', 'widgets') . ' <select style="width: 200px;" id="powncefooter" name="powncefooter" value="'.$footer.'" /><option value ="yes">Yes</option>
	<option value ="no">No</option></select></label></p>';
	echo '<p style="text-align:right;"><label for="powncelinkback">' . __('Display Link:', 'widgets') . ' <select style="width: 200px;" id="powncelinkback" name="powncelinkback" value="'.$linkback.'" /><option value ="yes">Yes</option>
	<option value ="no">No</option></select></label></p>';

	echo '<input type="hidden" id="powncesubmit" name="powncesubmit" value="1" />';
	
	echo '<p> Displaying the PowncePress link is up to you, but I appreciate it :) </p>';
	
	echo '<p>For more information, check out <a href="http://www.itssamuel.com/blog/powncepress">itssamuel.com/blog/powncepress</a></p>';
}


// Check to see if widget exists, then creates it.

function PowncePressWidgetize()	
{
    if ( !function_exists(
        'register_sidebar_widget') )
    {
        return;
    }
    register_sidebar_widget('PowncePress', 'PowncePressDispay');
	register_widget_control(array('PowncePress', 'widgets'), 'PowncePressControl', 350, 320);

}
	add_action('plugins_loaded','PowncePressWidgetize');

?>