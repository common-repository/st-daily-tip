<?php
//Display Admin Menu
add_action('admin_menu', 'daily_tip_admin_menu');
function daily_tip_admin_menu() {
	$page = add_menu_page( 'Daily Tips Page', 'Daily Tips', 'manage_options','daily-tip','daily_tip_option_page', plugins_url( 'st-daily-tip/images/icon.png' ));
	add_submenu_page( 'daily-tip' ,'Add Daily Tip Page', 'Add Daily Tip', 'manage_options','add-daily-tip','add_daily_tip_page', '');
	//add_menu_page ( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', string $icon_url = '', int $position = null )
	//add_submenu_page ( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' )
	//add_action('admin_print_scripts-' . $page, 'daily_tips_admin_scripts');

}
/*function daily_tips_admin_scripts() {
	
}*/
function st_daily_tip_check_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    
    return $data;
}
/********************************Upload CSV ***********************************/
function get_abs_path_from_src_file($src_file)
{
	if(preg_match("/http/",$src_file))
	{
		$path = parse_url($src_file, PHP_URL_PATH);
		$abs_path = $_SERVER['DOCUMENT_ROOT'].$path;
		$abs_path = realpath($abs_path);
		if(empty($abs_path)){
			$wpurl = get_bloginfo('wpurl');
			$abs_path = str_replace($wpurl,ABSPATH,$src_file);
			$abs_path = realpath($abs_path);			
		}
	}
	else
	{
		$relative_path = $src_file;
		$abs_path = realpath($relative_path);
	}
	return $abs_path;
}
//Upload CSV File
function readAndDump($src_file,$table_name,$column_string="",$start_row=2)
{
	$use_utf_encode = FALSE;
		
	if(isset($_POST['use_utf_encode'])){
		if($_POST['use_utf_encode'] == "1"){
			$use_utf_encode = TRUE;
		}
	}
	ini_set('auto_detect_line_endings', true);
	global $wpdb;
	$errorMsg = "";
	if(empty($src_file))
	{
            $errorMsg .= "<br />Input file is not specified";
            return $errorMsg;
    }
	
	$file_path = get_abs_path_from_src_file($src_file);	
	
	$file_handle = fopen($file_path, "r");
	if ($file_handle === FALSE) {
		// File could not be opened...
		$errorMsg .= "Source file could not be opened!<br />";
		$errorMsg .= "Error opening ('$file_path')";	// Catch any fopen() problems.
		return $errorMsg;
	}
	
	$row = 1;
	while (!feof($file_handle) ) 
	{
		$line_of_text = fgetcsv($file_handle, 1024);
		if ($row < $start_row)
		{
			// Skip until we hit the row that we want to read from.
			$row++;
			continue;
		}
		$columns = count($line_of_text);
		
		if ($columns>1)
		{
	        	$query_vals = "'".esc_sql($line_of_text[0])."'";
	        	for($c=1;$c<$columns;$c++)
	        	{
					/** Populate the Group Name if not mentioned in CSV**/
					if ($c == 3)
					{
						if ($line_of_text[$c] == '')
						{
							$line_of_text[$c]='Tip';
						}
					}		
					if ($use_utf_encode){
						$line_of_text[$c] = utf8_encode($line_of_text[$c]);
					}
					//$line_of_text[$c] = addslashes($line_of_text[$c]);
	                $query_vals .= ",'".esc_sql($line_of_text[$c])."'";
					
	        	}
				//Added Date
				$query_vals .= ",'" . current_time('mysql') . "'";
				$query = "INSERT INTO $table_name ($column_string) VALUES ($query_vals)";
				//echo $query."<br/>";
				$results = $wpdb->query($query);
				if(empty($results))
				{
					$errorMsg .= "<br />" . __('Insert into the Database failed for the following Query:','stdailytip') . "<br />";
					$errorMsg .= $query;
				}
		}
		$row++;
	}
	fclose($file_handle);
	
	return $errorMsg;
}
/********************************Upload CSV ***********************************/

