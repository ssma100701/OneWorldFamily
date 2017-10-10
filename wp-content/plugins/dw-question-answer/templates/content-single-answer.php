<?php
/**
 * The template for displaying single answers
 *
 * @package DW Question & Answer
 * @since DW Question & Answer 1.4.3
 */
?>
<div class="<?php echo dwqa_post_class() ?>">
	<?php if ( dwqa_current_user_can( 'edit_question', dwqa_get_question_from_answer_id() ) ) : ?>
		<?php $action = dwqa_is_the_best_answer() ? 'dwqa-unvote-best-answer' : 'dwqa-vote-best-answer' ; ?>
		<a class="dwqa-pick-best-answer" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'answer' => get_the_ID(), 'action' => $action ), admin_url( 'admin-ajax.php' ) ), '_dwqa_vote_best_answer' ) ) ?>"><?php _e( 'Best Answer', 'dwqa' ) ?></a>
	<?php elseif ( dwqa_is_the_best_answer() ) : ?>
		<span class="dwqa-pick-best-answer"><?php _e( 'Best Answer', 'dwqa' ) ?></span>
	<?php endif; ?>
	<div class="dwqa-answer-meta">
		<?php $user_id = get_post_field( 'post_author', get_the_ID() ) ? get_post_field( 'post_author', get_the_ID() ) : 0 ?>
		<?php printf( __( '<span><a href="%s">%s%s</a> %s answered %s ago</span>', 'dwqa' ), dwqa_get_author_link( $user_id ), get_avatar( $user_id, 48 ), get_the_author(), dwqa_print_user_badge( $user_id ), human_time_diff( get_post_time( 'U', true ) ) ) ?>
		<?php if ( 'private' == get_post_status() ) : ?>
			<span><?php _e( '&nbsp;&bull;&nbsp;', 'dwqa' ); ?></span>
			<span><?php _e( 'Private', 'dwqa' ) ?></span>
		<?php endif; ?>
		<span class="dwqa-answer-actions"><?php dwqa_answer_button_action(); ?></span>
	</div>
	<div class="dwqa-answer-content"><?php the_content(); ?></div>
	<?php comments_template(); ?>
</div>
