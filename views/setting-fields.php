<?php if ( 'rasr_ribbon-position' == $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position-top-right" name="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position" type="radio" value="top-right" <?php checked( $this->ribbonPosition, 'top-right' ); ?> />
	<label for="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position-top-right"><span class="description">Top Right Corner.</span></label>
	<br />
	
	<input id="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position-top-left" name="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position" type="radio" value="top-left" <?php checked( $this->ribbonPosition, 'top-left' ); ?> />
	<label for="<?php echo esc_attr( self::PREFIX ); ?>ribbon-position-top-left"><span class="description">Top Left Corner.</span></label>

<?php elseif ( 'rasr_new-window' == $field['label_for'] ) : ?>
	
	<input id="<?php echo esc_attr( self::PREFIX ); ?>new-window" name="<?php echo esc_attr( self::PREFIX ); ?>new-window" type="checkbox" <?php checked( $this->newWindow, 'on' ); ?> />
	<label for="<?php echo esc_attr( self::PREFIX ); ?>new-window">
		<span class="description">
			If checked, the link to the NFS website open in a new window.<br />
			<strong>Note:</strong> Forcing links to open in a new window is <a href="http://uxdesign.smashingmagazine.com/2008/07/01/should-links-open-in-new-windows/">considered a bad practice</a>. Please consider leaving this off.
		</span>
	</label>

<?php elseif ( 'rasr_bottom-for-mobile' == $field['label_for'] ) : ?>

	<input id="<?php echo esc_attr( self::PREFIX ); ?>bottom-for-mobile" name="<?php echo esc_attr( self::PREFIX ); ?>bottom-for-mobile" type="checkbox" <?php checked( $this->bottomForMobile, 'on' ); ?> />
	<label for="<?php echo esc_attr( self::PREFIX ); ?>bottom-for-mobile">
		<span class="description">
			If checked, the ribbon will appear at the bottom of the page when viewed on a smartphone so that it doesn't overlap the header. Note that this won't work in Internet Explorer versions 8 and below, because they don't support modern web standards.
		</span>
	</label>

<?php endif; ?>