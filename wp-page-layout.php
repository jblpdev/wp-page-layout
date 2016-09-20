<?php
/*
Plugin Name: WP Page Layout
Plugin URI: http://jblp.ca
Description: Defines default block for specific layouts.
Version: 1.0.0
Author: Jean-Philippe Dery (jp@jblp.ca)
Author URI: http://jblp.ca
License: MIT
Copyright: JBLP Inc.
*/

define('WPL_VERSION', '1.0.0');
define('WPL_FILE', __FILE__);
define('WPL_DIR', plugin_dir_path(WPL_FILE));
define('WPL_URL', plugins_url('/', WPL_FILE));

require_once WP_CONTENT_DIR . '/plugins/wp-page-layout/lib/functions.php';

//------------------------------------------------------------------------------
// Post Types
//------------------------------------------------------------------------------

$labels = array(
	'name'               => _x('Layouts', 'post type general name', 'your-plugin-textdomain' ),
	'singular_name'      => _x('Layout', 'post type singular name', 'your-plugin-textdomain' ),
	'menu_name'          => _x('Layouts', 'admin menu', 'your-plugin-textdomain' ),
	'name_admin_bar'     => _x('Layout', 'add new on admin bar', 'your-plugin-textdomain' ),
	'add_new'            => _x('Add new', 'Layout', 'your-plugin-textdomain' ),
	'add_new_item'       => __('Add new layout', 'your-plugin-textdomain' ),
	'new_item'           => __('New layout', 'your-plugin-textdomain' ),
	'edit_item'          => __('Edit layout', 'your-plugin-textdomain' ),
	'view_item'          => __('View layout', 'your-plugin-textdomain' ),
	'all_items'          => __('All layouts', 'your-plugin-textdomain' ),
	'search_items'       => __('Search layouts', 'your-plugin-textdomain' ),
	'parent_item_colon'  => __('Parent layouts:', 'your-plugin-textdomain' ),
	'not_found'          => __('No layouts found.', 'your-plugin-textdomain' ),
	'not_found_in_trash' => __('No layouts found in Trash.', 'your-plugin-textdomain' )
);

register_post_type('wpl-layout', array(
	'labels'             => $labels,
	'description'        => '',
	'public'             => false,
	'publicly_queryable' => false,
	'show_ui'            => true,
	'show_in_menu'       => false,
	'query_var'          => false,
	'rewrite'            => false,
	'capability_type'    => 'post',
	'has_archive'        => false,
	'hierarchical'       => false,
	'menu_position'      => null,
	'supports'           => array('title')
));

/**
 * @action init
 * @since 1.0.0
 */
