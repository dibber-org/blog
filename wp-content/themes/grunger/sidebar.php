    	<div id="left">
        
			<?php $noSb=0;
			if(!function_exists("dynamic_sidebar") || !dynamic_sidebar("sidebarOrange")):?>
					
			<?php $noSb++; endif;?>
			<?php if(!function_exists("dynamic_sidebar") || !dynamic_sidebar("sidebarGray")):?>
			<?php $noSb++; endif;?>
			<?php if(!function_exists("dynamic_sidebar") || !dynamic_sidebar("sidebarGreen")):?>
			
			<?php $noSb++; endif;?>
            
           <?php if($noSb==3):?>
		   <div class="leftLinks">
				<h2 class="bg1">Categories</h2><div class="widgetContent" style="overflow:hidden;">	
				<ul>		
				<?php wp_list_categories("title_li=");?></ul>
			</div></div>
			<div class="leftLinks">
				<h2 class="bg1">Pages</h2><div class="widgetContent" style="overflow:hidden;">	
				<ul>		
				<?php wp_list_pages("title_li=");?></ul>
			</div></div>
			<?php endif;?>
            	     
      
        </div><!--end fo left side-->