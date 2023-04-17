<?php


/**
 * Plugin Name: Real Estate
 * Description: Ініціалізує новий post type "Об'єкт нерухомості" та taxonomy "Район"
 * Author URI:  https://github.com/for4example
 * Author:      Georgy Ganchev
 * Version:     1.0
 *
 * Text Domain: ID перевода, указывается в load_plugin_textdomain()
 * Domain Path: Путь до файла перевода.
 * Requires at least: 2.5
 * Requires PHP: 5.4
 *
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Network:     Укажите "true" для возможности активировать плагин для сети Multisite.
 * Update URI: https://example.com/link_to_update
 */

// Scripts
add_action('wp_enqueue_scripts','my_ajax_filter_search_scripts');
function my_ajax_filter_search_scripts() {
    wp_enqueue_script( 'real-estate-test', plugins_url('/script.js', __FILE__), array(), '1.0', true );
    wp_enqueue_script('pagination', 'https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.4/jquery.simplePagination.min.js', array(), '', true);
    global $wp_query;
    wp_localize_script('real-estate-test', 'search', array(
        'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php',
        'nonce'    => wp_create_nonce( 'search' ),
        'posts' => json_encode( $wp_query->query_vars ), // передача параметров запроса в формате JSON
        'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
        'max_page' => $wp_query->max_num_pages
    ));
}

// Styles
add_action('wp_enqueue_scripts','my_custom_styles');
function my_custom_styles() {
    wp_enqueue_style( 'real-estate-test', plugins_url( '/style.css', __FILE__ ));
    wp_enqueue_script('real-estate-test');
    wp_enqueue_style( 'pagination', 'https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.4/simplePagination.css');
    wp_enqueue_script('pagination');
}

function my_posts_where( $where ) {
    $where = str_replace("meta_key = 'inside_$", "meta_key LIKE 'inside_%", $where);
    return $where;
}
add_filter('posts_where', 'my_posts_where');


// CREATED CUSTOM POST
function create_posttype() {
    register_post_type( 'estate',
        array(
            'labels' => array(
                'name' => __( "Об'єкт нерухомості" ),
                'singular_name' => __( 'Estate' )
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'estate','with_front' => false),
            'show_in_rest' => true,
            'hierarchical' => true,
            'taxonomies' => array('re'),
            'menu_icon' => 'dashicons-hammer',
            'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
            'query_var' => true,
        )
    );
}
add_action('init', 'create_posttype');

// CREATED TAXONOMY
function create_taxonomy(){
	$labels = array(
		'name' => _x( 'Categories', 'taxonomy general name' ),
		'singular_name' => _x( 'Category', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Category' ),
		'popular_items' => __( 'Popular Categories' ),
		'all_items' => __( 'All Categories' ),
		'parent_item' => __( 'Parent Categories' ),
		'parent_item_colon' => __( 'Parent Categories:' ),
		'edit_item' => __( 'Edit Categories' ),
		'update_item' => __( 'Update Categories' ),
		'add_new_item' => __( 'Add New Category' ),
		'new_item_name' => __( 'New Categories' ),
	);
    register_taxonomy('re',array('estate'), array(
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'show_in_rest' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 're' ),
	));
}
add_action('init', 'create_taxonomy', 0); 

