<?php session_start();
/*
Template Name: Contact Page
*/
?>
<?php 
	$et_ptemplate_settings = array();
	$et_ptemplate_settings = maybe_unserialize( get_post_meta($post->ID,'et_ptemplate_settings',true) );
	
	$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;
	
	$et_regenerate_numbers = isset( $et_ptemplate_settings['et_regenerate_numbers'] ) ? (bool) $et_ptemplate_settings['et_regenerate_numbers'] : false;
		
	$et_error_message = '';
	$et_contact_error = false;
	
	if ( isset($_POST['et_contactform_submit']) ) {
		if ( !isset($_POST['et_contact_captcha']) || empty($_POST['et_contact_captcha']) ) {
			$et_error_message .= '<p>' . esc_html__('Make sure you entered the captcha. ','Notebook') . '</p>';
			$et_contact_error = true;
		} else if ( $_POST['et_contact_captcha'] <> ( $_SESSION['et_first_digit'] + $_SESSION['et_second_digit'] ) ) {			
			$et_numbers_string = $et_regenerate_numbers ? __('Numbers regenerated.') : '';
			$et_error_message .= '<p>' . esc_html__('You entered the wrong number in captcha. ','Notebook') . $et_numbers_string . '</p>';
			
			if ($et_regenerate_numbers) {
				unset( $_SESSION['et_first_digit'] );
				unset( $_SESSION['et_second_digit'] );
			}
			
			$et_contact_error = true;
		} else if ( empty($_POST['et_contact_name']) || empty($_POST['et_contact_email']) || empty($_POST['et_contact_subject']) || empty($_POST['et_contact_message']) ){
			$et_error_message .= '<p>' . esc_html__('Make sure you fill all fields. ','Notebook') . '</p>';
			$et_contact_error = true;
		}
		
		if ( !is_email( $_POST['et_contact_email'] ) ) {
			$et_error_message .= '<p>' . esc_html__('Invalid Email. ','Notebook') . '</p>';
			$et_contact_error = true;
		}
	} else {
		$et_contact_error = true;
		if ( isset($_SESSION['et_first_digit'] ) ) unset( $_SESSION['et_first_digit'] );
		if ( isset($_SESSION['et_second_digit'] ) ) unset( $_SESSION['et_second_digit'] );
	}
	
	if ( !isset($_SESSION['et_first_digit'] ) ) $_SESSION['et_first_digit'] = $et_first_digit = rand(1, 15);
	else $et_first_digit = $_SESSION['et_first_digit'];
	
	if ( !isset($_SESSION['et_second_digit'] ) ) $_SESSION['et_second_digit'] = $et_second_digit = rand(1, 15);
	else $et_second_digit = $_SESSION['et_second_digit'];
	
	if ( !$et_contact_error ) {
		$et_email_to = ( isset($et_ptemplate_settings['et_email_to']) && !empty($et_ptemplate_settings['et_email_to']) ) ? $et_ptemplate_settings['et_email_to'] : get_site_option('admin_email');
				
		$et_site_name = is_multisite() ? $current_site->site_name : get_bloginfo('name');	
		wp_mail($et_email_to, sprintf( '[%s] ' . esc_html($_POST['et_contact_subject']), $et_site_name ), esc_html($_POST['et_contact_message']),'From: "'. esc_html($_POST['et_contact_name']) .'" <' . esc_html($_POST['et_contact_email']) . '>');
		
		$et_error_message = '<p>' . esc_html__('Thanks for contacting us','Notebook') . '</p>';
	}
?>

<?php get_header(); ?>

