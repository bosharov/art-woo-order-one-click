<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
add_filter( 'woocommerce_general_settings', 'bryce_add_a_setting' );
function bryce_add_a_setting( $settings ) {
	$args     = array(
		'post_type'      => 'wpcf7_contact_form',
		'posts_per_page' => -1,
	);
	$cf7Forms = get_posts( $args );
	$select= array();
	foreach ($cf7Forms as $form){
		$select[esc_attr($form->ID)] = '[contact-form-7 id="'. esc_attr($form->ID) .'" title="'. esc_html($form->post_title) .'"]';
	}
	$settings[] = array(
		'name' => 'Настройки режима каталога',
		'type' => 'title',
		'desc' => 'Настройки плагина Art WooCommerce Order One Click',
		'id'   => 'woocommerce_awooc_settings',
	);
	
	$settings[] = array(
		'title'    => 'Выбор формы',
		'desc'     => 'Выберите нужную форму',
		'id'       => 'woocommerce_awooc_select_form',
		'css'      => 'min-width:350px;',
		'class'    => 'wc-enhanced-select',
		'default'  => '-- Выбрать --',
		'type'     => 'select',
		'options'  => $select,
		'desc_tip' => true,
	);
	$settings[] = array(
		'type' => 'sectionend',
		'id'   => 'woocommerce_awooc_settings',
	);
	
	return $settings;
}