// SHORTCODE
function my_ajax_filter_search_shortcode(){
my_ajax_filter_search_scripts();
ob_start(); ?>
<div class="my-ajax-filter-search">
    <form action="" method="POST" id="form-filter">
        <div class="items column d-flex">
            <?php
                if (function_exists('acf_get_field_groups')) {
                    $acf_field_group = acf_get_field_group(14);
                    $acf_fields = acf_get_fields(14);
                }
                    $args = array(
                        'post_type' => 'estate',
                        'posts_per_page' => -1,
                        'orderby' => 'DESC'
                    );
                    $estate = new WP_Query( $args );
                    
                    $unique_values = array();
                    $rooms_array = array();
                    $floor_mass = array();
                    $ecology_mass = array();

                    if ( $estate->have_posts() ) : while ( $estate->have_posts() ) : $estate->the_post(); ?>
                        <?php 
                            $floors_value = get_field( 'floors', get_the_ID() );
                            $floors_val = $floors_value['value'];
                            if ( ! in_array( $floors_val, $floor_mass ) ) {
                                $floor_mass[] = $floors_val;
                            }
                            sort( $floor_mass );

                            $ecology_value = get_field( 'ecology', get_the_ID());
                            $eco_val = $ecology_value['value'];
                            if ( ! in_array( $eco_val, $ecology_mass ) ) {
                                $ecology_mass[] = $eco_val;
                            }
                            sort( $ecology_mass );

                            // repeater fields
                                if(have_rows('inside')) : 
                                while(have_rows('inside')) : the_row();
                                    $value = get_sub_field( 'square', get_the_ID() );
                                    if ( ! in_array( $value, $unique_values ) ) {
                                        $unique_values[] = $value;
                                    }
                                    sort( $unique_values );

                                    $rooms_value = get_sub_field( 'rooms', get_the_ID() );
                                    if ( ! in_array( $rooms_value, $rooms_array ) ) {
                                        $rooms_array[] = $rooms_value;
                                    }
                                    sort( $rooms_array );
                                endwhile; endif; 
                            endwhile; endif;
                            wp_reset_postdata(); 
                        ?>
            <?php foreach($acf_fields as $fields) { ?>
                <div class="item-search <?php if($fields['type'] == 'radio') : echo 'wrap d-flex justify-content-space column'; else : endif;?>">
                <?php if($fields['type'] == 'text'): ?>
                    <label for="<?php echo $fields['name'];?>"><?php echo $fields['label'];?></label>
                    <input name="<?php echo $fields['name'];?>" type="text" id="<?php echo $fields['name'];?>">
                <?php elseif($fields['type'] == 'radio') : ?>
                    <label>Тип будівлі</label>
                    <?php foreach($fields['choices'] as $value => $label) { ?>
                    <div class="d-flex flex-13 form-check">
                        <label for="type_building" class="form-check-label"><?php echo $label;?></label>
                        <input name="type_building" type="radio" value="<?php echo $value;?>" id="type" class="form-check-input">                
                    </div>
                    <?php } ?>
                <?php elseif($fields['wrapper']['class'] == 'floor_choose') : ?>
                    <label for="<?php echo $fields['name'];?>"><?php echo $fields['label'];?></label>
                    <select name="<?php echo $fields['name'];?>" id="<?php echo $fields['name'];?>">
                        <option value="" disable>Оберіть <?php echo mb_strtolower($fields['label']);?></option>
                        <?php foreach($floor_mass as $fl){ ?>
                        <option value="<?php echo $fl;?>"><?php echo $fl;?></option>
                        <?php } ?>
                    </select>
                    <?php elseif($fields['wrapper']['class'] == 'ecology_choose') : ?>
                    <label for="<?php echo $fields['name'];?>"><?php echo $fields['label'];?></label>
                    <select name="<?php echo $fields['name'];?>" id="<?php echo $fields['name'];?>">
                        <option value="" disable>Оберіть <?php echo mb_strtolower($fields['label']);?></option>
                        <?php foreach($ecology_mass as $eco){ ?>
                        <option value="<?php echo $eco;?>"><?php echo $eco;?></option>
                        <?php } ?>
                    </select>
                <?php elseif($fields['type'] == 'repeater') : ?>
                    <?php 
                        foreach($fields['sub_fields'] as $field) { ?>
                        <?php if($field['type'] == 'text') : ?>
                            <div class="d-flex flex-13 column">
                                <label for="<?php echo $field['name'];?>"><?php echo $field['label'];?></label>
                                <input name="<?php echo $field['name'];?>" type="text" id="<?php echo $field['name'];?>">                
                            </div>
                        </div>
                        <?php elseif($field['type'] == 'number') : ?>
                            <div class="d-flex flex-13 column">
                                <label for="<?php echo $field['name'];?>"><?php echo $field['label'];?> в м<sup><small>2</small></sup></label>
                                <select name="<?php echo $field['name'];?>" id="<?php echo $field['name'];?>">
                                    <option value="" disable>Оберіть площу</option>
                                    <?php foreach($unique_values as $value){ ?>
                                    <option value="<?php echo $value;?>"><?php echo $value;?></option>
                                    <?php } ?>
                                </select>       
                            </div>
                        </div>
                        <?php elseif($field['wrapper']['class'] =='mytest') : ?>
                        <div class="item-search wrap d-flex justify-content-space">
                            <label for="<?php echo $field['name'];?>"><?php echo $field['label'];?></label>
                            <select name="" id="<?php echo $field['name'];?>">
                                <option value="" disable>Оберіть <?php echo mb_strtolower($field['label']);?></option>
                                <?php foreach($rooms_array as $rooms){ ?>
                                <option value="<?php echo $rooms;?>"><?php echo $rooms;?></option>
                                <?php } ?>
                            </select>             
                        </div>
                        <?php elseif($field['type'] == 'radio') : ?>
                            <div class="item-search form-check form-switch">
                                <label for="<?php echo $field['name'];?>" class="form-check-label"><?php echo $field['label'];?></label>
                                <input type="checkbox" name="<?php echo $field['name'];?>" value="" class="form-check-input"  id="<?php echo $field['name'];?>">
                            </div>
                        <?php endif; ?>
                    <?php } ?>
                <?php endif; ?>
                </div>
            <?php } ?>
            <div class="controls">
                <input type="submit" id="search" value="Пошук" class="btn"/>
                <input type="submit" id="clear" value="Очистити" class="btn"/>
            </div>
            </div>
    </form>
</div>
<?php
return ob_get_clean();
}
add_shortcode ('my_ajax_filter_search', 'my_ajax_filter_search_shortcode');

