<?php global $smartbiz; ?>
<div style="clear: both; height: 20px;">&nbsp;</div>
</div>
</div>
<b class="border_radius page"><b class="r5">&nbsp;</b><b class="r4">&nbsp;</b><b class="r3">&nbsp;</b><b class="r2">&nbsp;</b><b class="r1">&nbsp;</b></b>
<!-- End page -->
</div>

<div id="footer">
	<p><?php _e( 'Designed by ', 'smartbiz' ) ?><?php $smartbiz_theme=get_theme_data(
get_template_directory().'/style.css' ); echo $smartbiz_theme['Author'];
?>. <?php _e( 'Powered by', 'smartbiz' ) ?> <a href="http://wordpress.org/">WordPress</a>.</p>
	<?php wp_footer(); ?>
	<br>
	<?php
		if ($smartbiz->statisticsCode() != '') {
			echo $smartbiz->statisticsCode();
		}
	?>
</div>


</body>
</html>
