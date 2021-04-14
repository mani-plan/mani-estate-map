<?php
/*
Plugin Name: mani estate map
Plugin URI: https://github.com/mani-plan/mani-estate-map
Description: estate post type & map query
Version: 1.0
Author: manidesign
Author URI: https://manidesign.org/
License: GPLv2 or later
*/

$mani_estate_map = Mani_Estate_Map::instance();

class Mani_Estate_Map {

  private static $instance;

  public static function instance() {
    if ( self::$instance == null ) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function activate() {
  }

  public function deactivate() {
  }

  public function __construct() {
    register_activation_hook (__FILE__, array($this, 'activate'));
    register_deactivation_hook (__FILE__, array($this, 'deactivate'));
    add_action('init', array($this, 'create_estate_post_type'));
  }

  public function create_estate_post_type() {
    register_post_type(
      'estate',
      array(
        'label' => '自社所有物件',
        'public' => true,
        'has_archive' => true,
        'menu_position' => 4,
        'supports' => array(
          'title',
          'editor',
          'author',
          'thumbnail',
          'excerpt',
          'custom-fields',
          'revisions'
        ),
        'taxonomies' => array('estate_cat'),
        'show_in_rest' => true
      )
    );

    register_taxonomy(
      'estate_cat',
      'estate',
      array(
        'label' => '物件カテゴリー',
        'labels' => array(
          'popular_items' => 'よく使う物件カテゴリー',
          'edit_item' =>'物件カテゴリーを編集',
          'add_new_item' => '物件カテゴリーを追加',
          'search_items' => '物件カテゴリーを検索'
        ),
        'public' => true,
        'description' => '物件カテゴリーの説明文です。',
        'hierarchical' => true,
        'show_in_rest' => true
      )
    );
  }

  public function mani_estate_query($category = array()) {

    $args = array(
      'post_type'      => 'estate',
      'posts_per_page' => -1,
    );

    $search = array();
    if ( !in_array( 'sold', $category, true ) ) {
      $search[] = array(
        'taxonomy' => 'estate_cat',
        'terms' => array('sold'),
        'field' => 'slug',
        'operator' => 'NOT IN',
      );
    } else {
      $category = array_diff($category, array('sold'));
      $category = array_values($category);
    }

    $esttype = array();
    if ( in_array( 'house', $category, true ) ) {
      $esttype[] = 'house';
    }
    if ( in_array( 'apartment', $category, true ) ) {
      $esttype[] = 'apartment';
    }
    if ( count( $esttype ) > 0 ) {
      $search[] = array(
        'taxonomy' => 'estate_cat',
        'terms' => $esttype,
        'field' => 'slug',
        'operator' => 'IN',
      );
    }

    if ( in_array( 'solar', $category, true ) ) {
      $search[] = array(
        'taxonomy' => 'estate_cat',
        'terms' => 'solar',
        'field' => 'slug',
      );
    }

    if ( count($search) > 0 ) {
      $search[] = array('relation' => 'AND');
      $args = array(
        'post_type' => 'estate',
        'posts_per_page' => -1,
        'tax_query' => $search,
      );
    }

    return $args;
  }

}