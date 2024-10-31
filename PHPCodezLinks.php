<?php
/**
* Plugin Name: PHPCodez Links
* Plugin URI: http://phpcodez.com/
* Description: A Widget That Displays Links
* Version: 0.1
* Author: Pramod T P
* Author URI: http://phpcodez.com/
*/

add_action( 'widgets_init', 'wpc_links_widgets' );

function wpc_links_widgets() {
	register_widget( 'wpclinksWidget' );
}

class wpclinksWidget extends WP_Widget {
	function wpclinksWidget() {
		$widget_ops = array( 'classname' => 'wpcClass', 'description' => __('A Widget That Displays Links.', 'wpcClass') );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wpc-links' );
		$this->WP_Widget( 'wpc-links', __('PHPCodez Links', ''), $widget_ops, $control_ops );
	}

	
	function widget( $args, $instance ) {
		extract( $args );
		global $wpdb;
		if($instance['link_count']) $limit =" LIMIT 0,".$instance['link_count'];
		if($instance['link_category_count']) $limitCategory =" LIMIT 0,".$instance['link_category_count'];
	
		if($instance['link_sort']) {
			$link_order_by =" ORDER BY ".$instance['link_sort'];
			if($instance['link_order']) $link_order_by .=" " .$instance['link_order'];
		}	
		
		if($instance['link_category_sort']){
			$category_order_by =" ORDER BY ".$instance['link_category_sort'];
			if($instance['link_category_order']) $category_order_by .=" " .$instance['link_category_order'];
		}	
	
		if($instance['link_category']) $link_category .=" AND  c.term_id  IN(".$instance['link_category'].")  ";
		
		$linkCategoryQry = "SELECT c.*,ct.* FROM {$wpdb->prefix}terms as c JOIN  {$wpdb->prefix}term_taxonomy as ct  ON c.term_id=ct.term_id 
				WHERE ct.taxonomy='link_category' $link_category  $category_order_by $limitCategory";
		$linkCategoryData	=	$wpdb->get_results($linkCategoryQry);	
?>
	<div class="arch_box">
		<?php if($instance['link_title']) { $haveLinks=1;?>
			<div class="side_hd">
				<h3><?php echo $instance['link_title'] ?></h3>
			</div>
		<?php } ?>
		<div class="sider_mid">
			<ul>
			<?php foreach($linkCategoryData as $key=>$linkCategory) { $haveLinkCategory=1;$haveLinks=0;?>
				<li><?php echo $linkCategory->name; ?></li>
			<?php
				$linkeQry="SELECT * FROM wp_links as link INNER JOIN wp_term_relationships ON (link.link_id = wp_term_relationships.object_id)
				   INNER JOIN wp_term_taxonomy ON (wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id) 
				   AND wp_term_taxonomy.taxonomy = 'link_category'
				   INNER JOIN {$wpdb->prefix}terms as c ON c.term_id=wp_term_taxonomy.term_id
				   WHERE c.slug='".$linkCategory->slug."' $link_order_by $limit";
				$linksData = $wpdb->get_results($linkeQry);
			?>	
			<ul>
				<?php foreach($linksData as $key=>$link) { $haveLinks=1;?>
					<li><a target="_blank" href="<?php echo $link->link_url ?>"><?php echo $link->link_name ?></a></li>
				<?php } ?>
				<?php if(!$haveLinks){ ?>
				<li>No links Are Added Yet</li>
			<?php } ?>
			</ul>
			<?php } ?>
			<?php if(!$haveLinkCategory){ ?>
				<li>No links Are Added Yet</li>
			<?php } ?>
			</ul>	
			
		</div>	
	</div>
<?php

}


function update( $new_instance, $old_instance ) {
	$instance = $old_instance;
	$instance['link_title']				=  $new_instance['link_title'] ;
	
	$instance['link_sort'] 				=  $new_instance['link_sort'] ;
	$instance['link_order'] 			=  $new_instance['link_order'] ;
	$instance['link_category_sort'] 	=  $new_instance['link_category_sort'] ;
	
	$instance['link_category_order'] 	=  $new_instance['link_category_order'] ;
	
	$instance['link_count'] 			=  $new_instance['link_count'] ;
	$instance['link_category_count'] 	=  $new_instance['link_category_count'] ;
	
	$instance['link_category'] 			=  implode(",",$new_instance['link_category']) ;
	return $instance;
}

function form( $instance ) {?>
	<p>
		<label for="<?php echo $this->get_field_id( 'link_title' ); ?>"><?php _e('Title', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'link_title' ); ?>" name="<?php echo $this->get_field_name( 'link_title' ); ?>" value="<?php echo $instance['link_title'] ?>"  type="text" width="99%" />
	</p>
	<?php
		global $wpdb;
		$linkCategoryQry = "SELECT c.*,ct.* FROM {$wpdb->prefix}terms as c JOIN  {$wpdb->prefix}term_taxonomy as ct  ON c.term_id=ct.term_id 
				WHERE ct.taxonomy='link_category' ";
		$linkCategoryData	=	$wpdb->get_results($linkCategoryQry);		
	?>
	<p>
		<label for="<?php echo $this->get_field_name( 'link_category' ); ?>"><?php _e('Link Categories', 'wpclass'); ?></label>
		<select id="<?php echo $this->get_field_name( 'link_category' ); ?>" name="<?php echo $this->get_field_name( 'link_category' ); ?>[]" multiple="multiple">
		<?php foreach($linkCategoryData as $key=>$linkCategory) { ?>
			<option value="<?php echo $linkCategory->term_id; ?>"  <?php if(in_array($linkCategory->term_id,explode(",",$instance['link_category']))) echo 'selected="selected"'; ?>>
				<?php echo $linkCategory->name; ?> 
			</option>
		<?php } ?>		
		</select>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'link_category_count' ); ?>"><?php _e('Number of link cateories . for "0" or "No Value" It will list all the link categories', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'link_category_count' ); ?>" name="<?php echo $this->get_field_name( 'link_category_count' ); ?>" value="<?php echo $instance['link_category_count'] ?>"  type="text" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_name( 'link_category_sort' ); ?>"><?php _e('Link Category Order BY ', 'wpclass'); ?></label>
		<select id="<?php echo $this->get_field_name( 'link_category_sort' ); ?>" name="<?php echo $this->get_field_name( 'link_category_sort' ); ?>">
			<option value="name"  <?php if($instance['link_category_sort']=="link_name") echo 'selected="selected"'; ?>>Name</option>
			<option value="c.term_id	"  <?php if($instance['link_category_sort']=="c.term_id") echo 'selected="selected"'; ?>>ID</option>
		</select>
		<select id="<?php echo $this->get_field_name( 'link_category_order' ); ?>" name="<?php echo $this->get_field_name( 'link_category_order' ); ?>">
			<option value="ASC" <?php if($instance['link_category_order']=="ASC") echo 'selected="selected"'; ?>>ASC</option>
			<option value="DESC" <?php if($instance['link_category_order']=="DESC") echo 'selected="selected"'; ?>>DESC</option>
		</select>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'link_count' ); ?>"><?php _e('Number of links . for "0" or "No Value" It will list all the links', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'link_count' ); ?>" name="<?php echo $this->get_field_name( 'link_count' ); ?>" value="<?php echo $instance['link_count'] ?>"  type="text" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_name( 'link_sort' ); ?>"><?php _e('Links Order BY', 'wpclass'); ?></label>
		<select id="<?php echo $this->get_field_name( 'link_sort' ); ?>" name="<?php echo $this->get_field_name( 'link_sort' ); ?>">
			<option value="link_name"  <?php if($instance['link_sort']=="link_name") echo 'selected="selected"'; ?>>Name</option>
			<option value="link_id	"  <?php if($instance['link_sort']=="c.term_id") echo 'selected="selected"'; ?>>ID</option>
		</select>
		<select id="<?php echo $this->get_field_name( 'link_order' ); ?>" name="<?php echo $this->get_field_name( 'link_order' ); ?>">
			<option value="ASC" <?php if($instance['link_order']=="ASC") echo 'selected="selected"'; ?>>ASC</option>
			<option value="DESC" <?php if($instance['link_order']=="DESC") echo 'selected="selected"'; ?>>DESC</option>
		</select>
	</p>
	
	
<?php
	}
}

?>