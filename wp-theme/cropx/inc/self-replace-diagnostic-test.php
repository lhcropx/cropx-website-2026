<?php
/**
 * ONE-TIME DIAGNOSTIC TOOL — deliberate self-replace corruption repro test.
 *
 * Background (Sep 2026): a sitewide unicode-escape corruption (bare `u003c`/
 * `u003e`/`u0026`/`u0027`/`u0022` tokens with the leading backslash missing,
 * plus stray "n"/"nn" text between tag boundaries where an escaped `\n`
 * should be) hit the site three times — Sep 10, Sep 11, and again Sep 14
 * despite a fix believed to have disabled the trigger. TranslatePress was
 * identified as the likely source on Sep 11, but the Sep 14 recurrence means
 * that's still not fully confirmed.
 *
 * Lauren asked to deliberately run a search/replace test "similar to the
 * ones that seem to have caused it before" to help pin down the trigger.
 * This tool searches for a real word ("aggregates") and replaces it with
 * ITSELF — byte-for-byte identical text — then saves via the exact same
 * `wp_update_post()` code path used by the CropX System → Platform rename
 * tool and the bgColor migration tool. The point of a no-op replacement is
 * to isolate one variable: does simply round-tripping a post's content
 * through this save pipeline introduce the corruption, regardless of
 * TranslatePress or of the replacement text itself?
 *
 *   - If corruption appears after running this on a post: the save pathway
 *     itself (or something hooked into it — a filter, a sanitizer, REST vs.
 *     admin-ajax handling, etc.) is implicated independent of TranslatePress.
 *   - If nothing breaks: that narrows suspicion back toward TranslatePress
 *     specifically, or toward something else this tool's code path doesn't
 *     exercise (e.g. the REST API save path used by the block editor itself,
 *     which this tool does NOT use — it calls wp_update_post() directly,
 *     same as the other admin tools).
 *
 * Safety: every save here goes through wp_update_post(), which WordPress
 * uses to auto-create a normal post revision before saving — so if this
 * *does* reproduce corruption, the pre-test content is recoverable from
 * Revisions on that post, exactly like the Sep 10 incident recovery. This
 * tool only ever touches ONE post per click (no bulk-apply), and only posts
 * Lauren explicitly runs it on from the report below.
 *
 * This is a temporary diagnostic tool, not a permanent fix — remove this
 * file (and its guarded require in functions.php) once the test has served
 * its purpose.
 *
 * Usage: Tools → CropX Diagnostics → Self-Replace Corruption Test.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The word to search for. Change here if testing a different term. */
function cropx_srdt_pattern(): string {
	return '/\baggregates\b/i';
}

/**
 * Post types to scan. Same list/rationale as the CropX System → Platform
 * rename tool and the original unicode-escape-repair tool: excludes
 * internal/system types that never hold real editorial content, includes
 * wp_block (Synced Patterns).
 */
function cropx_srdt_relevant_post_types(): array {
	$excluded = array(
		'revision',
		'attachment',
		'nav_menu_item',
		'customize_changeset',
		'custom_css',
		'oembed_cache',
		'user_request',
		'wp_global_styles',
		'wp_navigation',
		'wp_font_family',
		'wp_font_face',
	);
	$types = get_post_types( array(), 'names' );
	return array_values( array_diff( $types, $excluded ) );
}

function cropx_srdt_fetch_candidates(): array {
	global $wpdb;

	$post_types = cropx_srdt_relevant_post_types();
	if ( empty( $post_types ) ) {
		return array();
	}

	$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
	$statuses     = array( 'publish', 'draft', 'pending', 'private', 'future' );
	$status_ph    = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

	$sql = "SELECT ID, post_type, post_status, post_title, post_content
			FROM {$wpdb->posts}
			WHERE post_type IN ($placeholders)
			AND post_status IN ($status_ph)";

	$rows = $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $post_types, $statuses ) ) ); // phpcs:ignore

	return $rows ?: array();
}

