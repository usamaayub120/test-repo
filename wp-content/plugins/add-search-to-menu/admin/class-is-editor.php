<?php

class IS_Search_Editor
{
    private  $search_form ;
    private  $panels = array() ;
    private  $is_premium_plugin = false ;
    public function __construct( IS_Search_Form $search_form )
    {
        $this->search_form = $search_form;
    }
    
    function is_name( $string )
    {
        return preg_match( '/^[A-Za-z][-A-Za-z0-9_:.]*$/', $string );
    }
    
    public function add_panel( $id, $title, $callback )
    {
        if ( $this->is_name( $id ) ) {
            $this->panels[$id] = array(
                'title'    => $title,
                'callback' => $callback,
            );
        }
    }
    
    public function display()
    {
        if ( empty($this->panels) ) {
            return;
        }
        echo  '<ul id="search-form-editor-tabs">' ;
        $url = esc_url( menu_page_url( 'ivory-search-new', false ) );
        if ( isset( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) {
            $url = esc_url( menu_page_url( 'ivory-search', false ) ) . '&post=' . $_GET['post'] . '&action=edit';
        }
        $tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'includes' );
        foreach ( $this->panels as $id => $panel ) {
            $class = ( $tab == $id ? 'active' : '' );
            echo  sprintf(
                '<li id="%1$s-tab" class="%2$s"><a href="%3$s">%4$s</a></li>',
                esc_attr( $id ),
                esc_attr( $class ),
                $url . '&tab=' . $id,
                esc_html( $panel['title'] )
            ) ;
        }
        echo  '</ul>' ;
        echo  sprintf( '<div class="search-form-editor-panel" id="%1$s">', esc_attr( $tab ) ) ;
        $this->notice( $tab, $tab . '_panel' );
        $callback = $tab . '_panel';
        
        if ( method_exists( $this, $callback ) ) {
            $this->{$callback}( $this->search_form );
        } else {
            _e( 'The requested section does not exist.', 'ivory-search' );
        }
        
        echo  '</div>' ;
    }
    
    public function notice( $id, $panel )
    {
        echo  '<div class="config-error"></div>' ;
    }
    
    /**
     * Gets all public meta keys of post types
     *
     * @global Object $wpdb WPDB object
     * @return Array array of meta keys
     */
    function is_meta_keys( $post_types )
    {
        global  $wpdb ;
        $post_types = implode( "', '", $post_types );
        $is_fields = $wpdb->get_results( apply_filters( 'is_meta_keys_query', "select DISTINCT meta_key from {$wpdb->postmeta} pt LEFT JOIN {$wpdb->posts} p ON (pt.post_id = p.ID) where meta_key NOT LIKE '\\_%' AND post_type IN ( '{$post_types}' ) ORDER BY meta_key ASC" ) );
        $meta_keys = array();
        if ( is_array( $is_fields ) && !empty($is_fields) ) {
            foreach ( $is_fields as $field ) {
                if ( isset( $field->meta_key ) ) {
                    $meta_keys[] = $field->meta_key;
                }
            }
        }
        /**
         * Filter results of SQL query for meta keys
         */
        return apply_filters( 'is_meta_keys', $meta_keys );
    }
    
    public function includes_panel( $post )
    {
        $id = '_is_includes';
        $includes = $post->prop( $id );
        $excludes = $post->prop( '_is_excludes' );
        $settings = $post->prop( '_is_settings' );
        $default_search = ( NULL == $post->id() ? true : false );
        ?>
		<h4 class="panel-desc">
			<?php 
        _e( "This search form searches the below configured content.", 'ivory-search' );
        ?>
		</h4>
		<div class="search-form-editor-box" id="<?php 
        echo  $id ;
        ?>">

		<div class="form-table form-table-panel-includes">

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-post_type"><?php 
        esc_html_e( 'Post Types', 'ivory-search' );
        ?></label>
				<span class="actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'ivory-search' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'ivory-search' );
        ?></a></span>
			</h3>
			<div>
				<?php 
        $content = __( 'Search selected post types.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $post_types = get_post_types( array(
            'public'              => true,
            'exclude_from_search' => false,
        ) );
        
