<?php
/**
 * LaraPress 댓글 템플릿 — Fresh(SWN) / Classic(NYT) / Minimal(Basic) 공용
 * AJ 레이아웃은 single.php 내에서 직접 처리합니다.
 */

if ( post_password_required() ) {
    echo '<p class="lp-cmt-password-notice">비밀번호를 입력해야 댓글을 볼 수 있습니다.</p>';
    return;
}
?>
<div class="lp-comments" id="comments">

    <?php if ( have_comments() ) : ?>

    <h3 class="lp-cmts-heading">
        댓글
        <span class="lp-cmts-count"><?php comments_number( '0', '1', '%' ); ?></span>
    </h3>

    <ol class="lp-cmts-list">
        <?php
        wp_list_comments( [
            'style'       => 'ol',
            'type'        => 'comment',
            'short_ping'  => true,
            'avatar_size' => 40,
            'callback'    => 'lp_generic_comment_cb',
        ] );
        ?>
    </ol>

    <?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
    <nav class="lp-cmts-pager" aria-label="댓글 페이지">
        <?php previous_comments_link( '← 이전 댓글' ); ?>
        <?php next_comments_link( '다음 댓글 →' ); ?>
    </nav>
    <?php endif; ?>

    <?php endif; /* have_comments */ ?>

    <?php if ( comments_open() ) :
    comment_form( [
        'id_form'              => 'lp-cmt-form',
        'class_form'           => 'lp-cmt-form-inner',
        'title_reply'          => '댓글 작성',
        'title_reply_to'       => '%s에게 댓글 작성',
        'cancel_reply_link'    => '취소',
        'label_submit'         => '댓글 등록',
        'submit_button'        => '<input name="%1$s" type="submit" id="%2$s" class="lp-cmt-submit" value="%4$s">',
        'submit_field'         => '<p class="lp-cmt-submit-row">%1$s %2$s</p>',
        'comment_field'        => '<p class="lp-cmt-field lp-cmt-field--full"><label for="comment">댓글 <span class="required" aria-hidden="true">*</span></label><textarea id="comment" name="comment" rows="5" required></textarea></p>',
        'fields'               => [
            'author' => '<div class="lp-cmt-meta-fields"><p class="lp-cmt-field"><label for="author">이름 <span class="required" aria-hidden="true">*</span></label><input id="author" name="author" type="text" value="' . esc_attr( isset( $_COOKIE[ 'comment_author_' . COOKIEHASH ] ) ? $_COOKIE[ 'comment_author_' . COOKIEHASH ] : '' ) . '" required></p>',
            'email'  => '<p class="lp-cmt-field"><label for="email">이메일 <span class="required" aria-hidden="true">*</span></label><input id="email" name="email" type="email" value="' . esc_attr( isset( $_COOKIE[ 'comment_author_email_' . COOKIEHASH ] ) ? $_COOKIE[ 'comment_author_email_' . COOKIEHASH ] : '' ) . '" required></p></div>',
            'url'    => '',
        ],
        'comment_notes_before' => '',
        'comment_notes_after'  => '',
    ] );
    endif; ?>

</div><!-- /.lp-comments -->
