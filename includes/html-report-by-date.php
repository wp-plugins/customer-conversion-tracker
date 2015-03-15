<?php
/**
 * Admin View: Report by Date (with date filters)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div id="poststuff" class="woocommerce-reports-wide">
        <div class="postbox tabs">
                <?php if ( empty( $hide_sidebar ) ) : ?>
                                        <?php if ( $legends = $this->get_chart_legend() ) : ?>
                                                <ul class="chart-legend">
                                                        <?php foreach ( $legends as $legend ) : ?>
                                                                <li style="border-color: <?php echo $legend['color']; ?>" <?php if ( isset( $legend['highlight_series'] ) ) echo 'class="highlight_series" data-series="' . esc_attr( $legend['highlight_series'] ) . '"'; ?>>
                                                                        <?php echo $legend['title']; ?>
                                                                </li>
                                                        <?php endforeach; ?>
                                                </ul>
                                        <?php endif; ?>
        <?php  endif;  ?>

</div>
</div>
                              