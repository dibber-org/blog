<form method="get" id="searchform" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<div><input type="text" value="<?php the_search_query(); ?>" name="s" id="s" />

<input type="submit" id="searchsubmit" value="<?php esc_attr__( 'Search', 'smartbiz' ) ?>" />

</div>

</form>