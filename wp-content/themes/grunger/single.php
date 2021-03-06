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
                    
                    
                    <?php the_content("");?>
                    
                    </div> <!-- end of text class-->
                
                							
                    
                        <span class="tag">Tags: <?php the_tags(" ");?> </span>
                     
						<div class="singelNav"><div class="navigation"><p><?php wp_link_pages('before=<p>Page&after=</p>&next_or_number=number&pagelink=%'); ?></p></div></div>
                        
                    
                
            </div> <!-- end of article class-->
			
			<?php endwhile;?>
			<?php else:?>
			<?php endif;?>     
	<div class="article">
			<?php comments_template();?>
			</div>
			
        </div> <!-- end of right side-->
    
    
    </div> <!-- end of CONTENT-->


<div style="clear:both"></div>



<?php get_footer();?>