add_action('wp_ajax_my_ajax_filter_search', 'my_ajax_filter_search_callback');
add_action('wp_ajax_nopriv_my_ajax_filter_search', 'my_ajax_filter_search_callback');
// Filter
function my_ajax_filter_search_callback() {

    header("Content-Type: application/json"); 

    check_ajax_referer( 'search', 'nonce' );

    $meta_query = array('relation' => 'AND');

    if(isset($_POST['name_house'])) {
        $meta_query[] = array(
            'key' => 'name_house',
            'value' => $_POST['name_house'],
            'compare' => '='
        );
    }
 
    if(isset($_POST['floors'])) {
        $meta_query[] = array(
            'key' => 'floors',
            'value' => $_POST['floors'] ,
            'compare' => '='
        );
    }
 
    if(isset($_POST['coordinate'])) {
        $meta_query[] = array(
            'key' => 'coordinate',
            'value' => $_POST['coordinate'],
            'compare' => '='
        );
    }

    if(isset($_POST['ecology'])) {
        $meta_query[] = array(
            'key' => 'ecology',
            'value' => $_POST['ecology'],
            'compare' => '='
        );
    }


    $square = $_POST['square'];
    if(isset($_POST['square'])) {
        $meta_query[] = array(
            'key' => 'inside_$_square',
            'value' => $square,
            'compare' => '='
        );
    }

    $rooms = $_POST['rooms'];
    if(isset($_POST['rooms'])) {
        $meta_query[] = array(
            'key' => 'inside_$_rooms',
            'value' => $rooms,
            'compare' => '='
        );
    }

    $balcony = $_POST['balcony'];
    if(isset($_POST['balcony'])) {
        $meta_query[] = array(
            'key' => 'inside_$_balcony',
            'value' => $balcony,
            'compare' => '='
        );
    }

    $bedroom = $_POST['bedroom'];
    if(isset($_POST['bedroom'])) {
        $meta_query[] = array(
            'key' => 'inside_$_bedroom',
            'value' => $bedroom,
            'compare' => '='
        );
    }
   
    $type = $_POST['type_house'];
    if(isset($_POST['type_house'])) {
            $meta_query[] = array(
                'key' => 'type_house',
                'value' => $type,
                'compare' => '='
            );  
    }

    $paged = $_POST['page'];

    $search_query = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
    
    $args = array(
        'post_type'      => 'estate',
        'posts_per_page' => 5,
        'meta_query' => $meta_query,
        's'              => $search_query,
        'order' => 'ASC',
        'paged' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
    );
    $search_query = new WP_Query( $args );
    if( get_query_var('page') ) {
        $page = get_query_var( 'page' );
    } else {
        $page = 1;
    }
    
    if ( $search_query->have_posts() ) {
        $result = array();
        while ( $search_query->have_posts() ) {
            $search_query->the_post();
            $repeater_data = array();
            $compared = array();

            if ( get_field( 'inside' ) ) {
                while ( have_rows( 'inside' ) ) {
                    the_row();
                    $row++;

                    $square_inside = get_sub_field('square'); //square
                    $rooms_inside = get_sub_field('rooms'); // rooms
                    $balcony_inside = get_sub_field('balcony')['value']; // balcony
                    $bedroom_inside = get_sub_field('bedroom')['value']; // bedroom
                    if(
                        $square_inside === $square || 
                        $rooms_inside === $rooms || 
                        $balcony_inside === $balcony ||
                        $bedroom_inside === $bedroom
                    ){
                        $new_square = get_sub_field_object('square')['value'];
                        $new_image = get_sub_field_object('image_inside')['value']['url'];
                        $new_room = get_sub_field_object('rooms')['value'];
                        $new_balcony = get_sub_field_object('balcony')['value'];
                        $new_bedroom = get_sub_field_object('bedroom')['value'];
                        $compared[] = array(
                            'square' => $new_square, 
                            'rooms' => $new_room,
                            'balcony' => $new_balcony,
                            'bedroom' => $new_bedroom,
                            'image' => $new_image,
                            'id' => $count
                        );
                    }
                   
                }
            }
            $types = get_field('type_house');
            $updated_types = $types['label'];
            global $post;
            $terms = get_the_terms( $post->ID , 're' );
            if ( $terms != null ){
                foreach( $terms as $term ) {
                $term_name = $term->name;
                }
            }
            $result[] = array(
                "id" => get_the_ID(),
                "title" => get_the_title(),
                "content" => get_the_content(),
                "permalink" => get_permalink(),
                "image" => get_the_post_thumbnail(),
                "name_house" => get_field('name_house'),
                "floors" => get_field('floors'),
                "coordinate" => get_field('coordinate'),
                "ecology" => get_field('ecology'),
                "tax_name" => $term_name,
                "type_house" => $updated_types,
                "compared" => $compared
            );
        }
        wp_reset_query();
        wp_send_json($result);
    } else {
        echo 'no posts found';
    }
    ?>
  <div class="pagination-block">
    <div class="pagination-right">
        <?php $args = array(
            'show_all' => false,
            'end_size' => 1,  
            'mid_size' => 1,   
            'prev_next' => true,
            'add_args' => false,
            'add_fragment' => '', 
        );
        $pagination = get_the_posts_pagination($args);
        echo str_replace('wp-admin/admin-ajax.php', 'estate', $pagination); ?>
    </div>
    </div>
    <?php
    wp_die();
}

