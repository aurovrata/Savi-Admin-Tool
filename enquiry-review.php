<?php
 if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
  }

  class Enquiry_Review_List_Table extends WP_List_Table {
    
    function __construct(){
        global $status, $page;
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'enquiry_review',     //singular name of the listed records
            'plural'    => 'enquiry_reviews',    //plural name of the listed records
            'ajax'      => false,        //does this table support ajax?
            'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
        ) );

    }
    function column_default($item, $column_name){
        switch($column_name){
            case 'name':
            case 'email':
            case 'country':
            case 'phone':
            case 'time_of_stay':  
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

   function column_action($item){
        
        //Build row actions
        $actions = array(
            'skills'      => sprintf('<a href="javascript:void(0);" class="popper" data-popbox="savi_skill_%1$s">Skills</a>'),
            'motivation'    => sprintf('<a href="javascript:void(0);" class="popper" data-popbox="savi_motiv_%1$s">Motivation</a>'),
        );
        $hidden_div = sprintf('
        	<div id="savi_skill_%1$s" class="popbox">
			    <h2>Skills</h2>
			    <p>This is a skill.</p>
			</div>
			<div id="savi_motiv_%1$s" class="popbox">
			    <h2>Motivation</h2>
				<p>This is the motivation</p>
			</div>',
        	$item['ID']);
        //Return the title contents
        return sprintf('
        	%1$s
        	<span style="color:silver">%2$s</span>',
            $hidden_div,
            $this->row_actions($actions));
    }
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'name'     => 'Name',
            'email'    => 'Email',
            'country'  => 'Country',
            'phone'  => 'Phone Number',
            'time_of_stay'  => 'Time of Stay', 
            'action'  => 'Action'
        );

       
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'name'     => array('Name',false),     //true means it's already sorted
            'email'    => array('Email',false),
            'country'  => array('Country',false),
            
        );
        return $sortable_columns;
    }

    function get_bulk_actions() {
        $actions = array(
            'approve'    => 'Approve',
            'reject'    => 'Reject'
        );
        return $actions;
    }

    function process_bulk_action() {
        
        //Detect when a bulk action is being triggered...
        if( 'apporve'===$this->current_action() ) {
           wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        elseif( 'reject'===$this->current_action() ) {
           wp_die('Items deleted (or they would be if we had items to delete)!');
        }
        
    }

    function prepare_items() {
        global $wpdb; //This is used only if making any database queries

        /**
         * First, lets decide how many records per page to show
         */
        //$per_page = 5;
        $args = array(
		           'label' => __('Users per page', 'pippin'),
		           'default' => 10,
		           'option' => 'pippin_per_page'
	   );
        add_screen_option( 'per_page', $args );
        // get the current user ID
          $user = get_current_user_id();
        // get the current admin screen
         $screen = get_current_screen();
        // retrieve the "per_page" option
        $screen_option = $screen->get_option('per_page', 'option');
       // retrieve the value of the option stored for the current user
       $per_page = get_user_meta($user, $screen_option, true);
       if ( empty ( $per_page) || $per_page < 1 ) {
	   // get the default value if none is set
	     $per_page = $screen->get_option( 'per_page', 'default' );
       }
        $columns = $this->get_columns();
        $hidden = get_hidden_columns( $this->screen );;
        $sortable = $this->get_sortable_columns();
        
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        $this->process_bulk_action();
        
         /*$select_custom_List_qry = "select * from wp_custom_list";
         $custom_List_records = $wpdb->get_results( $select_custom_List_qry, ARRAY_A );
         */
          $args = array('post_type' => 'View_0',);
          $cpts = new WP_Query($args);
          //echo"<pre>",print_r($cpts),"</pre>";   
          if($cpts->have_posts()) : 
            while($cpts->have_posts() ) : 
                $cpts->the_post();
                $meta_values[] = get_post_meta(get_the_ID());
                $post_ID['ID'][] =get_the_ID();
            endwhile; 
         endif;
         $i=0; 
         foreach($meta_values as $meta_value) :
            $custom_post[$i]['Name']= $meta_value['Name'][0];
            $custom_post[$i]['Email']=  $meta_value['Email'][0]; 
            $custom_post[$i]['Country']= $meta_value['Country'][0];
            $custom_post[$i]['Phone']= $meta_value['Phone'][0]; 
            $custom_post[$i]['Time_of_Stay']=  $meta_value['Time_of_Stay'][0];
            $custom_post[$i]['Motivation']=$meta_value['Motivation'][0];
            $custom_post[$i]['SKills']= $meta_value['Skills'][0];
           $i++;
        endforeach;
        $j=0;
         foreach($post_ID['ID'] as  $key => $post) :
           $custom_post[$j]['ID']= $post;
         $j++;
        endforeach;
         // echo"<pre>",print_r($custom_post),"</pre>";
          $data = $custom_post;      
 
         function usort_reorder($a,$b){
            $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'Name'; //If no sort, default to title
            $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'asc'; //If no order, default to asc
            $result = strcmp($a[$orderby], $b[$orderby]); //Determine sort order
            return ($order==='asc') ? $result : -$result; //Send final sort direction to usort
        }
        usort($data, 'usort_reorder');
        
        $current_page = $this->get_pagenum();
        
        $total_items = count($data);
        
        $data = array_slice($data,(($current_page-1)*$per_page),$per_page);
        
        $this->items = $data;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }

  
}

class Enquiry_Review_init extends Enquiry_Review_List_Table {

  function __construct(){   

   add_action('admin_menu', array($this,'enquiry_review_add_menu_items'));
   add_filter('set-screen-option', array($this,'enquiry_review_screen_option'), 10, 3);
   add_filter('manage_toplevel_page_enquiry_review_columns',array($this,'enquiry_review_set_columns'));
  
 }
  
   function enquiry_review_add_menu_items(){
        global $enquiry_review_page;  
        $enquiry_review_page = add_menu_page('Enquiry Review List Table', 'Enquiry Review', 
                               'activate_plugins', 'enquiry_review', array($this,'enquiry_review_render_list_page'));
        add_action("load-$enquiry_review_page", array($this,"enquiry_review_screen_options"));
    } 
    function enquiry_review_screen_options() {
       global $enquiry_review_page;
       $screen = get_current_screen();
      // get out of here if we are not on our settings page
	   if(!is_object($screen) || $screen->id != $pippin_sample_page)
		  return;
       $args = array(
		'label' => __('Members per page', 'pippin'),
		'default' => 10,
		'option' => 'pippin_per_page'
	   );
	  add_screen_option( 'per_page', $args );
   }
   function enquiry_review_set_screen_option($status, $option, $value) {
	if ( 'pippin_per_page' == $option ) return $value;
   }
 
   function enquiry_review_set_columns( ) {
     $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'Name'     => 'Name',
            'Email'    => 'Email',
            'Country'  => 'Country',
            'Phone'  => 'Phone Number',
            'Time_of_Stay'  => 'Time of Stay', 
            'action'  => 'Action'
        );

     return $columns;
   }
   
   function enquiry_review_render_list_page(){
    
    //Create an instance of our package class...
    $enquiryReviewListTable = new Enquiry_Review_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $enquiryReviewListTable->prepare_items();
    
    ?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2>Enquiry Review List</h2>
        
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $enquiryReviewListTable->display() ?>
        </form>
        
    </div>
    <?php
  }
  

}

   