function cropx_srdt_context_snippet( string $content, int $offset, int $match_len, int $pad = 50 ): string {
	$start   = max( 0, $offset - $pad );
	$end     = min( strlen( $content ), $offset + $match_len + $pad );
	$snippet = substr( $content, $start, $end - $start );
	$snippet = preg_replace( '/\s+/', ' ', $snippet );
	$prefix  = $start > 0 ? '…' : '';
	$suffix  = $end < strlen( $content ) ? '…' : '';
	return $prefix . trim( $snippet ) . $suffix;
}

/**
 * Scan a content string for the two known corruption signatures from the
 * Sep 10/11/14 incidents:
 *   1. "bare tokens" — u003c/u003e/u0026/u0027/u0022 etc. with NO leading
 *      backslash (a properly-escaped JSON unicode sequence always has one;
 *      losing it is exactly what happened before).
 *   2. "newline runs" — a run of literal "n" characters sitting directly
 *      between an uncorrected u003e and u003c token, where an escaped `\n`
 *      should be.
 * Also validates that every `cropx/*` block's JSON attribute blob still
 * parses cleanly — the same check used during the Sep 10 recovery, before
 * ever saving repaired content.
 */
function cropx_srdt_corruption_scan( string $content ): array {
	$bare_tokens = array();
	if ( preg_match_all( '/(?<!\\\\)u00[0-9a-fA-F]{2}/', $content, $m, PREG_OFFSET_CAPTURE ) ) {
		foreach ( $m[0] as $hit ) {
			$bare_tokens[] = array(
				'token'   => $hit[0],
				'context' => cropx_srdt_context_snippet( $content, $hit[1], strlen( $hit[0] ) ),
			);
		}
	}

	$newline_runs = array();
	if ( preg_match_all( '/u003e(n+)u003c/', $content, $m, PREG_OFFSET_CAPTURE ) ) {
		foreach ( $m[0] as $hit ) {
			$newline_runs[] = array(
				'match'   => $hit[0],
				'context' => cropx_srdt_context_snippet( $content, $hit[1], strlen( $hit[0] ) ),
			);
		}
	}

	$invalid_json_blocks = array();
	if ( preg_match_all( '/<!--\s*wp:cropx\/([a-z0-9-]+)\s+(\{.*?\})\s*(\/)?-->/s', $content, $m, PREG_OFFSET_CAPTURE ) ) {
		foreach ( $m[0] as $i => $hit ) {
			$block_name = $m[1][ $i ][0];
			$json_blob  = $m[2][ $i ][0];
			json_decode( $json_blob );
			if ( JSON_ERROR_NONE !== json_last_error() ) {
				$invalid_json_blocks[] = array(
					'block' => 'cropx/' . $block_name,
					'error' => json_last_error_msg(),
					'context' => cropx_srdt_context_snippet( $content, $hit[1], strlen( $hit[0] ), 30 ),
				);
			}
		}
	}

	return array(
		'bare_tokens'          => $bare_tokens,
		'newline_runs'         => $newline_runs,
		'invalid_json_blocks'  => $invalid_json_blocks,
		'clean'                => empty( $bare_tokens ) && empty( $newline_runs ) && empty( $invalid_json_blocks ),
	);
}

/**
 * Scan every candidate post for the test word, returning context for each
 * hit so Lauren can pick exactly which post(s) to run the test on.
 */
function cropx_srdt_scan(): array {
	$report = array();
	$re     = cropx_srdt_pattern();

	foreach ( cropx_srdt_fetch_candidates() as $row ) {
		$content = (string) $row->post_content;
		if ( '' === $content || ! preg_match_all( $re, $content, $matches, PREG_OFFSET_CAPTURE ) ) {
			continue;
		}

		$contexts = array();
		foreach ( array_slice( $matches[0], 0, 5 ) as $m ) {
			list( $matched_text, $pos ) = $m;
			$contexts[] = cropx_srdt_context_snippet( $content, $pos, strlen( $matched_text ) );
		}

		$report[ $row->ID ] = array(
			'post_id'     => (int) $row->ID,
			'post_title'  => $row->post_title ?: '(untitled)',
			'post_type'   => $row->post_type,
			'post_status' => $row->post_status,
			'edit_link'   => get_edit_post_link( $row->ID, 'raw' ),
			'count'       => count( $matches[0] ),
			'contexts'    => $contexts,
		);
	}

	return $report;
}

