<div id="<?php echo self::PREFIX; ?>container" class="<?php echo $this->ribbonPosition . ( $this->bottomForMobile ? ' bottom' : '' ); ?>">
	<a href="http://wordpress.org/extend/plugins/re-abolish-slavery-ribbon/" <?php echo ( $this->newWindow ? 'target="_blank"' : '' ); ?>>
		<img id="<?php echo self::PREFIX; ?>add-icon" src="<?php echo plugins_url( 're-abolish-slavery-ribbon/images/add-to-website.png' ) ?>" alt="Add this ribbon to your WordPress website" title="Add this ribbon to your WordPress website" />
	</a>
	
	<a href="https://secure.notforsalecampaign.org/about/slavery/" <?php echo ( $this->newWindow ? 'target="_blank"' : '' ); ?>>
		<img src="<?php echo plugins_url( 're-abolish-slavery-ribbon/images/ribbon-'. $this->ribbonPosition .'.png' ) ?>" alt="re-abolish slavery" />
	</a>
</div>