        if ( !empty($post_types) ) {
            foreach ( $post_types as $key => $post_type ) {
                $checked = ( $default_search || isset( $includes['post_type'][esc_attr( $key )] ) ? esc_attr( $key ) : 0 );
                echo  '<div class="col-wrapper check-radio">' ;
                echo  '<label for="' . $id . '-post_type-' . esc_attr( $key ) . '"> ' ;
                echo  '<input class="_is_includes-post_type" type="checkbox" id="' . $id . '-post_type-' . esc_attr( $key ) . '" name="' . $id . '[post_type][' . esc_attr( $key ) . ']" value="' . esc_attr( $key ) . '" ' . checked( $key, $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' ;
                echo  ucfirst( esc_html( $post_type ) ) . '</label></div>' ;
            }
            $checked = ( $default_search || isset( $includes['search_title'] ) && $includes['search_title'] ? 1 : 0 );
            echo  '<br /><br /><p class="check-radio"><label for="' . $id . '-search_title"><input class="_is_includes-post_type" type="checkbox" id="' . $id . '-search_title" name="' . $id . '[search_title]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search post title", 'ivory-search' ) . '</label></p>' ;
            $checked = ( $default_search || isset( $includes['search_content'] ) && $includes['search_content'] ? 1 : 0 );
            echo  '<p class="check-radio"><label for="' . $id . '-search_content"><input class="_is_includes-post_type" type="checkbox" id="' . $id . '-search_content" name="' . $id . '[search_content]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search post content", 'ivory-search' ) . '</label></p>' ;
            $checked = ( $default_search || isset( $includes['search_excerpt'] ) && $includes['search_excerpt'] ? 1 : 0 );
            echo  '<p class="check-radio"><label for="' . $id . '-search_excerpt"><input class="_is_includes-post_type" type="checkbox" id="' . $id . '-search_excerpt" name="' . $id . '[search_excerpt]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search post excerpt", 'ivory-search' ) . '</label></p>' ;
        } else {
            echo  '<span class="notice-is-info">' . __( 'No post types registered on your site.', 'ivory-search' ) . '</span>' ;
        }
        
        ?>
			</div></div>

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-post__in"><?php 
        echo  esc_html( __( 'Posts, Pages & Custom Posts', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Search only selected posts.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        if ( isset( $includes['post_type'] ) && !empty($includes['post_type']) && is_array( $includes['post_type'] ) ) {
            $post_types = array_values( $includes['post_type'] );
        }
        $posts_found = false;
        foreach ( $post_types as $post_type ) {
            $posts = get_posts( array(
                'post_type'      => $post_type,
                'posts_per_page' => 100,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ) );
            
            if ( !empty($posts) ) {
                $posts_found = true;
                $html = '<div class="col-wrapper"><div class="col-title">';
                $col_title = '<span>' . ucwords( $post_type ) . '</span>';
                $temp = '';
                $selected_pt = array();
                foreach ( $posts as $post2 ) {
                    $checked = ( isset( $includes['post__in'] ) && in_array( $post2->ID, $includes['post__in'] ) ? $post2->ID : 0 );
                    if ( $checked ) {
                        array_push( $selected_pt, $post_type );
                    }
                    $post_title = ( isset( $post2->post_title ) && '' !== $post2->post_title ? esc_html( $post2->post_title ) : $post2->post_name );
                    $temp .= '<option value="' . esc_attr( $post2->ID ) . '" ' . selected( $post2->ID, $checked, false ) . '>' . $post_title . '</option>';
                }
                if ( !empty($selected_pt) && in_array( $post_type, $selected_pt ) ) {
                    $col_title = '<strong>' . $col_title . '</strong>';
                }
                $html .= $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'ivory-search' ) . '" type="text"></div>';
                $html .= '<select class="_is_includes-post__in" name="' . $id . '[post__in][]" multiple size="8" >';
                $html .= $temp . '</select>';
                if ( count( $posts ) >= 100 && !defined( 'DISABLE_IS_LOAD_ALL' ) ) {
                    $html .= '<div id="' . $post_type . '" class="load-all">' . __( 'Load All', 'ivory-search' ) . '</div>';
                }
                $html .= '</div>';
                echo  $html ;
            }
        
        }
        
        if ( !$posts_found ) {
            echo  '<span class="notice-is-info">' . __( 'No posts created for selected post types.', 'ivory-search' ) . '</span>' ;
        } else {
            echo  '<br /><label for="' . $id . '-post__in" style="font-size: 10px;clear:both;display:block;">' . esc_html__( "Press CTRL key & Left Mouse button to select multiple terms or deselect them.", 'ivory-search' ) . '</label>' ;
        }
        
        ?>
			</div></div>

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-tax_query"><?php 
        esc_html_e( 'Category & Taxonomy Terms', 'ivory-search' );
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Search posts of only selected categories, taxonomies & terms.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $tax_objs = get_object_taxonomies( $post_types, 'objects' );
        
        if ( !empty($tax_objs) ) {
            foreach ( $tax_objs as $key => $tax_obj ) {
                $terms = get_terms( array(
                    'taxonomy'   => $key,
                    'hide_empty' => false,
                ) );
                
                if ( !empty($terms) ) {
                    echo  '<div class="col-wrapper"><div class="col-title">' ;
                    $col_title = ucwords( str_replace( '-', ' ', str_replace( '_', ' ', esc_html( $key ) ) ) );
                    if ( isset( $includes['tax_query'][$key] ) ) {
                        $col_title = '<strong>' . $col_title . '</strong>';
                    }
                    echo  $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'ivory-search' ) . '" type="text"></div><input type="hidden" id="' . $id . '-tax_post_type" name="' . $id . '[tax_post_type][' . $key . ']" value="' . implode( ',', $tax_obj->object_type ) . '" />' ;
                    echo  '<select class="_is_includes-tax_query" name="' . $id . '[tax_query][' . $key . '][]" multiple size="8" >' ;
                    foreach ( $terms as $key2 => $term ) {
                        $checked = ( isset( $includes['tax_query'][$key] ) && in_array( $term->term_taxonomy_id, $includes['tax_query'][$key] ) ? $term->term_taxonomy_id : 0 );
                        echo  '<option value="' . esc_attr( $term->term_taxonomy_id ) . '" ' . selected( $term->term_taxonomy_id, $checked, false ) . '>' . esc_html( $term->name ) . '</option>' ;
                    }
                    echo  '</select></div>' ;
                }
            
            }
            echo  '<br /><label for="' . $id . '-tax_query" style="font-size: 10px;clear:both;display:block;">' . esc_html__( "Press CTRL key & Left Mouse button to select multiple terms or deselect them.", 'ivory-search' ) . '</label>' ;
            $checked = ( isset( $includes['tax_rel'] ) && "OR" == $includes['tax_rel'] ? "OR" : "AND" );
            echo  '<br /><p class="check-radio"><label for="' . $id . '-tax_rel_and" ><input class="_is_includes-tax_query" type="radio" id="' . $id . '-tax_rel_and" name="' . $id . '[tax_rel]" value="AND" ' . checked( 'AND', $checked, false ) . '/>' ;
            echo  '<span class="toggle-check-text"></span>' . esc_html__( "AND - Search posts having all the above selected terms", 'ivory-search' ) . '</label></p>' ;
            echo  '<p class="check-radio"><label for="' . $id . '-tax_rel_or" ><input class="_is_includes-tax_query" type="radio" id="' . $id . '-tax_rel_or" name="' . $id . '[tax_rel]" value="OR" ' . checked( 'OR', $checked, false ) . '/>' ;
            echo  '<span class="toggle-check-text"></span>' . esc_html__( "OR - Search posts having any one of the above selected terms", 'ivory-search' ) . '</label></p>' ;
            $checked = ( isset( $includes['search_tax_title'] ) && $includes['search_tax_title'] ? 1 : 0 );
            echo  '<br /><p class="check-radio"><label for="' . $id . '-search_tax_title" ><input class="_is_includes-tax_query" type="checkbox" id="' . $id . '-search_tax_title" name="' . $id . '[search_tax_title]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search term title", 'ivory-search' ) . '</label></p>' ;
            $checked = ( isset( $includes['search_tax_desp'] ) && $includes['search_tax_desp'] ? 1 : 0 );
            echo  '<p class="check-radio"><label for="' . $id . '-search_tax_desp" ><input class="_is_includes-tax_query" type="checkbox" id="' . $id . '-search_tax_desp" name="' . $id . '[search_tax_desp]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
            echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search term description", 'ivory-search' ) . '</label></p>' ;
        } else {
            echo  '<span class="notice-is-info">' . __( 'No taxonomies registered for selected post types.', 'ivory-search' ) . '</span>' ;
        }
        
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-custom_field"><?php 
        echo  esc_html( __( 'Custom Fields & Metadata', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Search values of selected custom fields.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $meta_keys = $this->is_meta_keys( $post_types );
        
        if ( !empty($meta_keys) ) {
            echo  '<input class="list-search wide" placeholder="' . __( "Search..", 'ivory-search' ) . '" type="text">' ;
            echo  '<select class="_is_includes-custom_field" name="' . $id . '[custom_field][]" multiple size="8" >' ;
            foreach ( $meta_keys as $meta_key ) {
                $checked = ( isset( $includes['custom_field'] ) && in_array( $meta_key, $includes['custom_field'] ) ? $meta_key : 0 );
                echo  '<option value="' . esc_attr( $meta_key ) . '" ' . selected( $meta_key, $checked, false ) . '>' . esc_html( $meta_key ) . '</option>' ;
            }
            echo  '</select>' ;
            echo  '<br /><label for="' . $id . '-custom_field" style="font-size: 10px;clear:both;display:block;">' . esc_html__( "Press CTRL key & Left Mouse button to select multiple terms or deselect them.", 'ivory-search' ) . '</label>' ;
        } else {
            echo  '<span class="notice-is-info">' . __( 'No custom fields created for selected post types.', 'ivory-search' ) . '</span>' ;
        }
        
        
        if ( isset( $includes['custom_field'] ) ) {
            echo  '<br />' . __( 'Selected Custom Fields :', 'ivory-search' ) ;
            foreach ( $includes['custom_field'] as $custom_field ) {
                echo  '<br /><span style="font-size: 11px;">' . $custom_field . '</span>' ;
            }
        }
        
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-woocommerce"><?php 
        echo  esc_html( __( 'WooCommerce', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Search WooCommerce products.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        
        if ( class_exists( 'WooCommerce' ) ) {
            
            if ( $default_search || in_array( 'product', $post_types ) ) {
                $woo_sku_disable = ( is_fs()->is_plan_or_trial( 'pro_plus' ) && $this->is_premium_plugin ? '' : ' disabled ' );
                $checked = ( isset( $includes['woo']['sku'] ) && $includes['woo']['sku'] ? 1 : 0 );
                echo  '<p class="check-radio"><label for="' . $id . '-sku" ><input class="_is_includes-woocommerce" type="checkbox" ' . $woo_sku_disable . ' id="' . $id . '-sku" name="' . $id . '[woo][sku]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search product SKU", 'ivory-search' ) . '</label></p>' ;
                $checked = ( isset( $includes['woo']['variation'] ) && $includes['woo']['variation'] ? 1 : 0 );
                echo  '<p class="check-radio"><label for="' . $id . '-variation" ><input class="_is_includes-woocommerce" type="checkbox" ' . $woo_sku_disable . ' id="' . $id . '-variation" name="' . $id . '[woo][variation]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search product variation", 'ivory-search' ) . '</label>' ;
                echo  IS_Admin::pro_link( 'pro_plus' ) . '</p>' ;
            } else {
                echo  '<span class="notice-is-info">' . __( 'Please first configure this search form to search WooCommerce product post type.', 'ivory-search' ) . '</span>' ;
            }
        
        } else {
            IS_Help::woocommerce_inactive_field_notice();
        }
        
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-author"><?php 
        echo  esc_html( __( 'Authors', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Search posts of selected authors.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $author_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
        
        if ( !isset( $excludes['author'] ) ) {
            $authors = get_users( array(
                'fields'  => array( 'ID', 'display_name' ),
                'orderby' => 'post_count',
                'order'   => 'DESC',
                'who'     => 'authors',
            ) );
            
            if ( !empty($authors) ) {
                if ( '' !== $author_disable ) {
                    echo  '<div class="upgrade-parent">' . IS_Admin::pro_link() ;
                }
                foreach ( $authors as $author ) {
                    $post_count = count_user_posts( $author->ID );
                    // Move on if user has not published a post (yet).
                    if ( !$post_count ) {
                        continue;
                    }
                    $checked = ( isset( $includes['author'][esc_attr( $author->ID )] ) ? $includes['author'][esc_attr( $author->ID )] : 0 );
                    echo  '<div class="col-wrapper check-radio"><label for="' . $id . '-author-' . esc_attr( $author->ID ) . '"><input class="_is_includes-author" type="checkbox" ' . $author_disable . ' id="' . $id . '-author-' . esc_attr( $author->ID ) . '" name="' . $id . '[author][' . esc_attr( $author->ID ) . ']" value="' . esc_attr( $author->ID ) . '" ' . checked( $author->ID, $checked, false ) . '/>' ;
                    echo  '<span class="toggle-check-text"></span> ' . ucfirst( esc_html( $author->display_name ) ) . '</label></div>' ;
                }
            }
        
        } else {
            echo  '<span class="notice-is-info">' . esc_html__( "This search form is already configured in the Excludes section to not search for specific author posts.", 'ivory-search' ) . '</span>' ;
        }
        
        if ( '' !== $author_disable ) {
            echo  '</div>' ;
        }
        $checked = ( isset( $includes['search_author'] ) && $includes['search_author'] ? 1 : 0 );
        echo  '<br /><br /><p class="check-radio"><label for="' . $id . '-search_author" ><input class="_is_includes-author" type="checkbox" id="' . $id . '-search_author" name="' . $id . '[search_author]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search author Display Name and display the posts created by that author", 'ivory-search' ) . '</label></p>' ;
        ?>
			</div></div>

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-post_status"><?php 
        echo  esc_html( __( 'Post Status', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Search posts having selected post statuses.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        
        if ( !isset( $excludes['post_status'] ) ) {
            $post_statuses = get_post_stati();
            $post_status_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
            
            if ( !empty($post_statuses) ) {
                if ( '' !== $post_status_disable ) {
                    echo  IS_Admin::pro_link() ;
                }
                foreach ( $post_statuses as $key => $post_status ) {
                    $checked = ( isset( $includes['post_status'][esc_attr( $key )] ) ? $includes['post_status'][esc_attr( $key )] : 0 );
                    echo  '<div class="col-wrapper check-radio"><label for="' . $id . '-post_status-' . esc_attr( $key ) . '"><input class="_is_includes-post_status" type="checkbox" ' . $post_status_disable . ' id="' . $id . '-post_status-' . esc_attr( $key ) . '" name="' . $id . '[post_status][' . esc_attr( $key ) . ']" value="' . esc_attr( $key ) . '" ' . checked( $key, $checked, false ) . '/>' ;
                    echo  '<span class="toggle-check-text"></span> ' . ucwords( str_replace( '-', ' ', esc_html( $post_status ) ) ) . '</label></div>' ;
                }
            }
        
        } else {
            echo  '<span class="notice-is-info">' . esc_html__( "This search form is already configured in the Excludes section to not search posts of specific post statuses.", 'ivory-search' ) . '</span>' ;
        }
        
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-comment_count"><?php 
        echo  esc_html( __( 'Comments', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Search posts by comments.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $comment_count_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
        if ( '' !== $comment_count_disable ) {
            echo  '<div class="upgrade-parent">' . IS_Admin::pro_link() ;
        }
        echo  '<label for="' . $id . '-comment_count-compare"> ' . esc_html( __( 'Search posts having number of comments', 'ivory-search' ) ) . '</label><select class="_is_includes-comment_count" name="' . $id . '[comment_count][compare]" ' . $comment_count_disable . ' style="min-width: 50px;">' ;
        $checked = ( isset( $includes['comment_count']['compare'] ) ? htmlspecialchars_decode( $includes['comment_count']['compare'] ) : '=' );
        $compare = array(
            '=',
            '!=',
            '>',
            '>=',
            '<',
            '<='
        );
        foreach ( $compare as $d ) {
            echo  '<option value="' . htmlspecialchars_decode( $d ) . '" ' . selected( $d, $checked, false ) . '>' . esc_html( $d ) . '</option>' ;
        }
        echo  '</select>' ;
        echo  '<select class="_is_includes-comment_count" name="' . $id . '[comment_count][value]" ' . $comment_count_disable . ' >' ;
        $checked = ( isset( $includes['comment_count']['value'] ) ? $includes['comment_count']['value'] : 'na' );
        echo  '<option value="na" ' . selected( 'na', $checked, false ) . '>' . esc_html( __( 'NA', 'ivory-search' ) ) . '</option>' ;
        for ( $d = 0 ;  $d <= 999 ;  $d++ ) {
            echo  '<option value="' . $d . '" ' . selected( $d, $checked, false ) . '>' . $d . '</option>' ;
        }
        echo  '</select>' ;
        if ( '' !== $comment_count_disable ) {
            echo  '</div>' ;
        }
        $checked = ( isset( $includes['search_comment'] ) && $includes['search_comment'] ? 1 : 0 );
        echo  '<br /><br /><p class="check-radio"><label for="' . $id . '-search_comment" ><input class="_is_includes-comment_count" type="checkbox" id="' . $id . '-search_comment" name="' . $id . '[search_comment]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search approved comment content", 'ivory-search' ) . '</label></p>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-date_query"><?php 
        echo  esc_html( __( 'Date', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Search posts created in the specified date range.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $range = array( 'after', 'before' );
        foreach ( $range as $value ) {
            $col_title = ( 'after' == $value ? __( 'From', 'ivory-search' ) : __( 'To', 'ivory-search' ) );
            echo  '<div class="col-wrapper ' . $value . '"><div class="col-title">' . $col_title . '</div>' ;
            echo  '<select class="_is_includes-date_query" name="' . $id . '[date_query][' . $value . '][day]" >' ;
            $checked = ( isset( $includes['date_query'][$value]['day'] ) ? $includes['date_query'][$value]['day'] : 'day' );
            echo  '<option value="day" ' . selected( 'day', $checked, false ) . '>' . esc_html( __( 'Day', 'ivory-search' ) ) . '</option>' ;
            for ( $d = 1 ;  $d <= 31 ;  $d++ ) {
                echo  '<option value="' . $d . '" ' . selected( $d, $checked, false ) . '>' . $d . '</option>' ;
            }
            echo  '</select>' ;
            echo  '<select class="_is_includes-date_query" name="' . $id . '[date_query][' . $value . '][month]" >' ;
            $checked = ( isset( $includes['date_query'][$value]['month'] ) ? $includes['date_query'][$value]['month'] : 'month' );
            echo  '<option value="month" ' . selected( 'month', $checked, false ) . '>' . esc_html( __( 'Month', 'ivory-search' ) ) . '</option>' ;
            for ( $m = 1 ;  $m <= 12 ;  $m++ ) {
                echo  '<option value="' . $m . '" ' . selected( $m, $checked, false ) . '>' . date( 'F', mktime(
                    0,
                    0,
                    0,
                    $m,
                    1
                ) ) . '</option>' ;
            }
            echo  '</select>' ;
            echo  '<select class="_is_includes-date_query" name="' . $id . '[date_query][' . $value . '][year]" >' ;
            $checked = ( isset( $includes['date_query'][$value]['year'] ) ? $includes['date_query'][$value]['year'] : 'year' );
            echo  '<option value="year" ' . selected( 'year', $checked, false ) . '>' . esc_html( __( 'Year', 'ivory-search' ) ) . '</option>' ;
            for ( $y = date( "Y" ) ;  $y >= 1995 ;  $y-- ) {
                echo  '<option value="' . $y . '" ' . selected( $y, $checked, false ) . '>' . $y . '</option>' ;
            }
            echo  '</select></div>' ;
        }
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-has_password"><?php 
        echo  esc_html( __( 'Password Protected', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        echo  '<div>' ;
        $checked = ( isset( $includes['has_password'] ) ? $includes['has_password'] : 'null' );
        echo  '<p class="check-radio"><label for="' . $id . '-has_password" ><input class="_is_includes-has_password" type="radio" id="' . $id . '-has_password" name="' . $id . '[has_password]" value="null" ' . checked( 'null', $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search posts with or without passwords", 'ivory-search' ) . '</label></p>' ;
        echo  '<p class="check-radio"><label for="' . $id . '-has_password_1" ><input class="_is_includes-has_password" type="radio" id="' . $id . '-has_password_1" name="' . $id . '[has_password]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search posts with passwords", 'ivory-search' ) . '</label></p>' ;
        echo  '<p class="check-radio"><label for="' . $id . '-has_password_0" ><input class="_is_includes-has_password" type="radio" id="' . $id . '-has_password_0" name="' . $id . '[has_password]" value="0" ' . checked( 0, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Search posts without passwords", 'ivory-search' ) . '</label></p>' ;
        ?>
			</div></div>

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-post_file_type"><?php 
        echo  esc_html( __( 'Attachments, Media, Files & MIME Types', 'ivory-search' ) ) ;
        ?></label>
			<span class="actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'ivory-search' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'ivory-search' );
        ?></a></span></h3>
			<div>
				<?php 
        $content = __( 'Search selected media, attachments, images, documents, videos, files or MIME types.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        global  $wp_version ;
        
        if ( 4.9 <= $wp_version ) {
            
            if ( $default_search || isset( $includes['post_type'] ) && in_array( 'attachment', $includes['post_type'] ) ) {
                
                if ( !isset( $excludes['post_file_type'] ) ) {
                    $file_types = get_allowed_mime_types();
                    
                    if ( !empty($file_types) ) {
                        $file_type_disable = ( is_fs()->is_plan_or_trial( 'pro_plus' ) && $this->is_premium_plugin ? '' : ' disabled ' );
                        ksort( $file_types );
                        echo  '<input class="list-search wide" placeholder="' . __( "Search..", 'ivory-search' ) . '" type="text">' ;
                        echo  '<select class="_is_includes-post_file_type" name="' . $id . '[post_file_type][]" ' . $file_type_disable . ' multiple size="8" >' ;
                        foreach ( $file_types as $key => $file_type ) {
                            $checked = ( isset( $includes['post_file_type'] ) && in_array( $file_type, $includes['post_file_type'] ) ? $file_type : 0 );
                            echo  '<option value="' . esc_attr( $file_type ) . '" ' . selected( $file_type, $checked, false ) . '>' . esc_html( $key ) . '</option>' ;
                        }
                        echo  '</select>' ;
                        echo  IS_Admin::pro_link( 'pro_plus' ) ;
                        echo  '<br /><br /><label for="' . $id . '-post_file_type" style="font-size: 10px;clear:both;display:block;">' . esc_html__( "Press CTRL key & Left Mouse button to select multiple terms or deselect them.", 'ivory-search' ) . '</label>' ;
                    }
                
                } else {
                    echo  '<span class="notice-is-info">' . esc_html__( "This search form is already configured in the Excludes section to not search specific file & MIME types.", 'ivory-search' ) . '</span>' ;
                }
            
            } else {
                echo  '<span class="notice-is-info">' . __( 'Please first configure this search form to search Attachment post type.', 'ivory-search' ) . '</span>' ;
            }
        
        } else {
            echo  '<span class="notice-is-info">' . __( 'You are using WordPress version less than 4.9 which does not support searching by MIME type.', 'ivory-search' ) . '</span>' ;
        }
        
        
        if ( isset( $includes['post_file_type'] ) ) {
            echo  '<br />' . __( 'Selected File Types :', 'ivory-search' ) ;
            foreach ( $includes['post_file_type'] as $post_file_type ) {
                echo  '<br /><span style="font-size: 11px;">' . $post_file_type . '</span>' ;
            }
        }
        
        ?>
			</div></div>
		</div>

		</div>

	<?php 
    }
    
    public function customize_panel( $post )
    {
        $id = '_is_customize';
        $settings = $post->prop( $id );
        $enable_customize = ( isset( $settings['enable_customize'] ) ? $settings['enable_customize'] : false );
        $field_class = ( $enable_customize ? '' : 'is-field-disabled' );
        ?>

		<h4 class="panel-desc"><?php 
        _e( "Use below options to customize this search form.", 'ivory-search' );
        ?></h4>
		<div class="search-form-editor-box" id="<?php 
        echo  esc_attr( $id ) ;
        ?>">

			<p class="check-radio">
				<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-enable_customize">
					<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-enable_customize" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-enable_customize" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[enable_customize]" value="1" <?php 
        checked( 1, $enable_customize );
        ?> data-depends="[<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_wrap,<?php 
        echo  esc_attr( $id ) ;
        ?>-description_length_wrap]"/>
					<span class="toggle-check-text"></span>
					<?php 
        esc_html_e( 'Enable Search Form Customization', 'ivory-search' );
        ?>
				</label>
			</p>

			<div class="form-table form-table-panel-customize">

				<!-- Search Results -->
				<h3 scope="row">
					<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-customizer"><?php 
        echo  esc_html( __( 'Customizer', 'ivory-search' ) ) ;
        ?></label>
					<span class="actions">
						<a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'ivory-search' );
        ?></a>
						<a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'ivory-search' );
        ?></a>
					</span>
				</h3>
				<div class="is-field-wrap <?php 
        echo  esc_attr( $field_class ) ;
        ?>">
					<span class="is-field-disabled-message"><span class="message"><?php 
        _e( 'Enable Search Form Customization', 'ivory-search' );
        ?></span></span>
                                        <?php 
        IS_Help::help_info( __( 'Use below customizer to customize search form colors, text and search form style.', 'ivory-search' ) );
        ?>
					<div>
                                            <?php 
        
        if ( isset( $_GET['post'] ) ) {
            $customizer_url = admin_url( 'customize.php?autofocus[section]=is_section_' . $_GET['post'] );
            if ( !$enable_customize ) {
                $customizer_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            }
            echo  '<a style="font-size: 20px;font-weight: 800; padding: 25px 0;display: block;text-align: center;box-shadow:none;"class="is-customize-link" href="' . $customizer_url . '">' . __( "Search Form Customizer", "ivory-search" ) . '</a>' ;
        }
        
        ?>
					</div>
				</div>
			</div>
		</div>

		<?php 
    }
    
    public function ajax_panel( $post )
    {
        $id = '_is_ajax';
        $settings = $post->prop( $id );
        $includes = $post->prop( '_is_includes' );
        // If not have any settings saved then set default value for fields.
        
        if ( empty($settings) ) {
            $show_description = true;
            $show_details_box = true;
            $show_more_result = true;
            $show_price = true;
            $show_matching_categories = true;
            $show_image = true;
            $search_results = 'both';
        } else {
            $show_description = ( isset( $settings['show_description'] ) && $settings['show_description'] ? 1 : 0 );
            $show_details_box = ( isset( $settings['show_details_box'] ) ? $settings['show_details_box'] : false );
            $show_more_result = ( isset( $settings['show_more_result'] ) && $settings['show_more_result'] ? 1 : 0 );
            $show_price = ( isset( $settings['show_price'] ) && $settings['show_price'] ? 1 : 0 );
            $show_matching_categories = ( isset( $settings['show_matching_categories'] ) && $settings['show_matching_categories'] ? 1 : 0 );
            $show_image = ( isset( $settings['show_image'] ) ? 1 : 0 );
            $search_results = ( isset( $settings['search_results'] ) ? $settings['search_results'] : 'both' );
        }
        
        $enable_ajax = ( isset( $settings['enable_ajax'] ) ? $settings['enable_ajax'] : false );
        $description_source = ( isset( $settings['description_source'] ) ? $settings['description_source'] : 'excerpt' );
        $description_length = ( isset( $settings['description_length'] ) ? $settings['description_length'] : 20 );
        $hide_price_out_of_stock = ( isset( $settings['hide_price_out_of_stock'] ) && $settings['hide_price_out_of_stock'] ? 1 : 0 );
        $show_sale_badge = ( isset( $settings['show_sale_badge'] ) && $settings['show_sale_badge'] ? 1 : 0 );
        $show_categories = ( isset( $settings['show_categories'] ) && $settings['show_categories'] ? 1 : 0 );
        $show_tags = ( isset( $settings['show_tags'] ) && $settings['show_tags'] ? 1 : 0 );
        $show_sku = ( isset( $settings['show_sku'] ) && $settings['show_sku'] ? 1 : 0 );
        $show_matching_tags = ( isset( $settings['show_matching_tags'] ) && $settings['show_matching_tags'] ? 1 : 0 );
        $show_stock_status = ( isset( $settings['show_stock_status'] ) && $settings['show_stock_status'] ? 1 : 0 );
        $show_featured_icon = ( isset( $settings['show_featured_icon'] ) && $settings['show_featured_icon'] ? 1 : 0 );
        $nothing_found_text = ( isset( $settings['nothing_found_text'] ) ? $settings['nothing_found_text'] : __( 'Nothing found', 'ivory-search' ) );
        $min_no_for_search = ( isset( $settings['min_no_for_search'] ) ? $settings['min_no_for_search'] : 1 );
        $view_all_results = ( isset( $settings['view_all_results'] ) ? $settings['view_all_results'] : false );
        $view_all_text = ( isset( $settings['view_all_text'] ) ? $settings['view_all_text'] : __( 'View All', 'ivory-search' ) );
        // Result Layout.
        $result_box_max_height = ( isset( $settings['result_box_max_height'] ) ? $settings['result_box_max_height'] : 400 );
        $more_result_text = ( isset( $settings['more_result_text'] ) ? $settings['more_result_text'] : __( 'More Results..', 'ivory-search' ) );
        $show_author = ( isset( $settings['show_author'] ) && $settings['show_author'] ? 1 : 0 );
        $show_date = ( isset( $settings['show_date'] ) && $settings['show_date'] ? 1 : 0 );
        // Details Box.
        $product_list = ( isset( $settings['product_list'] ) ? $settings['product_list'] : 'all' );
        $order_by = ( isset( $settings['order_by'] ) ? $settings['order_by'] : 'date' );
        $order = ( isset( $settings['order'] ) ? $settings['order'] : 'desc' );
        $field_class = ( $enable_ajax ? '' : 'is-field-disabled' );
        ?>
		<h4 class="panel-desc"><?php 
        _e( "Configure below options to manage AJAX functionality of this search form.", 'ivory-search' );
        ?></h4>
		<div class="search-form-editor-box" id="<?php 
        echo  esc_attr( $id ) ;
        ?>">

			<p class="check-radio">
				<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-enable_ajax">
					<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-enable_ajax" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-enable_ajax" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[enable_ajax]" value="1" <?php 
        checked( 1, $enable_ajax );
        ?> data-depends="[<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_wrap,<?php 
        echo  esc_attr( $id ) ;
        ?>-description_length_wrap]"/>
					<span class="toggle-check-text"></span>
					<?php 
        esc_html_e( 'Enable AJAX Search', 'ivory-search' );
        ?>
				</label>
			</p>

			<div class="form-table form-table-panel-ajax">
				<!-- Search Results -->
				<h3 scope="row">
					<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-search-form-search-results"><?php 
        esc_html_e( 'Search Results', 'ivory-search' );
        ?></label>
					<span class="actions">
						<a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'ivory-search' );
        ?></a>
						<a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'ivory-search' );
        ?></a>
					</span>
				</h3>
				<div class="is-field-wrap <?php 
        echo  $field_class ;
        ?>">
					<span class="is-field-disabled-message"><span class="message"><?php 
        _e( 'Enable AJAX Search', 'ivory-search' );
        ?></span></span>
                                        <?php 
        IS_Help::help_info( __( 'Display selected content in the search results.', 'ivory-search' ) );
        ?>
					<!-- Description -->
					<div class="is-field <?php 
        echo  esc_attr( $id ) ;
        ?>-description_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_description">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_description" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_description" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_description]" value="1" <?php 
        checked( 1, $show_description );
        ?> data-depends="[<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_wrap,<?php 
        echo  esc_attr( $id ) ;
        ?>-description_length_wrap]"/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Description', 'ivory-search' );
        ?>
							</label>
						</p>
					</div>
					<div class="is-field <?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_excerpt" >
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_excerpt" type="radio" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_excerpt" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[description_source]" value="excerpt" <?php 
        checked( 'excerpt', $description_source );
        ?>/>
								<span class="toggle-check-text"></span><?php 
        esc_html_e( "Excerpt", 'ivory-search' );
        ?>
							</label>
						</p>
						<p class="check-radio" style="margin-top: .5em;">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_content" >
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_content" type="radio" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_source_content" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[description_source]" value="content" <?php 
        checked( 'content', $description_source );
        ?>/>
								<span class="toggle-check-text"></span><?php 
        esc_html_e( "Content", 'ivory-search' );
        ?>
							</label>
						</p>
					</div>

					<!-- Description Length -->
					<div class="is-field <?php 
        echo  esc_attr( $id ) ;
        ?>-description_length_wrap">
                                            <input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_length" min="1" type="number" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-description_length" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[description_length]" value="<?php 
        echo  esc_attr( $description_length ) ;
        ?>"/>
                                            <p class="description"><?php 
        _e( 'Description Length.', 'ivory-search' );
        ?></p>
					</div>
					<!-- Image -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_image_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_image">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_image" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_image" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_image]" value="1" <?php 
        checked( 1, $show_image );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Image', 'ivory-search' );
        ?>
							</label>
						</p>
					</div>

					<!-- Categories -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-categories_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_categories">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_categories" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_categories" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_categories]" value="1" <?php 
        checked( 1, $show_categories );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Categories', 'ivory-search' );
        ?>
							</label>
						</p>
					</div>

					<!-- Tags -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-tags_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_tags">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_tags" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_tags" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_tags]" value="1" <?php 
        checked( 1, $show_tags );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Tags', 'ivory-search' );
        ?>
							</label>
						</p>
					</div>

					<!-- Show Author in Results -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_author_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_author">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_author" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_author" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_author]" value="1" <?php 
        checked( 1, $show_author );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Author', 'ivory-search' );
        ?>
							</label>
						</p>
					</div>
	
					<!-- Show Date in Results -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_date_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_date">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_date" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_date" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_date]" value="1" <?php 
        checked( 1, $show_date );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Date', 'ivory-search' );
        ?>
							</label>
						</p>
					</div>
					<!-- Minimum Number of Characters -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-min_no_for_search_wrap">
                                            <input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-min_no_for_search" type="number" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-min_no_for_search" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[min_no_for_search]" value="<?php 
        echo  $min_no_for_search ;
        ?>" />
                                            <p class="description"><?php 
        _e( 'Minimum number of characters required to run ajax search.', 'ivory-search' );
        ?></p>
					</div>
					<!-- Box Max Height -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-result_box_max_height_wrap">
                                            <input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-result_box_max_height" type="number" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-result_box_max_height" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[result_box_max_height]" value="<?php 
        echo  esc_attr( $result_box_max_height ) ;
        ?>"/>
                                            <p class="description"><?php 
        _e( 'Search results box max height.', 'ivory-search' );
        ?></p>
					</div>
                                        <br />
                                        <?php 
        IS_Help::help_info( __( 'Configure the plugin text displayed in the search results.', 'ivory-search' ) );
        ?>
					<!-- Nothing Found Text -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-nothing_found_text_wrap">
						<p>
                                                    <input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-nothing_found_text" type="text" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-nothing_found_text" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[nothing_found_text]" value="<?php 
        echo  $nothing_found_text ;
        ?>" />
                                                    <span class="description"><?php 
        _e( 'Text when there is no search results. HTML tags is allowed.', 'ivory-search' );
        ?></span>
						</p>
					</div>
					<!-- Show More Result -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_result_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_result">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_result" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-show_more_result" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[show_more_result]" value="1" <?php 
        checked( 1, $show_more_result );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'Show \'More Results..\' text in the bottom of the search results box', 'ivory-search' );
        ?>
							</label>
						</p>
					</div>
					<!-- More Result Text -->
					<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-more_result_text_wrap">
						<p>
							<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-more_result_text" type="text" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-more_result_text" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[more_result_text]" value="<?php 
        echo  esc_attr( $more_result_text ) ;
        ?>"/>
                                                        <span class="description"><?php 
        _e( 'Text for the "More Results..".', 'ivory-search' );
        ?></span>
						</p>
					</div>

					<!-- Show 'View All Results' -->
					<!--<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_results_wrap">
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_results">
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_results" type="checkbox" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_results" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[view_all_results]" value="1" <?php 
        checked( 1, $view_all_results );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( 'View All Result - Show link to search results page at the bottom of search results block.', 'ivory-search' );
        ?>
							</label>
						</p>
					</div>-->

					<!-- View All Text -->
					<!--<div class="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_text_wrap">
						<p>
							<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_text" type="text" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_text" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[view_all_text]" value="<?php 
        echo  esc_attr( $view_all_text ) ;
        ?>"/>
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-view_all_text"><?php 
        esc_html_e( 'Text for the "View All" which shown at the bottom of the search result.', 'ivory-search' );
        ?></label>
						</p>
					</div>-->
                                        <!-- Search Button Functionality -->
                                        <br />
                                        <?php 
        IS_Help::help_info( __( 'Configure how the search button should work clicking on it.', 'ivory-search' ) );
        ?>
					<div>
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-both" >
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-search_results" type="radio" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-both" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[search_results]" value="both" <?php 
        checked( 'both', $search_results );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( "Search button displays search results page", 'ivory-search' );
        ?>
							</label>
						</p>
						<p class="check-radio">
							<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-ajax_results" >
								<input class="<?php 
        echo  esc_attr( $id ) ;
        ?>-search_results" type="radio" id="<?php 
        echo  esc_attr( $id ) ;
        ?>-ajax_results" name="<?php 
        echo  esc_attr( $id ) ;
        ?>[search_results]" value="ajax_results" <?php 
        checked( 'ajax_results', $search_results );
        ?>/>
								<span class="toggle-check-text"></span>
								<?php 
        esc_html_e( "Search button displays ajax search results", 'ivory-search' );
        ?>
							</label>
						</p>
					</div>
				</div>

				<!-- WooCommerce -->
				<h3 scope="row">
					<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-search-form-woocommerce"><?php 
        esc_html_e( 'WooCommerce', 'ivory-search' );
        ?></label>
				</h3>
				<div class="is-field-wrap <?php 
        echo  $field_class ;
        ?>">
					<?php 
        
        if ( IS_Help::is_woocommerce_inactive() ) {
            IS_Help::woocommerce_inactive_field_notice();
        } else {
            
            if ( !isset( $includes['post_type'] ) || !in_array( 'product', $includes['post_type'] ) ) {
                echo  '<span class="notice-is-info">' . __( 'Please first configure this search form  to search WooCommerce product post type.', 'ivory-search' ) . '</span>' ;
            } else {
                ?>
						<span class="is-field-disabled-message"><span class="message"><?php 
                _e( 'Enable AJAX Search', 'ivory-search' );
                ?></span></span>
                                                <?php 
                IS_Help::help_info( __( 'Display selected WooCommerce content in the search results.', 'ivory-search' ) );
                ?>
						<!-- Price -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-price_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_price">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_price" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_price" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_price]" value="1" <?php 
                checked( 1, $show_price );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Price', 'ivory-search' );
                ?>
								</label>
							</p>
						</div>

						<!-- Price Out of Stock -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-price_out_of_stock_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-hide_price_out_of_stock">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-hide_price_out_of_stock" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-hide_price_out_of_stock" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[hide_price_out_of_stock]" value="1" <?php 
                checked( 1, $hide_price_out_of_stock );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Hide Price for Out of Stock Products', 'ivory-search' );
                ?>
								</label>
							</p>
						</div>

						<!-- Sale Badge -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-sale_badge_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sale_badge">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sale_badge" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sale_badge" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_sale_badge]" value="1" <?php 
                checked( 1, $show_sale_badge );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Sale Badge', 'ivory-search' );
                ?>
								</label>
							</p>
						</div>

						<!-- SKU -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-sku_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sku">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sku" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_sku" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_sku]" value="1" <?php 
                checked( 1, $show_sku );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'SKU', 'ivory-search' );
                ?>
								</label>
							</p>
						</div>

						<!-- Stock Status -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-stock_status_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_stock_status">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_stock_status" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_stock_status" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_stock_status]" value="1" <?php 
                checked( 1, $show_stock_status );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Stock Status', 'ivory-search' );
                ?>
								</label>
							</p>
						</div>

						<!-- Featured Icon -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-featured_icon_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_featured_icon">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_featured_icon" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_featured_icon" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_featured_icon]" value="1" <?php 
                checked( 1, $show_featured_icon );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Featured Icon', 'ivory-search' );
                ?>
								</label>
							</p>
						</div>

						<!-- Display Matching Categories -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-matching_categories_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_categories">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_categories" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_categories" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_matching_categories]" value="1" <?php 
                checked( 1, $show_matching_categories );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Matching Categories', 'ivory-search' );
                ?>
								</label>
							</p>
						</div>

						<!-- Display Matching Tags -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-matching_tags_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_tags">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_tags" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_matching_tags" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_matching_tags]" value="1" <?php 
                checked( 1, $show_matching_tags );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Matching Tags', 'ivory-search' );
                ?>
								</label>
							</p>
						</div>

						<!-- Show Details Box -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-details_box_wrap">
							<p class="check-radio">
								<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_details_box">
									<input class="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_details_box" type="checkbox" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-show_details_box" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[show_details_box]" value="1" <?php 
                checked( 1, $show_details_box );
                ?>/>
									<span class="toggle-check-text"></span>
									<?php 
                esc_html_e( 'Details Box', 'ivory-search' );
                ?>
								</label>
							</p>
						</div>
						<!-- Products List -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-product_list_wrap">
                                                        <?php 
                IS_Help::help_info( __( 'Below options only apply to matching categories or tags.', 'ivory-search' ) );
                ?><br />
							<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-product_list">
								<?php 
                esc_html_e( 'Product List', 'ivory-search' );
                ?>
							</label>
							<select class="<?php 
                echo  esc_attr( $id ) ;
                ?>-product_list" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-product_list" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[product_list]">
								<option value="all" <?php 
                selected( $product_list, 'all' );
                ?>><?php 
                _e( 'All Product', 'ivory-search' );
                ?></option>
								<option value="featured" <?php 
                selected( $product_list, 'featured' );
                ?>><?php 
                _e( 'Featured Products', 'ivory-search' );
                ?></option>
								<option value="onsale" <?php 
                selected( $product_list, 'onsale' );
                ?>><?php 
                _e( 'On-sale Products</option>', 'ivory-search' );
                ?></option>
							</select>
						</div>

						<!-- Order by -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-order_by_wrap">
							<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-order_by">
								<?php 
                esc_html_e( 'Order by', 'ivory-search' );
                ?>
							</label>
							<select class="<?php 
                echo  esc_attr( $id ) ;
                ?>-order_by" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-order_by" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[order_by]">
								<option value="date" <?php 
                selected( $order_by, 'date' );
                ?>><?php 
                _e( 'Date', 'ivory-search' );
                ?></option>
								<option value="price" <?php 
                selected( $order_by, 'price' );
                ?>><?php 
                _e( 'Price', 'ivory-search' );
                ?></option>
								<option value="rand" <?php 
                selected( $order_by, 'rand' );
                ?>><?php 
                _e( 'Random', 'ivory-search' );
                ?></option>
								<option value="sales" <?php 
                selected( $order_by, 'sales' );
                ?>><?php 
                _e( 'Sales', 'ivory-search' );
                ?></option>
							</select>
						</div>

						<!-- Order -->
						<div class="<?php 
                echo  esc_attr( $id ) ;
                ?>-order_wrap">
							<label for="<?php 
                echo  esc_attr( $id ) ;
                ?>-order">
								<?php 
                esc_html_e( 'Order', 'ivory-search' );
                ?>
							</label>
							<select class="<?php 
                echo  esc_attr( $id ) ;
                ?>-order" id="<?php 
                echo  esc_attr( $id ) ;
                ?>-order" name="<?php 
                echo  esc_attr( $id ) ;
                ?>[order]">
								<option value="asc" <?php 
                selected( $order, 'asc' );
                ?>><?php 
                _e( 'ASC', 'ivory-search' );
                ?></option>
								<option value="desc" <?php 
                selected( $order, 'desc' );
                ?>><?php 
                _e( 'DESC', 'ivory-search' );
                ?></option>
							</select>
						</div>

					<?php 
            }
        
        }
        
        ?>
				</div>

				<!-- Customizer -->
				<h3 scope="row">
					<label for="<?php 
        echo  esc_attr( $id ) ;
        ?>-customizer"><?php 
        echo  esc_html( __( 'Customizer', 'ivory-search' ) ) ;
        ?></label>
					<span class="actions">
						<a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'ivory-search' );
        ?></a>
						<a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'ivory-search' );
        ?></a>
					</span>
				</h3>
				<div class="is-field-wrap <?php 
        echo  esc_attr( $field_class ) ;
        ?>">
					<span class="is-field-disabled-message"><span class="message"><?php 
        _e( 'Enable AJAX Search', 'ivory-search' );
        ?></span></span>
                                        <?php 
        IS_Help::help_info( __( 'Use below customizer to customize AJAX search results color and loader image.', 'ivory-search' ) );
        ?>
					<div>
                                            <?php 
        
        if ( isset( $_GET['post'] ) ) {
            $customizer_url = admin_url( 'customize.php?autofocus[section]=is_section_' . $_GET['post'] );
            if ( !$enable_ajax ) {
                $customizer_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            }
            echo  '<a style="font-size: 20px;font-weight: 800; padding: 25px 0;display: block;text-align: center;box-shadow:none;"class="is-customize-link" href="' . $customizer_url . '">' . __( "AJAX Search Customizer", "ivory-search" ) . '</a>' ;
        }
        
        ?>
					</div>
				</div>
			</div>
		</div>
		<?php 
    }
    
    public function excludes_panel( $post )
    {
        $id = '_is_excludes';
        $excludes = $post->prop( $id );
        $includes = $post->prop( '_is_includes' );
        $default_search = ( NULL == $post->id() ? true : false );
        ?>
		<h4 class="panel-desc">
			<?php 
        _e( "This search form excludes the below configured content from search.", 'ivory-search' );
        ?>
		</h4>
		<div class="search-form-editor-box" id="<?php 
        echo  $id ;
        ?>">
		<div class="form-table form-table-panel-excludes">

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-post__not_in"><?php 
        echo  esc_html( __( 'Posts, Pages & Custom Posts', 'ivory-search' ) ) ;
        ?></label>
			<span class="actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'ivory-search' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'ivory-search' );
        ?></a></span></h3>
			<div>
				<?php 
        $content = __( 'Exclude selected posts from search.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $post_types = get_post_types( array(
            'public'              => true,
            'exclude_from_search' => false,
        ) );
        if ( isset( $includes['post_type'] ) && !empty($includes['post_type']) && is_array( $includes['post_type'] ) ) {
            $post_types = array_values( $includes['post_type'] );
        }
        
        if ( !isset( $includes['post__in'] ) ) {
            $posts_found = false;
            foreach ( $post_types as $post_type ) {
                $posts = get_posts( array(
                    'post_type'      => $post_type,
                    'posts_per_page' => 100,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                ) );
                
                if ( !empty($posts) ) {
                    $posts_found = true;
                    $html = '<div class="col-wrapper"><div class="col-title">';
                    $col_title = '<span>' . ucwords( $post_type ) . '</span>';
                    $temp = '';
                    $selected_pt = array();
                    foreach ( $posts as $post2 ) {
                        $checked = ( isset( $excludes['post__not_in'] ) && in_array( $post2->ID, $excludes['post__not_in'] ) ? $post2->ID : 0 );
                        if ( $checked ) {
                            array_push( $selected_pt, $post_type );
                        }
                        $post_title = ( isset( $post2->post_title ) && '' !== $post2->post_title ? esc_html( $post2->post_title ) : $post2->post_name );
                        $temp .= '<option value="' . esc_attr( $post2->ID ) . '" ' . selected( $post2->ID, $checked, false ) . '>' . $post_title . '</option>';
                    }
                    if ( !empty($selected_pt) && in_array( $post_type, $selected_pt ) ) {
                        $col_title = '<strong>' . $col_title . '</strong>';
                    }
                    $html .= $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'ivory-search' ) . '" type="text"></div>';
                    $html .= '<select class="_is_excludes-post__not_in" name="' . $id . '[post__not_in][]" multiple size="8" >';
                    $html .= $temp . '</select>';
                    if ( count( $posts ) >= 100 && !defined( 'DISABLE_IS_LOAD_ALL' ) ) {
                        $html .= '<div id="' . $post_type . '" class="load-all">' . __( 'Load All', 'ivory-search' ) . '</div>';
                    }
                    $html .= '</div>';
                    echo  $html ;
                }
            
            }
            
            if ( !$posts_found ) {
                echo  '<span class="notice-is-info">' . __( 'No posts created for selected post types.', 'ivory-search' ) . '</span>' ;
            } else {
                echo  '<br /><label for="' . $id . '-post__not_in" style="font-size: 10px;clear:both;display:block;">' . esc_html__( "Press CTRL key & Left Mouse button to select multiple terms or deselect them.", 'ivory-search' ) . '</label>' ;
            }
        
        } else {
            echo  '<span class="notice-is-info">' . __( 'This search form is already configured in the Includes section to search specific posts.', 'ivory-search' ) . '</span>' ;
        }
        
        ?>
			</div></div>

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-tax_query"><?php 
        esc_html_e( 'Category & Taxonomy Terms', 'ivory-search' );
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Exclude posts of selected categories, taxonomies & terms from search.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $tax_objs = get_object_taxonomies( $post_types, 'objects' );
        
        if ( !empty($tax_objs) ) {
            foreach ( $tax_objs as $key => $tax_obj ) {
                $terms = get_terms( array(
                    'taxonomy'   => $key,
                    'hide_empty' => false,
                ) );
                
                if ( !empty($terms) ) {
                    echo  '<div class="col-wrapper"><div class="col-title">' ;
                    $col_title = ucwords( str_replace( '-', ' ', str_replace( '_', ' ', esc_html( $key ) ) ) );
                    if ( isset( $excludes['tax_query'][$key] ) ) {
                        $col_title = '<strong>' . $col_title . '</strong>';
                    }
                    echo  $col_title . '<input class="list-search" placeholder="' . __( "Search..", 'ivory-search' ) . '" type="text"></div><select class="_is_excludes-tax_query" name="' . $id . '[tax_query][' . $key . '][]" multiple size="8" >' ;
                    foreach ( $terms as $key2 => $term ) {
                        $checked = ( isset( $excludes['tax_query'][$key] ) && in_array( $term->term_taxonomy_id, $excludes['tax_query'][$key] ) ? $term->term_taxonomy_id : 0 );
                        echo  '<option value="' . esc_attr( $term->term_taxonomy_id ) . '" ' . selected( $term->term_taxonomy_id, $checked, false ) . '>' . esc_html( $term->name ) . '</option>' ;
                    }
                    echo  '</select></div>' ;
                }
            
            }
            echo  '<br /><label for="' . $id . '-tax_query" style="font-size: 10px;clear:both;display:block;">' . esc_html__( "Press CTRL key & Left Mouse button to select multiple terms or deselect them.", 'ivory-search' ) . '</label>' ;
        } else {
            echo  '<span class="notice-is-info">' . __( 'No taxonomies registered for selected post types.', 'ivory-search' ) . '</span>' ;
        }
        
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-custom_field"><?php 
        echo  esc_html( __( 'Custom Fields & Metadata', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Exclude posts having selected custom fields from search.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $meta_keys = $this->is_meta_keys( $post_types );
        
        if ( !empty($meta_keys) ) {
            $custom_field_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
            echo  '<input class="list-search wide" placeholder="' . __( "Search..", 'ivory-search' ) . '" type="text">' ;
            echo  '<select class="_is_excludes-custom_field" name="' . $id . '[custom_field][]" ' . $custom_field_disable . ' multiple size="8" >' ;
            foreach ( $meta_keys as $meta_key ) {
                $checked = ( isset( $excludes['custom_field'] ) && in_array( $meta_key, $excludes['custom_field'] ) ? $meta_key : 0 );
                echo  '<option value="' . esc_attr( $meta_key ) . '" ' . selected( $meta_key, $checked, false ) . '>' . esc_html( $meta_key ) . '</option>' ;
            }
            echo  '</select>' ;
            echo  IS_Admin::pro_link() ;
            echo  '<br /><br /><label for="' . $id . '-custom_field" style="font-size: 10px;clear:both;display:block;">' . esc_html__( "Press CTRL key & Left Mouse button to select multiple terms or deselect them.", 'ivory-search' ) . '</label>' ;
        } else {
            echo  '<span class="notice-is-info">' . __( 'No custom fields created for selected post types.', 'ivory-search' ) . '</span>' ;
        }
        
        
        if ( isset( $excludes['custom_field'] ) ) {
            echo  '<br />' . __( 'Excluded Custom Fields :', 'ivory-search' ) ;
            foreach ( $excludes['custom_field'] as $custom_field ) {
                echo  '<br /><span style="font-size: 11px;">' . $custom_field . '</span>' ;
            }
        }
        
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-woocommerce"><?php 
        echo  esc_html( __( 'WooCommerce', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Exclude selected WooCommerce products from search.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        
        if ( class_exists( 'WooCommerce' ) ) {
            
            if ( in_array( 'product', $post_types ) ) {
                $outofstock_disable = ( is_fs()->is_plan_or_trial( 'pro_plus' ) && $this->is_premium_plugin ? '' : ' disabled ' );
                $checked = ( isset( $excludes['woo']['outofstock'] ) && $excludes['woo']['outofstock'] ? 1 : 0 );
                echo  '<p class="check-radio"><label for="' . $id . '-outofstock" ><input class="_is_excludes-woocommerce" type="checkbox" ' . $outofstock_disable . ' id="' . $id . '-outofstock" name="' . $id . '[woo][outofstock]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
                echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude 'Out of Stock' products", 'ivory-search' ) . '</label></p>' ;
                echo  IS_Admin::pro_link( 'pro_plus' ) ;
            } else {
                echo  '<span class="notice-is-info">' . __( 'Please first configure this search form to search WooCommerce product post type.', 'ivory-search' ) . '</span>' ;
            }
        
        } else {
            IS_Help::woocommerce_inactive_field_notice();
        }
        
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-author"><?php 
        echo  esc_html( __( 'Authors', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Exclude posts created by selected authors from search.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        
        if ( !isset( $includes['author'] ) ) {
            $author_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
            $authors = get_users( array(
                'fields'  => array( 'ID', 'display_name' ),
                'orderby' => 'post_count',
                'order'   => 'DESC',
                'who'     => 'authors',
            ) );
            
            if ( !empty($authors) ) {
                if ( '' !== $author_disable ) {
                    echo  IS_Admin::pro_link() ;
                }
                foreach ( $authors as $author ) {
                    $post_count = count_user_posts( $author->ID );
                    // Move on if user has not published a post (yet).
                    if ( !$post_count ) {
                        continue;
                    }
                    $checked = ( isset( $excludes['author'][esc_attr( $author->ID )] ) ? $excludes['author'][esc_attr( $author->ID )] : 0 );
                    echo  '<div class="col-wrapper check-radio"><label for="' . $id . '-author-' . esc_attr( $author->ID ) . '"><input class="_is_excludes-author" type="checkbox" ' . $author_disable . ' id="' . $id . '-author-' . esc_attr( $author->ID ) . '" name="' . $id . '[author][' . esc_attr( $author->ID ) . ']" value="' . esc_attr( $author->ID ) . '" ' . checked( $author->ID, $checked, false ) . '/>' ;
                    echo  '<span class="toggle-check-text"></span> ' . ucfirst( esc_html( $author->display_name ) ) . '</label></div>' ;
                }
            }
        
        } else {
            echo  '<span class="notice-is-info">' . esc_html__( "This search form is already configured in the Includes section to search posts created by specific authors.", 'ivory-search' ) . '</span>' ;
        }
        
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-post_status"><?php 
        echo  esc_html( __( 'Post Status', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
				<?php 
        $content = __( 'Exclude posts having selected post statuses from search.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        
        if ( !isset( $includes['post_status'] ) ) {
            $post_statuses = get_post_stati();
            $post_status_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
            
            if ( !empty($post_statuses) ) {
                if ( '' !== $post_status_disable ) {
                    echo  '<div class="upgrade-parent">' . IS_Admin::pro_link() ;
                }
                foreach ( $post_statuses as $key => $post_status ) {
                    $checked = ( isset( $excludes['post_status'][esc_attr( $key )] ) ? $excludes['post_status'][esc_attr( $key )] : 0 );
                    echo  '<div class="col-wrapper check-radio"><label for="' . $id . '-post_status-' . esc_attr( $key ) . '"><input class="_is_excludes-post_status" type="checkbox" ' . $post_status_disable . ' id="' . $id . '-post_status-' . esc_attr( $key ) . '" name="' . $id . '[post_status][' . esc_attr( $key ) . ']" value="' . esc_attr( $key ) . '" ' . checked( $key, $checked, false ) . '/>' ;
                    echo  '<span class="toggle-check-text"></span> ' . ucwords( str_replace( '-', ' ', esc_html( $post_status ) ) ) . '</label></div>' ;
                }
                if ( '' !== $post_status_disable ) {
                    echo  '</div>' ;
                }
            }
        
        } else {
            echo  '<span class="notice-is-info">' . esc_html__( "This search form is already configured in the Includes section to search posts of specific post statuses.", 'ivory-search' ) . '</span>' ;
        }
        
        $checked = ( isset( $excludes['ignore_sticky_posts'] ) && $excludes['ignore_sticky_posts'] ? 1 : 0 );
        echo  '<br /><br /><p class="check-radio"><label for="' . $id . '-ignore_sticky_posts" ><input class="_is_excludes-post_status" type="checkbox" id="' . $id . '-ignore_sticky_posts" name="' . $id . '[ignore_sticky_posts]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Exclude sticky posts from search", 'ivory-search' ) . '</label></p>' ;
        ?>
			</div></div>

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-post_file_type"><?php 
        echo  esc_html( __( 'Attachments, Media, Files & MIME Types', 'ivory-search' ) ) ;
        ?></label>
			<span class="actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'ivory-search' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'ivory-search' );
        ?></a></span></h3>
			<div>
				<?php 
        $content = __( 'Exclude selected media, attachments, images, documents, videos, files or MIME types from search.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        global  $wp_version ;
        
        if ( 4.9 <= $wp_version ) {
            
            if ( $default_search || isset( $includes['post_type'] ) && in_array( 'attachment', $includes['post_type'] ) ) {
                
                if ( !isset( $includes['post_file_type'] ) ) {
                    $file_types = get_allowed_mime_types();
                    
                    if ( !empty($file_types) ) {
                        $file_type_disable = ( is_fs()->is_plan_or_trial( 'pro_plus' ) && $this->is_premium_plugin ? '' : ' disabled ' );
                        ksort( $file_types );
                        echo  '<input class="list-search wide" placeholder="' . __( "Search..", 'ivory-search' ) . '" type="text">' ;
                        echo  '<select class="_is_excludes-post_file_type" name="' . $id . '[post_file_type][]" ' . $file_type_disable . ' multiple size="8" >' ;
                        foreach ( $file_types as $key => $file_type ) {
                            $checked = ( isset( $excludes['post_file_type'] ) && in_array( $file_type, $excludes['post_file_type'] ) ? $file_type : 0 );
                            echo  '<option value="' . esc_attr( $file_type ) . '" ' . selected( $file_type, $checked, false ) . '>' . esc_html( $key ) . '</option>' ;
                        }
                        echo  '</select>' ;
                        echo  IS_Admin::pro_link( 'pro_plus' ) ;
                        echo  '<br /><br /><label for="' . $id . '-post_file_type" style="font-size: 10px;clear:both;display:block;">' . esc_html__( "Press CTRL key & Left Mouse button to select multiple terms or deselect them.", 'ivory-search' ) . '</label>' ;
                    }
                
                } else {
                    echo  '<span class="notice-is-info">' . esc_html__( "This search form is already configured in the Includes section to search specific Attachments, Media or Files.", 'ivory-search' ) . '</span>' ;
                }
            
            } else {
                echo  '<span class="notice-is-info">' . __( 'Please first configure this search form to search Attachment post type.', 'ivory-search' ) . '</span>' ;
            }
        
        } else {
            echo  '<span class="notice-is-info">' . __( 'You are using WordPress version less than 4.9 which does not support searching by MIME type.', 'ivory-search' ) . '</span>' ;
        }
        
        
        if ( isset( $excludes['post_file_type'] ) ) {
            echo  '<br />' . __( 'Excluded File Types :', 'ivory-search' ) ;
            foreach ( $excludes['post_file_type'] as $post_file_type ) {
                echo  '<br /><span style="font-size: 11px;">' . $post_file_type . '</span>' ;
            }
        }
        
        ?>
			</div></div>
		</div>
		</div>
	<?php 
    }
    
    public function options_panel( $post )
    {
        $id = '_is_settings';
        $settings = $post->prop( $id );
        ?>
		<h4 class="panel-desc">
			<?php 
        _e( "Configure below options to manage functionality of this search form.", 'ivory-search' );
        ?>
		</h4>
		<div class="search-form-editor-box" id="<?php 
        echo  $id ;
        ?>">
		<div class="form-table form-table-panel-options">

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-posts_per_page"><?php 
        echo  esc_html( __( 'Posts Per Page', 'ivory-search' ) ) ;
        ?></label>
			<span class="actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'ivory-search' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'ivory-search' );
        ?></a></span></h3>
			<div>
			<?php 
        $content = __( 'Display selected number of posts on search results page.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        echo  '<select class="_is_settings-posts_per_page" name="' . $id . '[posts_per_page]" >' ;
        $default_per_page = get_option( 'posts_per_page', 10 );
        $checked = ( isset( $settings['posts_per_page'] ) ? $settings['posts_per_page'] : $default_per_page );
        for ( $d = 1 ;  $d <= 1000 ;  $d++ ) {
            echo  '<option value="' . $d . '" ' . selected( $d, $checked, false ) . '>' . $d . '</option>' ;
        }
        echo  '</select>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-order"><?php 
        echo  esc_html( __( 'Order Search Results', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div><?php 
        $content = __( 'Display posts on search results page ordered by selected options.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $orderby_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
        echo  '<select class="_is_settings-order" name="' . $id . '[orderby]" ' . $orderby_disable . ' >' ;
        $checked = ( isset( $settings['orderby'] ) ? $settings['orderby'] : 'date' );
        $orderbys = array(
            'date',
            'relevance',
            'none',
            'ID',
            'author',
            'title',
            'name',
            'type',
            'modified',
            'parent',
            'rand',
            'comment_count',
            'menu_order',
            'meta_value',
            'meta_value_num',
            'post__in',
            'post_name__in',
            'post_parent__in'
        );
        foreach ( $orderbys as $orderby ) {
            echo  '<option value="' . $orderby . '" ' . selected( $orderby, $checked, false ) . '>' . ucwords( str_replace( '_', ' ', esc_html( $orderby ) ) ) . '</option>' ;
        }
        echo  '</select><select class="_is_settings-order" name="' . $id . '[order]" ' . $orderby_disable . ' >' ;
        $checked = ( isset( $settings['order'] ) ? $settings['order'] : 'DESC' );
        $orders = array( 'DESC', 'ASC' );
        foreach ( $orders as $order ) {
            echo  '<option value="' . $order . '" ' . selected( $order, $checked, false ) . '>' . ucwords( str_replace( '_', ' ', esc_html( $order ) ) ) . '</option>' ;
        }
        echo  '</select>' ;
        echo  IS_Admin::pro_link() ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-highlight_terms"><?php 
        echo  esc_html( __( 'Highlight Search Terms', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div><div>
			<?php 
        $checked = ( isset( $settings['highlight_terms'] ) && $settings['highlight_terms'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-highlight_terms" ><input class="_is_settings-highlight_terms" type="checkbox" id="' . $id . '-highlight_terms" name="' . $id . '[highlight_terms]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Highlight searched terms on search results page", 'ivory-search' ) . '</label></p>' ;
        $color = ( isset( $settings['highlight_color'] ) ? $settings['highlight_color'] : '#FFFFB9' );
        echo  '<br /><input style="width: 80px;" class="_is_settings-highlight_terms" size="5" type="text" id="' . $id . '-highlight_color" name="' . $id . '[highlight_color]" value="' . $color . '" />' ;
        echo  '<label for="' . $id . '-highlight_color" > ' . esc_html__( "Set highlight color in Hex format", 'ivory-search' ) . '</label>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-term_rel"><?php 
        echo  esc_html( __( 'Search All Or Any Search Terms', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
			<?php 
        $content = __( 'Select whether to search posts having all or any of the words being searched.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $term_rel_disable = ( is_fs()->is_plan_or_trial( 'pro' ) && $this->is_premium_plugin ? '' : ' disabled ' );
        $checked = ( isset( $settings['term_rel'] ) && "OR" === $settings['term_rel'] ? "OR" : "AND" );
        echo  '<p class="check-radio"><label for="' . $id . '-term_rel_or" ><input class="_is_settings-term_rel" type="radio" ' . $term_rel_disable . ' id="' . $id . '-term_rel_or" name="' . $id . '[term_rel]" value="OR" ' . checked( 'OR', $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "OR - Display content having any of the search terms", 'ivory-search' ) . '</label>' . IS_Admin::pro_link() . '</p>' ;
        echo  '<p class="check-radio"><label for="' . $id . '-term_rel_and" ><input class="_is_settings-term_rel" type="radio" ' . $term_rel_disable . ' id="' . $id . '-term_rel_and" name="' . $id . '[term_rel]" value="AND" ' . checked( 'AND', $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "AND - Display content having all the search terms", 'ivory-search' ) . '</label></p>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-fuzzy_match"><?php 
        echo  esc_html( __( 'Fuzzy Matching', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div><?php 
        $content = __( 'Select whether to search posts having whole or partial word being searched.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $checked = ( isset( $settings['fuzzy_match'] ) ? $settings['fuzzy_match'] : '2' );
        echo  '<p class="check-radio"><label for="' . $id . '-whole" ><input class="_is_settings-fuzzy_match" type="radio" id="' . $id . '-whole" name="' . $id . '[fuzzy_match]" value="1" ' . checked( '1', $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Whole - Search posts that include the whole search term", 'ivory-search' ) . '</label></p>' ;
        echo  '<p class="check-radio"><label for="' . $id . '-partial" ><input class="_is_settings-fuzzy_match" type="radio" id="' . $id . '-partial" name="' . $id . '[fuzzy_match]" value="2" ' . checked( '2', $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Partial - Also search words in the posts that begins or ends with the search term", 'ivory-search' ) . '</label></p>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-keyword_stem"><?php 
        echo  esc_html( __( 'Keyword Stemming', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div>
			<?php 
        $content = __( 'Select whether to search the base word of a searched keyword.', 'ivory-search' );
        $content .= '<p>' . __( 'For Example: If you search "doing" then it also searches base word of "doing" that is "do" in the specified post types.', 'ivory-search' ) . '</p>';
        $content .= '<p>' . __( 'Not recommended to use when Fuzzy Matching option is set to Whole.', 'ivory-search' ) . '</p>';
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $stem_disable = ( is_fs()->is_plan_or_trial( 'pro_plus' ) && $this->is_premium_plugin ? '' : ' disabled ' );
        $checked = ( isset( $settings['keyword_stem'] ) && $settings['keyword_stem'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-keyword_stem" ><input class="_is_settings-keyword_stem" type="checkbox" id="' . $id . '-keyword_stem" ' . $stem_disable . ' name="' . $id . '[keyword_stem]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Also search base word of searched keyword", 'ivory-search' ) . '</label></p>' ;
        echo  IS_Admin::pro_link( 'pro_plus' ) ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-move_sticky_posts"><?php 
        echo  esc_html( __( 'Sticky Posts', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div><div>
			<?php 
        $checked = ( isset( $settings['move_sticky_posts'] ) && $settings['move_sticky_posts'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-move_sticky_posts" ><input class="_is_settings-move_sticky_posts" type="checkbox" id="' . $id . '-move_sticky_posts" name="' . $id . '[move_sticky_posts]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Display sticky posts to the start of the search results page", 'ivory-search' ) . '</label></p>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-empty_search"><?php 
        echo  esc_html( __( 'Empty Search Query', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div><?php 
        $content = __( 'Select whether to display an error when user perform search without any search word.', 'ivory-search' );
        IS_Help::help_info( $content );
        echo  '<div>' ;
        $checked = ( isset( $settings['empty_search'] ) && $settings['empty_search'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-empty_search" ><input class="_is_settings-empty_search" type="checkbox" id="' . $id . '-empty_search" name="' . $id . '[empty_search]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Display an error for empty search query", 'ivory-search' ) . '</label></p>' ;
        ?>
			</div></div>

			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-demo"><?php 
        echo  esc_html( __( 'Demo Search', 'ivory-search' ) ) ;
        ?></label>
			</h3>
			<div><div>
			<?php 
        $checked = ( isset( $settings['demo'] ) && $settings['demo'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-demo" ><input class="_is_settings-demo" type="checkbox" id="' . $id . '-demo" name="' . $id . '[demo]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Display search form only for site administrator", 'ivory-search' ) . '</label></p>' ;
        ?>
			</div></div>


			<h3 scope="row">
				<label for="<?php 
        echo  $id ;
        ?>-disable"><?php 
        echo  esc_html( __( 'Disable Search', 'ivory-search' ) ) ;
        ?></label>
			<span class="actions"><a class="expand" href="#"><?php 
        esc_html_e( 'Expand All', 'ivory-search' );
        ?></a><a class="collapse" href="#" style="display:none;"><?php 
        esc_html_e( 'Collapse All', 'ivory-search' );
        ?></a></span></h3>
			<div><div>
			<?php 
        $checked = ( isset( $settings['disable'] ) && $settings['disable'] ? 1 : 0 );
        echo  '<p class="check-radio"><label for="' . $id . '-disable" ><input class="_is_settings-disable" type="checkbox" id="' . $id . '-disable" name="' . $id . '[disable]" value="1" ' . checked( 1, $checked, false ) . '/>' ;
        echo  '<span class="toggle-check-text"></span>' . esc_html__( "Disable this search form", 'ivory-search' ) . '</label></p>' ;
        ?>
			</div></div>
		</div>
		</div>
		<?php 
    }

}