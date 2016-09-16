<?php

/**
 * @function wpl_get_layout_definitions
 * @since 1.0.0
 */
function wpl_get_layout_definitions()
{
	static $data = null;

	$args = array(
		'posts_per_page'   => 0,
		'offset'           => 0,
		'category'         => '',
		'category_name'    => '',
		'orderby'          => 'date',
		'order'            => 'DESC',
		'include'          => '',
		'exclude'          => '',
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'wpl-layout',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'author'	   => '',
		'author_name'	   => '',
		'post_status'      => 'publish',
		'suppress_filters' => true
	);

	$definitions = array();

	if ($data == null) {
		$data = get_posts($args);
	}

	foreach ($data as $definition) {

		$blocks = wpb_get_blocks($definition->ID);
		$layout = array_shift($blocks);

		$definitions[] = array(
			'id' => $definition->ID,
			'name' => $definition->post_title,
			'layout' => $layout,
			'blocks' => $blocks,
		);
	}

	return $definitions;
}

/**
 * @function wpl_get_layouts
 * @since 1.0.0
 */
function wpl_get_layouts($page_id)
{
	static $data = null;

	$page_layouts = get_post_meta($page_id, '_wpl_layouts', true);

	$args = array(
		'posts_per_page'   => 0,
		'offset'           => 0,
		'category'         => '',
		'category_name'    => '',
		'orderby'          => 'date',
		'order'            => 'DESC',
		'include'          => '',
		'exclude'          => '',
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'wpl-layout',
		'post_mime_type'   => '',
		'post_parent'      => '',
		'author'	   => '',
		'author_name'	   => '',
		'post_status'      => 'publish',
		'suppress_filters' => true
	);

	$layouts = array();

	if ($data == null) {
		$data = get_posts($args);
	}

	foreach ($data as $definition) {

		foreach ($page_layouts as $block_id => $definition_id) {

			if ($definition->ID != $definition_id) {
				continue;
			}

			$layouts[$block_id] = array(
				'id' => $definition->ID,
				'name' => $definition->post_title,
			);
		}
	}

	return $layouts;
}

/**
 * @function wpl_get_layout
 * @since 1.0.0
 */
function wpl_get_layout($page_id, $post_id)
{
	$layouts = wpl_get_layouts($page_id);
	return isset($layouts[$post_id]) ? $layouts[$post_id] : null;
}

/**
 * @function wpl_layout_is_type
 * @since 1.0.0
 */
function wpl_layout_is_type($page, $buid)
{
	foreach (wpb_get_blocks($page->ID) as $block) {
		if ($block['buid'] === $buid) {
			return true;
		}
	}

	return false;
}