add_action('init', function() {

	$layouts = array();

	foreach (wpb_block_template_infos() as $block_template_info) {
		if ($block_template_info['category'] === 'Layout') {
			$layouts[$block_template_info['buid']] = $block_template_info['name'];
		}
	}

	if (function_exists('acf_add_local_field_group')) acf_add_local_field_group(array(
		'key' => 'group_57dab63eb6a82',
		'title' => 'Layout',
		'fields' => array(
			array(
				'key' => 'field_57dab64ad6e4a',
				'label' => 'Layout',
				'name' => 'layout',
				'type' => 'select',
				'instructions' => 'This is an instruction',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array(
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'choices' => $layouts,
				'default_value' => array(
				),
				'allow_null' => 1,
				'multiple' => 0,
				'ui' => 0,
				'ajax' => 0,
				'return_format' => 'value',
				'placeholder' => '',
			)
		),
		'location' => array(
			array(
				array(
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'wpl-layout',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'seamless',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));

});

/**
 * @action admin_init
 * @since 1.0.0
 */
add_action('admin_init', function() {

});

/**
 * @action admin_menu
 * @since 1.0.0
 */
add_action('admin_menu', function() {
	global $submenu;
	$submenu['themes.php'][] = array('Layouts', 'manage_options', 'edit.php?post_type=wpl-layout');
});

/**
 * @action admin_enqueue_scripts
 * @since 1.0.0
 */
add_action('admin_enqueue_scripts', function() {
	if (get_post_type() == 'wpl-layout') {
		wp_enqueue_script('wpl_layout_editor_js', WPL_URL . 'assets/js/admin-layout-editor.js', false, WPL_VERSION);
		wp_enqueue_style('wpl_layout_editor_css', WPL_URL . 'assets/css/admin-layout-editor.css', false, WPL_VERSION);
	}
});

//------------------------------------------------------------------------------
// WPB Actions
//------------------------------------------------------------------------------

/**
 * @filter wpb/block_context
 * @since 1.0.0
 */
add_filter('wpb/block_context', function($context) {
	$context[] = 'wpl-layout';
	return $context;
});

/**
 * @filter wpb/block_metabox_title
 * @since 1.0.0
 */
add_filter('wpb/block_metabox_title', function($title, $post_type) {

	if ($post_type == 'wpl-layout') {
		return 'Customize';
	}

	return $title;

}, 10, 2);

/**
 * @filter wpb/block_metabox_priority
 * @since 1.0.0
 */
add_filter('wpb/block_metabox_priority', function($priority, $post_type) {

	if ($post_type == 'wpl-layout') {
		return 'high';
	}

	return $priority;

}, 10, 2);

/**
 * Adds a selector to the layout which allows the client to indicate which
 * layout this layout will inherit.
 * @filter wpb/block_preview_header
 * @since 1.0.0
 */
add_filter('wpb/block_preview_header', function($header, $block) {

	if (get_post_type() === 'page') {

		if ($block->infos['category'] === 'Layout') {

			$definitions = wpl_get_layout_definitions();

			$layouts = array_filter(array_map(function($definition) use ($block) {

				if ($definition['layout']['buid'] != $block->infos['buid']) {
					return null;
				}

				$selected = wpl_get_layout(
					$block->get_page_id(),
					$block->get_post_id()
				);

				if ($selected['id'] == $definition['id']) {
					$selected = true;
				} else {
					$selected = false;
				}

				return array(
					'id' => $definition['id'],
					'name' => $definition['name'],
					'selected' => $selected
				);

			}, $definitions), function($layout) {
				return $layout;
			});

			ob_start();

			?>

			<label>Inherits:</label>
			<select name="_wpl_layouts[<?php echo $block->get_post_id() ?>]">
				<option>Do not inherit an existing layout</option>
				<?php foreach ($layouts as $layout) : ?>
					<option <?php echo $layout['selected'] ? 'selected="selected"' : '' ?> value="<?php echo $layout['id'] ?>">
						<?php echo $layout['name'] ?>
					</option>
				<?php endforeach ?>
			</select>

			<?php

			$header = $header . ob_get_contents();

			ob_end_clean();
		}
	}

	return $header;

}, 10, 2);

/**
 * @filter wpb/block_preview_footer
 * @since 1.0.0
 */
add_filter('wpb/block_preview_footer', function($footer, $block) {

	return $footer;

}, 10, 2);

/**
 * @filter wpb/children_blocks
 * @since 1.0.0
 */
add_filter('wpb/children_blocks', function($blocks, $block) {

	if (get_post_type() === 'page') {

		if ($block->infos['category'] === 'Layout') {

			$layout = wpl_get_layout(
				$block->get_page_id(),
				$block->get_post_id()
			);

			if (isset($layout['blocks'])) {

				foreach ($layout['blocks'] as $child) {
					$child['into_id'] = $block->get_post_id();
					$child['disable'] = true;
					$blocks[] = $child;
				}

				$ordered_blocks = array();
				$ordered_offset = 0;

				foreach ($blocks as $index => $block) {

					$position = isset($block['position']) ? $block['position'] : null;

					if ($position !== null) {

						if (isset($ordered_blocks[$position])) {

							$ordered_blocks = array_merge(
								array_slice($ordered_blocks, 0, $position), array($block),
								array_slice($ordered_blocks, $position)
							);

							continue;
						}

						$ordered_blocks[$position] = $block;
						continue;
					}

					while (isset($ordered_blocks[$ordered_offset])) {
						$ordered_offset++;
					}

					$ordered_blocks[$ordered_offset] = $block;
				}

				ksort($ordered_blocks);

				$blocks = $ordered_blocks;
			}
		}
	}

	return $blocks;

}, 10, 2);

/**
 * @filter wpb/render
 * @since 1.0.0
 */
add_filter('wpb/render', function($render, $block) {

	if (get_post_type() === 'page') {

		if ($block->infos['category'] === 'Layout') {

			$layout = wpl_get_layout(
				$block->get_page_id(),
				$block->get_post_id()
			);

			return $content . $render;
		}
	}

	return $render;

}, 10, 2);

/**
 * @action wpb/save_block
 * @since 1.0.0
 */
add_action('wpb/save_block', function($page_id, $page_blocks) {

	if (get_post_type() === 'page') {

		$blocks = isset(
			$_POST['_wpb_blocks']
		) ? $_POST['_wpb_blocks'] : array();

		foreach ($blocks as $position => $post_id) {
			foreach ($page_blocks as &$page_block) {
				if ($page_block['post_id'] == $post_id) {
					$page_block['position'] = $position;
				}
			}
		}

		update_post_meta($page_id, '_wpb_blocks', $page_blocks);
	}

	$layouts = isset(
		$_POST['_wpl_layouts']
	) ? $_POST['_wpl_layouts'] : array();

	update_post_meta($page_id, '_wpl_layouts', $layouts);

}, 10, 2);