<div id="regular_content">
	<?php get_template_part('includes/breadcrumbs','index'); ?>

	<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>				
		<article class="single_view">
			<h1 class="main_title"><?php the_title(); ?></h1>

			<div id="post_content">			
				<?php if (get_option('notebook_page_thumbnails') == 'on') { ?>
					<?php 
						$thumb = '';
						$width = apply_filters( 'notebook_single_image_width', 220 );
						$height = apply_filters( 'notebook_single_image_height', 220 );
						$classtext = '';
						$titletext = get_the_title();
						$thumbnail = get_thumbnail($width,$height,$classtext,$titletext,$titletext,false,'Single');
						$thumb = $thumbnail["thumb"];
					?>
					
					<?php if($thumb <> '') { ?>
						<div class="single-thumbnail">
							<?php print_thumbnail($thumb, $thumbnail["use_timthumb"], $titletext, $width, $height, $classtext); ?>
							<span class="post-overlay"></span>
						</div> 	<!-- end .post-thumbnail -->
					<?php } ?>
				<?php } ?>
			
				<?php the_content(); ?>
			
				<div id="et-contact">

					<div id="et-contact-message"><?php echo($et_error_message); ?> </div>
					
					<?php if ( $et_contact_error ) { ?>
						<form action="<?php echo esc_url( get_permalink($post->ID) ); ?>" method="post" id="et_contact_form">
							<div id="et_contact_left">
								<p class="clearfix">
									<label for="et_contact_name" class="et_hidden"><?php esc_attr_e('Name','Notebook'); ?></label>
									<input type="text" name="et_contact_name" value="<?php if ( isset($_POST['et_contact_name']) ) echo esc_attr($_POST['et_contact_name']); else esc_attr_e('Name','Notebook'); ?>" id="et_contact_name" class="input" />
								</p>
								
								<p class="clearfix">
									<label for="et_contact_email" class="et_hidden"><?php esc_attr_e('Email Address','Notebook'); ?></label>
									<input type="text" name="et_contact_email" value="<?php if ( isset($_POST['et_contact_email']) ) echo esc_attr($_POST['et_contact_email']); else esc_attr_e('Email Address','Notebook'); ?>" id="et_contact_email" class="input" />
								</p>
								
								<p class="clearfix">
									<label for="et_contact_subject" class="et_hidden"><?php esc_attr_e('Subject','Notebook'); ?></label>
									<input type="text" name="et_contact_subject" value="<?php if ( isset($_POST['et_contact_subject']) ) echo esc_attr($_POST['et_contact_subject']); else esc_attr_e('Subject','Notebook'); ?>" id="et_contact_subject" class="input" />
								</p>
							</div> <!-- #et_contact_left -->
							
							<div id="et_contact_right">
								<p class="clearfix">
									<?php 
										esc_html_e('Captcha: ','Notebook');	
										echo '<br/>';
										echo esc_attr($et_first_digit) . ' + ' . esc_attr($et_second_digit) . ' = ';
									?>
									<input type="text" name="et_contact_captcha" value="<?php if ( isset($_POST['et_contact_captcha']) ) echo esc_attr($_POST['et_contact_captcha']); ?>" id="et_contact_captcha" class="input" size="2" />
								</p>
							</div> <!-- #et_contact_right -->
							
							<div class="clear"></div>
							
							<p class="clearfix">
								<label for="et_contact_message" class="et_hidden"><?php esc_attr_e('Message','Notebook'); ?></label>
								<textarea class="input" id="et_contact_message" name="et_contact_message"><?php if ( isset($_POST['et_contact_message']) ) echo esc_textarea($_POST['et_contact_message']); else echo esc_textarea( esc_attr_e('Message','Notebook') ); ?></textarea>
							</p>
								
							<input type="hidden" name="et_contactform_submit" value="et_contact_proccess" />
							
							<input type="reset" id="et_contact_reset" value="<?php esc_attr_e('Reset','Notebook'); ?>" />
							<input class="et_contact_submit" type="submit" value="<?php esc_attr_e('Submit','Notebook'); ?>" id="et_contact_submit" />
						</form>
					<?php } ?>
				</div> <!-- end #et-contact -->
				
				<div class="clear"></div>
				<?php wp_link_pages(array('before' => '<p><strong>'.esc_attr__('Pages','Notebook').':</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
				<?php edit_post_link(esc_attr__('Edit this page','Notebook')); ?>
			</div>
		</article> <!-- end .single_view -->
	<?php endwhile; // end of the loop. ?>
</div> <!-- end #regular_content -->
	
<?php get_footer(); ?>