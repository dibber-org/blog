<?php get_header();?>
    
    <div style="clear:both"></div>
    
    <div id="content">
    
    
		<?php get_sidebar();?>
        
        
        <div id="right">
        <?php if(have_posts()): while(have_posts()): the_post();?>
		
        
        	<div class="article">
            
            <div class="date">
                    
                    	<span class="dateNo"><?php the_time("j");?></span> 
                        <span class="th">     <?php $my_date = get_the_time('d', FALSE);
						if($my_date=="1") echo "st";
						else if($my_date=="2") echo "nd";
						else if ($my_date=="3") echo "rd";
						else echo "th";
					?></span>
                        <span class="month"><?php the_time("M");?></span>
                    
                    </div>
                
                	
                    <div class="artTitle">
                    
                    	<h3><a href="<?php the_permalink();?>"><?php the_title();?></a></h3>
                        <span class="by">Posted by <span class="nick"><?php the_author();?></span> to <span class="category"><?php the_category(" ,");?></span>
                    
                    </div> <!-- end of artTitle class-->
                    <div style="clear:both"></div>
              
                    <div class="text">
                    
                    
                    <?php the_content("<br/>Read More");?>
                    
                    </div> <!-- end of text class-->
                
                	
                    
                        <span class="tag">Tags: <?php the_tags(" ");?> </span>
                     
                     	<span class="comm"><a href=" <?php comments_link(); ?> ">Comments</a></span>
                        
                    
                
            </div> <!-- end of article class-->
			
			<?php endwhile;?>
			<?php else:?>
						<h3> Sorry. We found nothing here.</h3>
			<div style="clear:both"></div>
			<p>Please try again with a new search term</p>
			<?php endif;?>     
			<div style="text-align:center;">
<?php posts_nav_link(' &#183; ', 'previous page', 'next page'); ?>
</div>

        </div> <!-- end of right side-->
    
    
    </div> <!-- end of CONTENT-->


<div style="clear:both"></div>



<?php get_footer();?>