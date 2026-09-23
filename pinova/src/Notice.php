<?php

namespace Pinova;

defined( 'ABSPATH' ) || exit;

class Notice {

	public function __construct() {
		add_action( 'admin_notices', [ $this, 'admin_notices' ], 5 );
		add_action( 'wp_ajax_pinova_dismiss_notice', [ $this, 'dismiss_notice' ] );
	}

	public function admin_notices() {

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( $this->is_dismiss( 'all' ) ) {
			return;
		}

		foreach ( $this->notices() as $notice ) {

			if ( ! $notice['condition'] || $this->is_dismiss( $notice['id'] ) ) {
				continue;
			}

			$dismissible    = $notice['dismiss'] ? 'is-dismissible' : '';
			$notice_content = strip_tags( $notice['content'], '<p><a><b><img><ul><ol><li>' );

			printf(
				'<div class="notice pinova_notice notice-%s %s" id="pinova_%s"><p>%s</p></div>',
				esc_attr( $notice['class'] ?? 'success' ),
				esc_attr( $dismissible ),
				esc_attr( $notice['id'] ),
				$notice_content
			);

			break;
		}

		?>
		<script type="text/javascript">
            jQuery(function ($) {

                $(document.body).on('click', '.pinova_notice .notice-dismiss', function () {

                    let notice = $(this).closest('.pinova_notice').attr('id');

                    if (notice !== undefined && notice.indexOf('pinova_') !== -1) {

                        notice = notice.replace('pinova_', '');

                        $.ajax({
                            url: "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>",
                            type: 'post',
                            data: {
                                notice: notice,
                                action: 'pinova_dismiss_notice',
                                nonce: "<?php echo esc_attr( wp_create_nonce( 'pinova_dismiss_notice' ) ); ?>"
                            }
                        });

                    }

                });

            });
		</script>
		<?php
	}

	public function notices(): array {
		global $pagenow;

		$page = sanitize_text_field( $_GET['page'] ?? '' );
		$tab  = sanitize_text_field( $_GET['tab'] ?? '' );

		$notices = [
			[
				'id'        => 'plain_permalink_structure',
				'class'     => 'warning',
				'content'   => '<b>پینوا:</b> ساختار پیوند یکتای وردپرس در حالت «ساده» تنظیم شده است و باعث اختلال در عملکرد پینوا و دریافت خطای ۴۰۴ می‌شود. لطفاً از بخش <a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">تنظیمات > پیوندهای یکتا</a>، ساختار دیگری را انتخاب و ذخیره کنید.',
				'condition' => empty( get_option( 'permalink_structure' ) ),
				'dismiss'   => false,
			],
		];

		$_notices = get_option( 'pinova_notices', [] );

		foreach ( $_notices['notices'] ?? [] as $_notice ) {

			$_notice['condition'] = 1;

			$rules = $_notice['rules'] ?? [];

			if ( isset( $rules['pagenow'] ) && $rules['pagenow'] !== $pagenow ) {
				$_notice['condition'] = 0;
			}

			if ( isset( $rules['page'] ) && $rules['page'] !== $page ) {
				$_notice['condition'] = 0;
			}

			if ( isset( $rules['tab'] ) && $rules['tab'] !== $tab ) {
				$_notice['condition'] = 0;
			}

			if ( isset( $rules['active'] ) && is_plugin_inactive( $rules['active'] ) ) {
				$_notice['condition'] = 0;
			}

			if ( isset( $rules['inactive'] ) && is_plugin_active( $rules['inactive'] ) ) {
				$_notice['condition'] = 0;
			}

			unset( $_notice['rules'] );

			array_unshift( $notices, $_notice );
		}

		return $notices;
	}

	public function dismiss_notice() {

		check_ajax_referer( 'pinova_dismiss_notice', 'nonce' );

		$this->set_dismiss( sanitize_text_field( $_POST['notice'] ?? '' ) );

		wp_die();
	}

	public function set_dismiss( string $notice_id ) {

		$notices = wp_list_pluck( $this->notices(), 'dismiss', 'id' );

		if ( isset( $notices[ $notice_id ] ) && $notices[ $notice_id ] ) {
			update_option(
				'pinova_dismiss_notice_' . $notice_id,
				time() + intval( $notices[ $notice_id ] ),
				false
			);

			update_option(
				'pinova_dismiss_notice_all',
				time() + DAY_IN_SECONDS,
				false
			);
		}
	}

	public function is_dismiss( string $notice_id ): bool {
		return intval( get_option( 'pinova_dismiss_notice_' . $notice_id ) ) >= time();
	}

}