<?php global $smartbiz; ?>
<div id="sidebar">
	<!-- Spot 1 -->
	<?php if ($smartbiz->hideSpot1() !== 'true' ){ ?>
		<div class="spot">
			<?php if ($smartbiz->spot1Content() != '') : ?>
				<?php echo $smartbiz->spot1Content(); ?>
			<?php else : ?>
				<p>You can replace this with your own text or code easily. Head into the <em>SmartBiz Options</em> menu and check out the Sidebar Spots section. Type some stuff in <b>Spot 1</b> box, click save, and your custom content will show up here.</p>
			<?php endif; ?>			
		</div>
	<?php } ?>
	<!-- End spot 1 -->
	
	<ul>
		<?php if ( !dynamic_sidebar( 'main_sidebar' ) ): ?>
		<?php endif; ?>
	</ul>

	<!-- Spot 2 -->
	<?php if ($smartbiz->hideSpot2() !== 'true' ){ ?>
		<div class="spot">
			<?php if ($smartbiz->spot2Content() != '') : ?>
				<?php echo $smartbiz->spot2Content(); ?>
			<?php else : ?>
				<p>Replace this with your own text or code by editing <b>Spot 2</b> in the Sidebar Spots section of the <em>SmartBiz Options</em> page.</p>
			<?php endif; ?>			
		</div>
	<?php } ?>
	<!-- End spot 2 -->
</div>