/*** WP_List_Table */		
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Daily_Tips_Table extends WP_List_Table{
	/* Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items(){
    	//list($group_name,$from_date,$to_date) = func_get_args();
		$group_name = "";
		$from_date = "";
		$to_date = "";
		if(isset($_REQUEST['group_name'])){
			$group_name = $_REQUEST['group_name'];	
		}
		if(isset($_REQUEST['display_from_date'])){
			$from_date = $_REQUEST['display_from_date'];
		}
		if(isset($_REQUEST['display_to_date'])){
			$to_date = $_REQUEST['display_to_date'];
		}
		
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data($group_name,$from_date,$to_date);
		usort( $data, array( &$this, 'sort_data' ) );

		$perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);
		
		$this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
		
		$data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
		
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
	/**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
			'chk_delete'		=> '<span><input type="checkbox" name="checkall" onclick="checkedAll();"/> Select All<span/> ',
            'id'          		=> 'ID',
			'tip_title'      	=> 'Tip Title',
			'tip_text'		  	=> 'Tip Text',
			'display_date' 		=> 'Display Date',
			'display_day'		=> 'Display Day',
			'last_shown_on'		=> 'Last Shown On',
			'group_name'		=> 'Group Name',
			'display_yearly'	=> 'Repeat Yearly',
			//'preview'			=> 'Preview',
			'edit'				=> 'Edit'
        );

        return $columns;
    }
	/**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }
	/**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('display_date' 	=> array('display_date', false),
					 'tip_title' 	    => array('tip_title', true),
					 'last_shown_on'	=> array('last_shown_on', false),
					 'group_name'	=> array('group_name', true),
					 'tip_text'	=> array('tip_text', true),
					);
    }
	/**
     * Get the table data
     *
     * @return Array
     */
    private function table_data($group_name,$from_date,$to_date)
    {
		global $wpdb;	
		global $table_suffix;
		
		$weekdays = array(0=> "",1 => "Sunday",2 => "Monday", 3 => "Tuesday",4 => "Wednesday ", 5 => "Thursday",6 => "Friday",7 => "Saturday");
		
		$table_suffix = "dailytipdata";
		$daily_tips_table = $wpdb->prefix . $table_suffix;
		
		
		$data = array();
	
		$where = "WHERE 1=1 ";
		if($group_name != NULL){
			$where .= " AND group_name = '$group_name'";
		}
		if($from_date != NULL){
			$where .= " AND display_date >= '$from_date'";
		}
		if($to_date != NULL){
			$where .= " AND display_date <= '$to_date'";
		}
		$sql = "SELECT * FROM $daily_tips_table $where";
		//echo $sql;
		$daily_tips = $wpdb->get_results($sql);
		foreach ( $daily_tips as $daily_tip ) {
			$data[] = array(
                    'id'             	=> $daily_tip->id,
					'tip_title'         => $daily_tip->tip_title,
					'tip_text'	        => $daily_tip->tip_text,
					'display_day'	    => $weekdays[$daily_tip->display_day],
					'display_date'		=> $daily_tip->display_date,
					'last_shown_on'		=> $daily_tip->shown_date,
					'group_name'		=> $daily_tip->group_name,
					'display_yearly'	=> $daily_tip->Display_yearly,
					'preview'			=> "<a href='#'  class=\"button showPreview\" style=\"color:#41411D;\">Show Preview</a>",
					'edit'				=> "<a href=\"".$_SERVER['PHP_SELF']."?page=add-daily-tip&op=edit&edit_id=".$daily_tip->id."\" class=\"button\" style=\"color:#41411D;\">Edit</a>",
					'chk_delete'		=> "<input type='checkbox' name='checkbox[]' value='" . $daily_tip->id . "'></input>"
               );
			   
		}
        return $data;
    }
	// Used to display the value of the id column
	public function column_id($item)
	{
		return $item['id'];
	}
	
	/**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'id':
			case 'tip_title':
			case 'tip_text':
			case 'display_day':
			case 'display_date':
			case 'last_shown_on':
			case 'group_name':
			case 'display_yearly':
			case 'edit':
			case 'preview':
			case 'chk_delete':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }
	/**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'display_date';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        $result = strnatcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}

function add_daily_tip_page(){
	$weekdays = array(0 =>"",1 => "Sunday",2 => "Monday", 3 => "Tuesday",4 => "Wednesday ", 5 => "Thursday",6 => "Friday",7 => "Saturday");
	
	global $wpdb;
	global $table_suffix;
	
	$table_suffix = "dailytipdata";
	
	$table_name = $wpdb->prefix . $table_suffix;
	$edit_tip_title = "";
	$edit_tip_text = "";
	$edit_display_date = "";
	$edit_display_yearly = "";
	$edit_display_day = "";
		if (isset($_REQUEST['op'])&&isset($_REQUEST['edit_id'])){
			
			$id = st_daily_tip_check_input($_REQUEST["edit_id"]);
			$edit_tip = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$id';", ARRAY_A);
			$edit_added_date = $edit_tip['added_date'];
			$edit_tip_title = st_daily_tip_check_input($edit_tip['tip_title']);
			$edit_tip_text = st_daily_tip_check_input($edit_tip['tip_text']);
			$edit_group_name = $edit_tip['group_name'];
			$edit_display_yearly = $edit_tip['Display_yearly'];
			$edit_display_date = $edit_tip['display_date'];
			$edit_shown_date = $edit_tip['shown_date'];
			$edit_display_day = $edit_tip['display_day'];
		}
		
?>

<div class="wrap">  
	
	<h2><?php __('Daily Tip Plugin','stdailytip')?></h2>
	<div class="postbox-container" style="width:70%;padding-right:25px;">
	    <div class="metabox-holder">
			<div class="meta-box-sortables">
				
				<div id="toc" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e('Enter Single Tip','stdailytip') ?></span></h3>
					<div class="inside">
					<a href="<?=$_SERVER['PHP_SELF']."?page=daily-tip" ;?>" class="button-primary">All Tips</a>
					<p></p>
					<form id="edit_data" action="<?php echo $_SERVER['PHP_SELF']."?page=daily-tip"; ?>" method="post">
						<?php
						if (isset($_REQUEST['op'])&&isset($_REQUEST['edit_id'])) { 
							echo "<input type='hidden' name=\"id\" value=\"" . st_daily_tip_check_input($_REQUEST["edit_id"]) . "\" />"; 
						}else{ 
							if (isset($edit_id) && $edit_id!="" ) { 
								echo "<input type='hidden' name=\"id\" value=\"" . st_daily_tip_check_input($edit_id) . "\" />"; 
							}  
						}
						?>
						<div>
							<label><?php _e('Tip Title','stdailytip') ?></label>
							<input name="tip_title" class="regular-text code" value="<?php echo $edit_tip_title; ?>"/>
							<span></span>
						</div>
						<div>
							<label><?php _e('Tip Text','stdailytip') ?><span style="color:red;vertical-align:top;">*</span></label>
							<?php 
								{ $content = $edit_tip_text; } 
								$settings = array( 'media_buttons' => true );
								$editor_id = 'tiptext';
								wp_editor( $content, $editor_id, $settings ); ?>
						</div>
						<div>
						<script type="text/javascript">
							jQuery(document).ready(function() {
								jQuery('#display_date').datepicker({
									dateFormat : 'yy-mm-dd'
								});
							});
						</script>
							<label>Display Date</label>
							<input type="text" name="display_date" id="display_date" class="regular-text code" value="<?php { echo $edit_display_date; } ?>"/>
						</div>
						<div>
							<label>Display Day</label>
							<select name="display_day">
						<option value='0' <?php  { if($edit_display_day=='0') {echo "selected=\"selected\"";}} ?>></option>
						<?php
							for ($i=1; $i<=7; $i++)
							{
								if($edit_display_day==$i) {
									echo "<option value='$i' selected=\"selected\">$weekdays[$i]</option>";
								}else{
									echo "<option value='$i'>$weekdays[$i]</option>";
								}
							}
						?>
						</select></div>
						
						<?php 
						global $showyearly;
							if($edit_display_yearly=="on"){
								$showyearly="checked";
							}else{
								$showyearly="";
							}
						 ?>
						<div><label><?php _e('Repeat Yearly?','stdailytip') ?></label><input type="checkbox" name="chkyearly" <?php echo $showyearly;?>></input></div>
						<div><label><?php _e('Group Name','stdailytip') ?></label><input name="group_name" class="regular-text code" value="<?php if (isset($_REQUEST['op'])&&isset($_REQUEST['edit_id'])) { echo $edit_group_name; }?>"/><span></span></div>
						<?php wp_nonce_field( 'edit_data' ); ?>
						<p class="submit">
							<input class="button-primary" type="submit" name="Submit" value="Submit" />
							<input class="button-secondary" type="submit" name="Cancel" value="Cancel" />
						</p>
					</form>
				</div>
			</div>
			
				</div>
			</div>
		</div>
	<div class="postbox-container side" style="width:20%;">
	    <div class="metabox-holder">
			<div class="meta-box-sortables">
				<div id="toc" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e('How to Use','stdailytip')?></span></h3>
					<div class="inside">
					<strong><?php _e('1. Create Tips List','stdailytip')?></strong><br/>
					<?php _e('You can upload list of tips from CSV file or Manually Entering Tips','stdailytip')?><br/>
					<strong><?php _e('2. Display Tips','stdailytip')?></strong><br/>
					<?php _e('You can use widget or the short code:','stdailytip')?> <br/>[stdailytip group="Tip" date="show" title="show"]<br/>
					<br/>[stdailytip yesterday="on" group="Tip" date="show" title="show"] - to display yesterday's tip<br/>
					<?php _e('If you do not want to show last date then replace "show" with "hide" in date','stdailytip')?><br/>
					<?php _e('If you do not want to show title then replace "show" with "hide" in title','stdailytip')?><br/>
					<br/>[stdailytiplist show_last=10] - to display displayed tips<br/>
					<?php _e('set show_last to any number of tips you want to show. Set it to 0 to remove limit','stdailytip')?><br/>
					<strong><?php _e('3. Use classes','stdailytip')?></strong><br/>
					<?php _e('Use classes tip_title, tip_text, and single_tip to style the tips','stdailytip')?>
					</div>
				</div>
				<div id="toc" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e('Show your Support','stdailytip')?></span></h3>
					<div class="inside">
						<p>
						<strong><?php _e('Want to help make this plugin even better? All donations are used to improve this plugin, so donate now!','stdailytip')?></strong>
						</p>
						<a href="https://sanskruti.net/daily-tip-plugin-for-wordpress/"target="_blank"><?php _e('Donate','stdailytip')?></a>
						<p>Or you could:</p>
						<ul>
							<li><a href="https://wordpress.org/plugins/st-daily-tip/"target="_blank"><?php _e('Rate the plugin 5 star on WordPress.org','stdailytip')?></a></li>
							<li><a href="https://wordpress.org/support/plugin/st-daily-tip/" target="_blank"><?php _e('Help out other users in the forums','stdailytip')?></a></li>
							<li><?php _e('Blog about it &amp; link to the ','stdailytip')?><a href="https://sanskruti.net/daily-tip-plugin-for-wordpress/" target="_blank"><?php _e('plugin page','stdailytip')?></a></li>				
						</ul>
					</div>
				</div>
				<div id="toc" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e('Connect With Us ','stdailytip')?></span></h3>
					<div class="inside">
					<a class="facebook" href="https://www.facebook.com/sanskrutitech"></a>
					<a class="twitter" href="https://twitter.com/#!/sanskrutitech"></a>
					<a class="googleplus" href="https://plus.google.com/107541175744077337034/posts"></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
}

function preview_tip($group,$date,$title,$id){
	global $wpdb;
	global $table_suffix;
	
	$table_suffix = "dailytipdata";
	$table_name = $wpdb->prefix . $table_suffix;
	
	$todate = current_time('mysql',0);
	
	$sql = "SELECT * FROM $table_name WHERE ID=$id;";
	$tips = $wpdb->get_row($sql, ARRAY_A);
	
	if($tips['tip_text'] != null) 
	{	
			
		if ($tips['tip_title'] != null && $title == "show")
		{
			
			if($date=="show")
			{	
				$dat=$tips['shown_date'];
				$show_date=date(get_option("st_daily_date_format"),strtotime($dat));
				return "<div class='tip_container'><div class='tip_date'>Date: ".$show_date . "</div><div class='tip_title'>" .$tips['tip_title'] . "</div><div class='tip_text'>" .$tips['tip_text'] ."</div></div>";
			}
			else
			{
				return "<div class='tip_container'><div class='tip_title'>" .$tips['tip_title'] . "</div><div class='tip_text'>" .$tips['tip_text'] . "</div></div>";
			}
		}
		else
		{
			if($date=="show")
			{	
				$dat=$tips['shown_date'];
				$show_date=date(get_option("st_daily_date_format"),strtotime($dat));
				return "<div class='tip_container'><div class='tip_text'>" .$tips['tip_text'] . "</div><div class='tip_last_shown'> Last Shown Date: ".$show_date."</div></div>";
			}
			else
			{
				return "<div class='tip_container'><div class='tip_text'>" .$tips['tip_text'] . "</div></div>";
			}
		}
	}else{
		return "<div class='tip_container'><div class='tip_text'>$today_tip</div></div>";
	}
	
}
function daily_tip_option_page() {
	
	$weekdays = array(1 => "Sunday",2 => "Monday", 3 => "Tuesday",4 => "Wednesday ", 5 => "Thursday",6 => "Friday",7 => "Saturday");

	global $wpdb;
	global $table_suffix;
	
	$table_suffix = "dailytipdata";
	
	$table_name = $wpdb->prefix . $table_suffix;
	$column_string = "tip_title,tip_text,display_date,display_day,group_name,Display_yearly,added_date";
?>
<script>
jQuery(function() {
    jQuery( "#dialog" ).dialog({
      autoOpen: false,
      show: {
        effect: "blind",
        duration: 1000
      },
      hide: {
        effect: "explode",
        duration: 1000
      }
    });
 
    jQuery( ".showPreview" ).click(function() {
		jQuery( "#dialog" ).html('Hello');
		jQuery( "#dialog" ).dialog( "open" );
    });
  });
</script>
<div id="dialog" title="Basic dialog">
  <p>This is an animated dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the 'x' icon.</p>
</div>

<div class="wrap">
	
	<h2><?php __('Daily Tip Plugin','stdailytip')?></h2>
	<?php
	
		if (isset($_REQUEST['filter'])) {
			$nonce = $_REQUEST['_wpnonce'];
			if ( ! wp_verify_nonce( $nonce, 'filter_tips' ) ) {	
				exit; // Get out of here, the nonce is rotten!
			}
			$group_name_filter = $_REQUEST['group_name'];	
			$display_from_date = $_REQUEST['display_from_date'];
			$display_to_date = $_REQUEST['display_to_date'];
		}else{
			$display_from_date = "";
			$display_to_date = "";
		}
		if (isset($_REQUEST['Delete'])) {
			$nonce = $_REQUEST['_wpnonce'];
			if ( ! wp_verify_nonce( $nonce, 'delete_tip' ) ) {
			  exit; // Get out of here, the nonce is rotten!
			}
			if(isset($_REQUEST['checkbox']))
			{
				$i=0;
				foreach($_REQUEST['checkbox']  as $chkid)
				{
					$wpdb->query("DELETE FROM $table_name WHERE ID = " .$chkid."");
					$i++;
				}
				echo "<div id=\"message\" class=\"updated fade\"><p><strong>$i ";
				echo __('Tip(s) Deleted Successfully!','stdailytip');
				echo "</strong></p></div>";
			}
		}		
		
		if (isset($_REQUEST['chngdatefrmt'])){
			$nonce = $_REQUEST['_wpnonce'];
			if ( ! wp_verify_nonce( $nonce, 'change_date_format' ) ) {
			  exit; // Get out of here, the nonce is rotten!
			}
			if($_REQUEST["datfrmt"]=="Y-m-d")
			{
				update_option("st_daily_date_format", 'Y-m-d');
			}
			elseif($_REQUEST["datfrmt"]=="d-m-Y")
			{
				update_option("st_daily_date_format", 'd-m-Y');
			}
			elseif($_REQUEST["datfrmt"]=="m-d-Y")
			{
				update_option("st_daily_date_format", 'm-d-Y');
			}
			elseif($_REQUEST["datfrmt"]=="F j, Y")
			{
				update_option("st_daily_date_format", 'F j, Y');
			}
			elseif($_REQUEST["datfrmt"]=="l jS F, Y")
			{
				update_option("st_daily_date_format", 'l jS F, Y');
			}
		}
		if (isset($_REQUEST['default_text'])){
			$nonce = $_REQUEST['_wpnonce'];
			if ( ! wp_verify_nonce( $nonce, 'default_text_nonce' ) ) {
			  exit; // Get out of here, the nonce is rotten!
			}
			
			update_option("st_daily_default_text", $_REQUEST["default_tip"]);	
		}			

		if (isset($_REQUEST['op']) && isset($_REQUEST['edit_id'])) {
			global $wpdb;
			global $table_suffix;
	
			$table_suffix = "dailytipdata";
			$table_name = $wpdb->prefix . $table_suffix;
			
			$id = st_daily_tip_check_input($_REQUEST["edit_id"]);
			$edit_tip = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$id';", ARRAY_A);
			$edit_added_date = $edit_tip['added_date'];
			$edit_tip_title = st_daily_tip_check_input($edit_tip['tip_title']);
			$edit_tip_text = st_daily_tip_check_input($edit_tip['tip_text']);
			$edit_group_name = $edit_tip['group_name'];
			$edit_display_yearly = $edit_tip['Display_yearly'];
			$edit_display_date = $edit_tip['display_date'];
			$edit_shown_date = $edit_tip['shown_date'];
			$edit_display_day = $edit_tip['display_day'];
		}		
		
		
		if(isset($_REQUEST['file_upload'])){
			$nonce = $_REQUEST['_wpnonce'];
			if ( ! wp_verify_nonce( $nonce, 'file_upload_nonce' ) ) {
			  exit; // Get out of here, the nonce is rotten!
			}
			$upload_dir = wp_upload_dir();
			$target_path =  $upload_dir['path'];
			
			$tmp_name = $_FILES["uploadedfile"]["tmp_name"];
			$name = $_FILES["uploadedfile"]["name"];
			
			if(move_uploaded_file($tmp_name,"$target_path/$name"))
			{
				$file_name = $target_path . "/" . $name;
			} 
			else
			{
				echo '<div id="message" class="error"><p><strong>';
				echo  __('There was an error uploading the file, please try again!','stdailytip');
				echo '</strong></p></div>';
			}
            
			
			$errorMsg = readAndDump($file_name,$table_name,$column_string);
        
			
			if(empty($errorMsg))
			{
				echo '<div id="message" class="updated fade"><p><strong>';
				echo __('File content has been successfully imported into the database!','stdailytip');
				echo '</strong></p></div>';
			}
			else
			{
				echo '<div id="message" class="error"><p><strong>';
				echo __('Error occurred while trying to import!','stdailytip') . "<br />";
				echo $errorMsg;
				echo '</strong></p></div>';
			}
			
		}
		//Store the Data input if data is submitted
		if (isset($_REQUEST['Submit'])) { 
			$nonce = $_REQUEST['_wpnonce'];
			if ( ! wp_verify_nonce( $nonce, 'edit_data' ) ) {
			  exit; // Get out of here, the nonce is rotten!
			}

			$tip_text = st_daily_tip_check_input($_REQUEST["tiptext"]);
			$display_date = st_daily_tip_check_input($_REQUEST["display_date"]); 
			$display_date = htmlspecialchars($display_date);
			$display_day = st_daily_tip_check_input($_REQUEST["display_day"]);
			$display_day = htmlspecialchars($display_day);
			$group_name = st_daily_tip_check_input($_REQUEST["group_name"]);
			$group_name = htmlspecialchars($group_name);
			$tip_title = st_daily_tip_check_input($_REQUEST["tip_title"]);
			//$tip_title = htmlspecialchars($tip_title);
			if($group_name==null){
				$group_name="Tip";
			}
			if(isset($_REQUEST["chkyearly"])){
				$yearly ="on";
			}else{
				$yearly="";
			}
							
			if (isset($_REQUEST['id'])) { 
				//Update
				$id = st_daily_tip_check_input($_REQUEST["id"]);
				$wpdb->update( $table_name , array( 'tip_title' => $tip_title,'tip_text' => $tip_text,'Display_yearly' => $yearly,'display_date'=>$display_date,'display_day'=>$display_day,'group_name'=>$group_name), array('ID' => $id)); 
				
				echo "<div id=\"message\" class=\"updated fade\"><p><strong>" . __('Tip Updated Successfully!','stdailytip') . "</strong></p></div>";
			}else{
				//Insert
				if($tip_text!=null)
				{
					if($display_date!=null)
					{
						$rows_affected = $wpdb->insert( $table_name, array( 'added_date' => current_time('mysql'), 'tip_text' => $tip_text,'tip_title' => $tip_title, 'display_date' => $display_date, 'display_day' => $display_day, 'Display_yearly' =>$yearly,'group_name' => $group_name ) );
						echo "<div id=\"message\" class=\"updated fade\"><p><strong>". __('Tip Inserted Successfully!','stdailytip') ."</strong></p></div>";
						$id = $wpdb->insert_id;
					}
					else if($display_day!=0 )
					{
						$rows_affected = $wpdb->insert( $table_name, array( 'added_date' => current_time('mysql'), 'tip_text' => $tip_text,'tip_title' => $tip_title, 'display_date' => $display_date, 'display_day' => $display_day,'group_name' => $group_name ) );
						echo "<div id=\"message\" class=\"updated fade\"><p><strong>". __('Tip Inserted Successfully!','stdailytip') ."</strong></p></div>";
						$id = $wpdb->insert_id;
					}
					else{
						$rows_affected = $wpdb->insert( $table_name, array( 'added_date' => current_time('mysql'), 'tip_text' => $tip_text,'tip_title' => $tip_title, 'display_date' => $display_date, 'display_day' => $display_day, 'Display_yearly' =>$yearly,'group_name' => $group_name ) );
						echo "<div id=\"message\" class=\"updated fade\"><p><strong>". __('Tip Inserted Successfully!','stdailytip') ."</strong></p></div>";
						$id = $wpdb->insert_id;
					}
				}
			}
			$edit_tip = $wpdb->get_row("SELECT * FROM $table_name WHERE id='$id';", ARRAY_A);
			$edit_id = $id;
			$edit_added_date = $edit_tip['added_date'];
			$edit_tip_title = st_daily_tip_check_input($edit_tip['tip_title']);
			$edit_tip_text = st_daily_tip_check_input($edit_tip['tip_text']);
			$edit_group_name = $edit_tip['group_name'];
			$edit_display_yearly = $edit_tip['Display_yearly'];
			$edit_display_date = $edit_tip['display_date'];
			$edit_shown_date = $edit_tip['shown_date'];
			$edit_display_day = $edit_tip['display_day'];
			
		}
	?>
	<div class="postbox-container" style="width:70%;padding-right:25px;">
	    <div class="metabox-holder">
			<div class="meta-box-sortables">
				
			
			<div id="toc" class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e('Tips','stdailytip') ?></span></h3>
					<div class="inside">
					<a href="<?=$_SERVER['PHP_SELF']."?page=add-daily-tip" ;?>" class="button-primary">Add Tip</a>
					<a href="<?=$_SERVER['PHP_SELF']."?page=daily-tip&export_daily_tips_csv=yes" ;?>" class="button-primary alignright">Export CSV</a>
					<form style="display:block; width:100%;">
						<div>
						<script type="text/javascript">
							jQuery(document).ready(function() {
								jQuery('#display_from_date').datepicker({
									dateFormat : 'yy-mm-dd'
								});
								jQuery('#display_to_date').datepicker({
									dateFormat : 'yy-mm-dd'
								});
							});
						</script>
							<input type="hidden" name="page" id="page" value="daily-tip"/>
							<label>Group Name</label>
							<select name="group_name" class="code">
								<option value=''>All</option>
							<?php 
								$table_result = $wpdb->get_results("SELECT DISTINCT group_name FROM $table_name "); 
								foreach ( $table_result as $table_row ) 
								{
									echo "<option value='" . $table_row->group_name . "'>" . $table_row->group_name . "</option>";
								}
							?>
							</select><br><br>&nbsp; 
							<label>Display Date From</label>
							<input type="text" name="display_from_date" id="display_from_date" class="code" value="<?=$display_from_date;?>"/>
							&nbsp;&nbsp;<label>To</label>
							<input type="text" name="display_to_date" id="display_to_date" class="code" value="<?=$display_to_date;?>"/>
						</div>
						<input type="submit" class="button-primary" name="filter" value="Filter" /><br><br>
						<?php wp_nonce_field( 'filter_tips' ); ?>

					</form>
					<?php $DailyTipsTable = new Daily_Tips_Table(); ?>
					<?php $DailyTipsTable->prepare_items(); ?>
					<form id="myform" action="<?=$_SERVER['PHP_SELF'];?>?page=daily-tip" method="post">
					<input type="submit" name="Delete" value="Delete Selected Tips" id="btnsubmit" class="button-secondary" />
					<?php wp_nonce_field( 'delete_tip' ); ?>
					<?php $DailyTipsTable->display(); ?>
					</form>
					</div>
				</div>
				<div id="toc" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e('Upload a File','stdailytip') ?></span></h3>
					<div class="inside">
						<form id="upload" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']."?page=daily-tip"; ?>" method="POST">
							<input type="hidden" name="file_upload" id="file_upload" value="true" />
							<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
							<strong><?php _e('Choose a CSV file to upload:','stdailytip') ?></strong><input name="uploadedfile" id="upload" type="file" size="25" />
							<input type="submit" class="button-primary" value="Upload File" /><br/>
							<input type="checkbox" name="use_utf_encode" value="1" checked >Use UTF Encode? (Use for Special Characters)
							<?php wp_nonce_field( 'file_upload_nonce' ); ?>
						</form>
						<br/>
						<h4>Note : </h4>
						<span class="description"><strong><?php _e('The Format of CSV File must be as below :','stdailytip') ?></strong><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('The First line must be headers as it is ignored while uploading on database','stdailytip') ?><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('From the second line, the data should begin in following order :','stdailytip') ?><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php _e('Tip Title, Tip Text, Display Date,Display Day,Group Name,Repeat Yearly.','stdailytip') ?></strong><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Tip Title : If you want to add title to tip.','stdailytip') ?><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Tip Text : The Actual Statement to be displayed.','stdailytip') ?><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php _e('To insert a tip with comma (,) place the tip between two inverted commas ". e.g. "Like , this" ','stdailytip') ?></strong><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Display Date : Any Specific Date in format YYYY-MM-DD when you want to display the Tip.','stdailytip') ?><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Display Day : Day of week (number format) on which the Tip Should Come. (1 = Sunday ,2 = Monday , 3 = Tuesday, 4 = Wednesday  ...7 = Saturday) ','stdailytip') ?><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Group Name : Group Name in which the tip is to be added. <strong>Group name is Must. Keep "Tip" Group Name in case single group','stdailytip') ?></strong><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Repeat Yearly','stdailytip')?> : <strong>on</strong> - <?php _e('To repeat yearly. Leave blank otherwise.','stdailytip') ?><br/>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><?php _e('Please Note','stdailytip')?>:</strong><?php _e('Display Day is ignored if Display Date is mentioned.','stdailytip') ?><br/></span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="postbox-container side" style="width:20%;">
	    <div class="metabox-holder">
			<div class="meta-box-sortables">
				<div id="toc" class="postbox">
					<h3 class="hndle"><span><?php _e('Change Date Format to display on Front Page','stdailytip') ?></span></h3>
					<div class="inside">
					<form id="chngdatefrmt" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']."?page=daily-tip"; ?>" method="POST">
						<select name="datfrmt">
							<option value="Y-m-d" <?php if(get_option("st_daily_date_format")=="Y-m-d"){echo "selected=\"selected\"";}?>>yy-mm-dd (e.g. 2013-10-25)</option>
							<option value="d-m-Y" <?php if(get_option("st_daily_date_format")=="d-m-Y"){echo "selected=\"selected\"";}?>>dd-mm-yy (e.g. 25-10-2013)</option>
							<option value="m-d-Y" <?php if(get_option("st_daily_date_format")=="m-d-Y"){echo "selected=\"selected\"";}?>>mm-dd-yy (e.g. 10-25-2013)</option>
							<option value="F j, Y" <?php if(get_option("st_daily_date_format")=="F j, Y"){echo "selected=\"selected\"";}?>>F j, Y (e.g. October 25, 2013)</option>
							<option value="l jS F, Y" <?php if(get_option("st_daily_date_format")=="l jS F, Y"){echo "selected=\"selected\"";}?>>l jS F, Y (e.g. Friday 25th October, 2013)</option>
						</select><br><br>
						<input class="button-primary" type="submit" name="chngdatefrmt" value="Change" />
						<?php wp_nonce_field( 'change_date_format' ); ?>
					</form>
					</div>
				</div>
				<div id="toc" class="postbox">
					<h3 class="hndle"><span><?php _e('Default Text To Display If No Tips','stdailytip') ?></span></h3>
					<div class="inside">
					<form id="default_text" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']."?page=daily-tip"; ?>" method="POST">
						<?php 
							$st_daily_date_format = get_option("st_daily_default_text");
						?>
						<input type="text" name="default_tip" id="default_tip" class="code" value="<?=$st_daily_date_format;?>"/>
						<input class="button-primary" type="submit" name="default_text" value="Save" />
						<?php wp_nonce_field( 'default_text_nonce' ); ?>
					</form>
					</div>
				</div>
				<div id="toc" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e('How to Use','stdailytip')?></span></h3>
					<div class="inside">
					<strong><?php _e('1. Create Tips List','stdailytip')?></strong><br/>
					<?php _e('You can upload list of tips from CSV file or Manually Entering Tips','stdailytip')?><br/>
					<strong><?php _e('2. Display Tips','stdailytip')?></strong><br/>
					<?php _e('You can use widget or the short code:','stdailytip')?> <br/>[stdailytip group="Tip" date="show" title="show"]<br/>
					<br/>[stdailytip yesterday="on" group="Tip" date="show" title="show"] - to display yesterday's tip<br/>
					<br/>[stdailytiplist] - to display last 10 displayed tips<br/>
					<?php _e('If you do not want to show last date then replace "show" with "hide" in date','stdailytip')?><br/>
					<?php _e('If you do not want to show title then replace "show" with "hide" in title','stdailytip')?><br/>
					<strong><?php _e('3. Use classes','stdailytip')?></strong><br/>
					<?php _e('Use classes tip_title, tip_text, and single_tip to style the tips','stdailytip')?>
					</div>
				</div>
				<div id="toc" class="postbox">
					<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e('Show your Support','stdailytip')?></span></h3>
					<div class="inside">
						<p>
						<strong><?php _e('Want to help make this plugin even better? All donations are used to improve this plugin, so donate now!','stdailytip')?></strong>
						</p>
						<a href="https://sanskruti.net/daily-tip-plugin-for-wordpress/" target="_blank"><?php _e('Donate','stdailytip')?></a>
						<p>Or you could:</p>
						<ul>
							<li><a href="http://wordpress.org/extend/plugins/st-daily-tip/"><?php _e('Rate the plugin 5 star on WordPress.org','stdailytip')?></a></li>
							<li><a href="https://wordpress.org/support/plugin/st-daily-tip/" target="_blank"><?php _e('Help out other users in the forums','stdailytip')?></a></li>
							<li><?php _e('Blog about it &amp; link to the ','stdailytip')?><a href="https://sanskruti.net/daily-tip-plugin-for-wordpress/" target="_blank"><?php _e('plugin page','stdailytip')?></a></li>				
						</ul>
					</div>
				</div>
				<div id="toc" class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>
					<h3 class="hndle"><span><?php _e('Connect With Us ','stdailytip')?></span></h3>
					<div class="inside">
					<a class="facebook" href="https://www.facebook.com/sanskrutitech"></a>
					<a class="twitter" href="https://twitter.com/#!/sanskrutitech"></a>
					<a class="googleplus" href="https://plus.google.com/107541175744077337034/posts"></a>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php } ?>