/**
 * The self-replace: matches the word and returns it completely unchanged.
 * The point is a byte-identical replacement — see file doc comment.
 */
function cropx_srdt_self_replace_string( string $text ): string {
	return preg_replace_callback(
		cropx_srdt_pattern(),
		function ( $m ) {
			return $m[0]; // identity — no actual change
		},
		$text
	);
}

/**
 * Run the test on a single post: self-replace + wp_update_post(), then
 * re-fetch and corruption-scan the saved result. Returns a full report.
 */
function cropx_srdt_run_test( int $post_id ): ?array {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return null;
	}

	$before_content = $post->post_content;
	$before_scan    = cropx_srdt_corruption_scan( $before_content );
	$new_content    = cropx_srdt_self_replace_string( $before_content );

	// wp_update_post()/wp_insert_post() -> $wpdb->update() ALWAYS runs
	// wp_unslash() on incoming field values before writing to the DB (the
	// same convention $_POST data follows). If we pass already-clean,
	// unslashed content here, that unslash step strips the LEGITIMATE
	// single backslashes out of any </&/\n JSON-escape sequences
	// living inside a block's comment attributes — turning < into a
	// bare, broken u003c. wp_slash() here cancels that out. THIS turned out
	// to be the real root cause of the whole unicode-escape corruption saga
	// (see PROGRESS.md, Sep 15) — not TranslatePress.
	wp_update_post( array(
		'ID'           => $post_id,
		'post_content' => wp_slash( $new_content ),
	) );

	// Re-fetch fresh from the DB — not from the in-memory $new_content — so
	// we're checking what actually got persisted, not what we think we sent.
	clean_post_cache( $post_id );
	$after_post    = get_post( $post_id );
	$after_content = $after_post ? $after_post->post_content : '';
	$after_scan    = cropx_srdt_corruption_scan( $after_content );

	return array(
		'post_id'            => $post_id,
		'post_title'         => $post->post_title,
		'before_length'      => strlen( $before_content ),
		'after_length'       => strlen( $after_content ),
		'byte_identical'     => $before_content === $after_content,
		'before_scan'        => $before_scan,
		'after_scan'         => $after_scan,
		'newly_introduced'   => $before_scan['clean'] && ! $after_scan['clean'],
		'revisions_link'     => get_edit_post_link( $post_id, 'raw' ),
	);
}

add_action( 'admin_menu', function () {
	add_management_page(
		__( 'Self-Replace Corruption Test', 'cropx' ),
		__( 'CropX Diagnostics', 'cropx' ),
		'manage_options',
		'cropx-self-replace-diagnostic',
		'cropx_render_self_replace_diagnostic_page'
	);
} );

