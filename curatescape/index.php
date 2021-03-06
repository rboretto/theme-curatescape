<?php 
$stealthMode=(get_theme_option('stealth_mode')==1)&&(is_allowed('Items', 'edit')!==true) ? true : false;
$classname='home'.($stealthMode ? ' stealth' : null);
if ($stealthMode) queue_css_file('stealth');
echo head(array('maptype'=>'focusarea','bodyid'=>'home','bodyclass'=>$classname)); 
?>
<?php
if ($stealthMode){
	include_once('stealth-index.php');
}
else{
//if not stealth mode, do everything else
?>	

	<div id="content" role="main">
	<section class="map">
		<figure>
			<?php echo mh_map_type('focusarea',null,null); ?>
		</figure>
	</section>	
	<article id="homepage" class="page show">
		<?php echo homepage_widget_sections();?>
	</article>
	</div> <!-- end content -->

<?php
//end stealth mode else statement
}?>

<?php echo foot(); ?>