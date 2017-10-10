<?php
/**
 * The template for displaying answers
 *
 * @package DW Question & Answer
 * @since DW Question & Answer 1.4.3
 */

global $dwqa_general_settings;
$sort = isset( $_GET['sort'] ) ? esc_html( $_GET['sort'] ) : '';
$filter = isset( $_GET['filter'] ) ? esc_html( $_GET['filter'] ) : 'all';
?>
