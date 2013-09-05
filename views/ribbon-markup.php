<div id="<?php echo esc_attr( self::PREFIX ); ?>container" class="<?php echo esc_attr( $this->ribbonPosition . ( $this->bottomForMobile ? ' bottom' : '' ) ); ?>">
	<a href="http://wordpress.org/extend/plugins/re-abolish-slavery-ribbon/" <?php echo ( $this->newWindow ? 'target="_blank"' : '' ); ?>>
		<img id="<?php echo esc_attr( self::PREFIX ); ?>add-icon" src="<?php echo esc_url( plugins_url( 're-abolish-slavery-ribbon/images/add-to-website.png' ) ); ?>" alt="Add this ribbon to your WordPress website" title="Add this ribbon to your WordPress website" />
	</a>
	
	<a href="<?php echo esc_url( $this->imageLinkURL ); ?>" <?php echo ( $this->newWindow ? 'target="_blank"' : '' ); ?>>
		<img src="<?php echo esc_url( $this->imageLocation ); ?>" alt="re-abolish slavery" />
	</a>
</div>