function cropx_render_self_replace_diagnostic_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report      = array();
	$did_scan    = false;
	$test_result = null;

	if ( isset( $_POST['cropx_srdt_action'] ) && check_admin_referer( 'cropx_srdt_action', 'cropx_srdt_nonce' ) ) {
		$action = sanitize_text_field( wp_unslash( $_POST['cropx_srdt_action'] ) );

		if ( 'scan' === $action ) {
			$report   = cropx_srdt_scan();
			$did_scan = true;
		} elseif ( 'run_test' === $action ) {
			$post_id     = isset( $_POST['test_post_id'] ) ? (int) $_POST['test_post_id'] : 0;
			$test_result = $post_id ? cropx_srdt_run_test( $post_id ) : null;
			$report      = cropx_srdt_scan();
			$did_scan    = true;
		}
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Self-Replace Corruption Test', 'cropx' ); ?></h1>

		<div class="notice notice-warning" style="padding:12px;">
			<p><strong><?php esc_html_e( 'This is a deliberate diagnostic test, built at Lauren\'s request.', 'cropx' ); ?></strong></p>
			<p><?php esc_html_e( 'It searches for the word "aggregates" and replaces it with itself — byte-for-byte identical text — then saves via wp_update_post(), the same save path used by the CropX System → Platform rename tool and the bgColor migration tool. The goal is to test whether simply round-tripping content through this save pipeline reproduces the unicode-escape corruption seen on Sep 10/11/14, independent of TranslatePress.', 'cropx' ); ?></p>
			<p><?php esc_html_e( 'Every save creates a normal WordPress revision, so anything this breaks is recoverable from that post\'s Revisions history. Only run this on a post you\'re prepared to check (and revert if needed) right after.', 'cropx' ); ?></p>
		</div>

		<?php if ( $test_result ) : ?>
			<div class="notice <?php echo $test_result['newly_introduced'] ? 'notice-error' : 'notice-success'; ?>" style="padding:14px;margin-top:1em;">
				<h2 style="margin-top:0;"><?php esc_html_e( 'Test result', 'cropx' ); ?> — <?php echo esc_html( $test_result['post_title'] ); ?> (#<?php echo (int) $test_result['post_id']; ?>)</h2>

				<p>
					<strong><?php esc_html_e( 'Content length before/after:', 'cropx' ); ?></strong>
					<?php echo (int) $test_result['before_length']; ?> → <?php echo (int) $test_result['after_length']; ?> <?php esc_html_e( 'bytes', 'cropx' ); ?>
					&nbsp;|&nbsp;
					<strong><?php esc_html_e( 'Byte-identical to before:', 'cropx' ); ?></strong>
					<?php echo $test_result['byte_identical'] ? esc_html__( 'YES', 'cropx' ) : esc_html__( 'NO — content changed even though the replacement was a no-op', 'cropx' ); ?>
				</p>

				<?php if ( $test_result['newly_introduced'] ) : ?>
					<p style="font-size:16px;"><strong style="color:#a00;">⚠ <?php esc_html_e( 'Corruption was NOT present before this test and IS present after. This reproduces the bug via this save path, independent of TranslatePress.', 'cropx' ); ?></strong></p>
				<?php elseif ( ! $test_result['after_scan']['clean'] ) : ?>
					<p style="font-size:16px;"><strong style="color:#a00;">⚠ <?php esc_html_e( 'Corruption signatures are present, but they were ALSO present before this test ran — this post already had pre-existing corruption unrelated to this test.', 'cropx' ); ?></strong></p>
				<?php else : ?>
					<p style="font-size:16px;"><strong style="color:#0a0;">✓ <?php esc_html_e( 'No corruption signatures found after the test. This save path alone did not reproduce the bug on this post.', 'cropx' ); ?></strong></p>
				<?php endif; ?>

				<?php foreach ( array( 'before_scan' => __( 'Before', 'cropx' ), 'after_scan' => __( 'After', 'cropx' ) ) as $key => $label ) :
					$scan = $test_result[ $key ]; ?>
					<h3><?php echo esc_html( $label ); ?></h3>
					<?php if ( $scan['clean'] ) : ?>
						<p><em><?php esc_html_e( 'Clean — no bare tokens, no newline runs, no invalid block JSON.', 'cropx' ); ?></em></p>
					<?php else : ?>
						<?php if ( ! empty( $scan['bare_tokens'] ) ) : ?>
							<p><strong><?php echo count( $scan['bare_tokens'] ); ?> <?php esc_html_e( 'bare unicode token(s):', 'cropx' ); ?></strong></p>
							<ul style="list-style:disc;margin-left:2em;">
								<?php foreach ( array_slice( $scan['bare_tokens'], 0, 10 ) as $t ) : ?>
									<li><code><?php echo esc_html( $t['token'] ); ?></code> — <span style="color:#666;"><?php echo esc_html( $t['context'] ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php if ( ! empty( $scan['newline_runs'] ) ) : ?>
							<p><strong><?php echo count( $scan['newline_runs'] ); ?> <?php esc_html_e( 'newline-run corruption(s):', 'cropx' ); ?></strong></p>
							<ul style="list-style:disc;margin-left:2em;">
								<?php foreach ( $scan['newline_runs'] as $t ) : ?>
									<li><code><?php echo esc_html( $t['match'] ); ?></code> — <span style="color:#666;"><?php echo esc_html( $t['context'] ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php if ( ! empty( $scan['invalid_json_blocks'] ) ) : ?>
							<p><strong><?php echo count( $scan['invalid_json_blocks'] ); ?> <?php esc_html_e( 'block(s) with invalid JSON attributes:', 'cropx' ); ?></strong></p>
							<ul style="list-style:disc;margin-left:2em;">
								<?php foreach ( $scan['invalid_json_blocks'] as $t ) : ?>
									<li><code><?php echo esc_html( $t['block'] ); ?></code> — <?php echo esc_html( $t['error'] ); ?> — <span style="color:#666;"><?php echo esc_html( $t['context'] ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>

				<p style="margin-top:1em;">
					<a href="<?php echo esc_url( $test_result['revisions_link'] ); ?>" target="_blank"><?php esc_html_e( 'Open this post in the editor to view live rendering + Revisions', 'cropx' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<form method="post" style="margin-top:1.5em;">
			<?php wp_nonce_field( 'cropx_srdt_action', 'cropx_srdt_nonce' ); ?>
			<input type="hidden" name="cropx_srdt_action" value="scan">
			<?php submit_button( __( 'Scan for "aggregates"', 'cropx' ), 'primary', 'submit', false ); ?>
		</form>

		<?php if ( $did_scan ) : ?>
			<?php if ( empty( $report ) ) : ?>
				<div class="notice notice-info" style="margin-top:1em;"><p><?php esc_html_e( 'No occurrences of "aggregates" found.', 'cropx' ); ?></p></div>
			<?php else : ?>
				<h2 style="margin-top:2em;"><?php esc_html_e( 'Found on these posts', 'cropx' ); ?> (<?php echo count( $report ); ?>)</h2>
				<table class="widefat striped">
					<thead>
						<tr>
							<th style="width:22%"><?php esc_html_e( 'Post', 'cropx' ); ?></th>
							<th><?php esc_html_e( 'Occurrences — example context', 'cropx' ); ?></th>
							<th style="width:14%"><?php esc_html_e( 'Action', 'cropx' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $report as $row ) : ?>
							<tr>
								<td>
									<a href="<?php echo esc_url( $row['edit_link'] ); ?>" target="_blank"><?php echo esc_html( $row['post_title'] ); ?></a>
									<br><small><?php echo esc_html( $row['post_type'] ); ?> #<?php echo (int) $row['post_id']; ?> (<?php echo esc_html( $row['post_status'] ); ?>)</small>
								</td>
								<td>
									<code>aggregates</code> ×<?php echo (int) $row['count']; ?>
									<?php foreach ( $row['contexts'] as $ctx ) : ?>
										<br><span style="color:#666;font-size:12px;word-break:break-word;"><?php echo esc_html( $ctx ); ?></span>
									<?php endforeach; ?>
								</td>
								<td>
									<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Run the self-replace test on this ONE post? This deliberately re-saves it via wp_update_post() to test for the unicode-escape corruption bug. A revision is created automatically, so this is recoverable.', 'cropx' ) ); ?>');">
										<?php wp_nonce_field( 'cropx_srdt_action', 'cropx_srdt_nonce' ); ?>
										<input type="hidden" name="cropx_srdt_action" value="run_test">
										<input type="hidden" name="test_post_id" value="<?php echo (int) $row['post_id']; ?>">
										<?php submit_button( __( 'Run test', 'cropx' ), 'secondary', 'submit', false ); ?>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}
