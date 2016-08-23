<?php
/*
Plugin Name: Rate Own Post
Version: 1.0
Plugin URI: http://nyasro.com/wordpress-plugin/rate-own-post/
Description: Rate your own post yourself
Author: nyasro
Author URI: http://nyasro.com/
License: GPLv2 or later (license.txt).
*/

add_filter('manage_posts_columns','ny_content_filter');
add_filter('manage_posts_custom_column','ny_content',2,10);
register_activation_hook(__FILE__,'add_new_rate_column');
add_action('wp_ajax_nyasro_rate','set_nyasro_rating');
add_shortcode('nyrating','add_ratings_SC');


function add_ratings_SC( $atts=array() )
{
	global $post;
	$rate		= $post->post_rating;
	
	$element	= '<style type="text/css">
					.nyasro_rating{
						height: 18px;
						overflow:hidden;
						}
					.nyasro_rating .rate{
						height: 18px;
						width: 18px;
						float:left;
						background:url('.plugins_url('rate.png',__FILE__).') no-repeat 0 0;
						}
					.nyasro_rating .rth{
						background-position:0 -18px;
						}
					.nyasro_rating .rtf{
						background-position:0 -36px;
						}
				  </style>';
	
	$element	.= '<div class="nyasro_rating">';

	$half		= $rate / 2;
	$ceil		= (int)ceil( $half );
	
	$title		= array('very poor','poor','good','very good','excellent');
	
	for( $i=1; $i<6; $i++)
	{
		$j = '';
		if($i<=$ceil)
		{
			if($i==$ceil && $ceil!==$half)
				$j = ' rth';
			else
				$j = ' rtf';
		}
		
		$element	.= '<div class="rate'.$j.'" title="'.$title[$i-1].'"></div>';
	}
	
	return $element.'</div>';
}

function add_new_rate_column()
{
	global $wpdb;
	if($wpdb->get_var('post_rating')!=='post_rating')
		$wpdb->query("ALTER TABLE $wpdb->posts
	ADD COLUMN post_rating INT(2) UNSIGNED AFTER comment_count");
}

function ny_content_filter( $content )
{
	$content['rating'] = 'Ratings';
	return $content;
}

$nyasro_rate_count = 0;
function ny_content( $column, $id)
{
	static $nyasro_rate_count;
	$nyasro_rate_count++;
	$scr	= 0;
	if($nyasro_rate_count===1)
		$scr	= 1;
	if($column==='rating')
		rating_display( $scr );	
}

function set_nyasro_rating()
{
	global $wpdb;
	$success = $wpdb->query("UPDATE $wpdb->posts SET post_rating=$_POST[rate] WHERE ID=$_POST[id]");
	if(!$success)
		echo 'Something went wrong. Your rating was not applied.';
	exit;
}
 
function rating_display( $scr)
{
	global $post;
	$rating		= (int)$post->post_rating;
	$img 				= plugins_url('rate.png',__FILE__);
	?>
    <?php if($scr === 1): ?>
	<style type="text/css">
		.nyasro_rating_box select{
			padding:3px 4px;
			width:50px;
			}
		
	</style>
    <script type="text/javascript">
    	jQuery(document).ready(function(e) {
            
			$box		= jQuery('.rating_select');
			
			$box.change(function(e) {
              $opt	= jQuery(this);
			  $id	= $opt.parent('.nyasro_rating_box').find('.post_id').val();

				data	= {
					
						action  : 'nyasro_rate',
						rate	: $opt.val(),
						id		: $id
					};
				jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',data,function(call){
					if(call) alert(call);
					});
            });
			
        });
			
    </script>
    <?php endif; ?>
    <div class="nyasro_rating_box">
   
    	<select class="rating_select" name="rate">
        <?php	for($i=1;$i<11;$i++)
				{
					$selected	= '';
					if($rating===$i) $selected = ' selected="selected"';
					echo '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
				}
		?>
        </select>
        <input type="hidden" class="post_id" value="<?php echo $post->ID; ?>" name="id" />
    </div>
    
<?php 
} ?>