add_action('wp_ajax_ajaxpagination', 'ajax_pagination_in_filter');
add_action('wp_ajax_nopriv_ajaxpagination', 'ajax_pagination_in_filter');
function ajax_pagination_in_filter()
{

    $link = !empty($_POST['link']) ? esc_attr($_POST['link']) : false;
    $paged = $link ? wp_basename($link) : false;

    query_posts(array(
        'posts_per_page' => get_option('posts_per_page'),
        'post_status' => 'publish',
        'post_type' => 'estate',
        'paged' => $paged,
        'order' => 'ASC'
    ));

    if (have_posts()) :
        while (have_posts()): the_post();
            ?>
     <li id="real-estate-<?php echo $i;?>">  
        <a href="<?php echo get_permalink();?>" title="<?php the_title();?>">
        <div class="building-info">
            <?php $image = get_sub_field('image');?>  
            <?php echo get_the_post_thumbnail();?>        
            <h2>Будинок <?php the_field('name_house');?></h2>    
            <?php
                global $post;
                $terms = get_the_terms( $post->ID , 're' );
                if ( $terms != null ){
                foreach( $terms as $term ) {
               echo '<span class="cat-name">' . $term->name . '</span>';
            } } ?>
            <div class="content d-flex wrap justify-content-space">
                <p>К-сть поверхів: <?php $floors = get_field('floors'); echo $floors['value'];?></p>          
                <p>Координати: <?php the_field('coordinate');?></p>         
                <p>Тип: <?php $type = get_field('type_house'); echo $type['label'];?></p>
                <p>Екологічність: <?php $eco = get_field('ecology'); echo $eco['value'];?></p>  
            </div>     
        </div>  
    </a>
    </li>
        <?php endwhile;
    else:
        echo '<p>Nothing found for your criteria.</p>';
    endif; ?>

    <div class="pagination-block">
    <div class="pagination-right">
        <?php $args = array(
            'show_all' => false,
            'end_size' => 1,  
            'mid_size' => 1,   
            'prev_next' => true,
            'add_args' => false,
            'add_fragment' => '', 
        );
        $pagination = get_the_posts_pagination($args);
        echo str_replace('wp-admin/admin-ajax.php', 'estate', $pagination); ?>
    </div>
    </div>
    <?php wp_die();
}

add_action('wp_enqueue_scripts', 'my_assets');
function my_assets()
{
    global $wp_query;

    wp_register_script('my_assets', plugins_url('/filter.js', __FILE__), array('jquery'), '', true);

    wp_localize_script('my_assets', 'ajax_pagination', array(
        'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', 
        'posts' => json_encode( $wp_query->query_vars ), 

    ));
    wp_enqueue_script('my_assets');
}



?>

