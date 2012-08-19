<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type');?> charset=<?php bloginfo("charset"); ?>" />
<title><?php wp_title("&laquo;",true, "right"); bloginfo("name"); ?></title>

<link rel="stylesheet" href="<?php bloginfo('template_directory');?>/cssReset.css" type="text/css" media="screen" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url');?>" type="text/css" media="screen" />
<link rel="alternate" href="<?php bloginfo("rss2_url");?>" type="application/rss+xml" title="<?php bloginfo('name');?> RSS Feed" />
<link rel="alternate" href="<?php bloginfo("atom_url");?>" type="application/atom+xml" title="<?php bloginfo('name');?> Atom Feed" />
<link rel="pingback" href="<?php bloginfo('pingback_url');?>" />
<?php wp_head();?>

<style type="text/css"> 
img, div, a, li, a:hover,h2,span  { behavior: url(iepngfix.htc) }
</style> 
<!--[if IE 6]>
<link rel="stylesheet" href="<?php bloginfo('template_directory');?>/ie6.css" />
<![endif]-->

</head>


<body>

<div id="website">



	<div id="top">
    
    	<div id="header">
        
        	<h1 id="title"><a href="<?php bloginfo("url");?>"><?php  bloginfo("name");?></a></h1>
	<div style="float:right;">
	  <form id="searchBox" action="" method="get"> 
		   <p><input type="text" id="s" name="s" value=""/>
		   <input type="submit" value="Search" id="searchsubmit"/></p> 
	  </form>        
	</div>
            <p id="tagline"><?php bloginfo("description");?></p>

	
        </div><!-- end of header-->
       
        <div style="clear:both"></div>
        <div id="orange">
        <ul id="nav">
        
        	<li><a href="<?php bloginfo("url");?>" class="menu <?php if(is_home()):?>active<?php endif;?>">Home</a></li>
			<?php wp_list_pages("title_li=&depth=1");?>
        
        </ul> <!-- navigation-->
        </div>
    <div style="clear:both"></div>
    </div><!-- end of top div-->