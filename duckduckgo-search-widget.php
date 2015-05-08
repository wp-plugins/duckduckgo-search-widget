<?php
/*
Plugin Name: DuckDuckGo Search Widget
Description: This plugin adds a DuckDuckGo search widget to your site
Version: 1.0
*/


class duckduck_search_widgetClass {
	function __construct() {
		add_action( 'widgets_init', array( $this, 'register_dd_search_widget' ) );
		if( !is_admin() ) {
			add_filter( 'query_vars', array( $this, 'filter_query_vars' ) );
			add_action( 'template_redirect', array( $this, 'search_redirect' ) );
		}
	}
	
	function filter_query_vars( $qvs ) {
		$qvs[] = 'ddg-s';
		return $qvs;
	}
	
	function register_dd_search_widget() {
		register_widget( 'duckduckgo_search_widget' );
	}
	
	function search_redirect() {
		if( ! $ddg = get_query_var( 'ddg-s' ) )
			return;

		$vars = array( 
			'q' => get_search_query( false ) . ' site:' . preg_replace( '#^https?://#', '', home_url( '/' ) ),
			'kf' => '-1',	// No favicons/WOT ratings for single site
			'ki' => '-1',	// No disambiguations
		);
		
		$scheme = ( $ddg == 'ssl' ) ? 'https' : 'http';
		wp_redirect( $scheme . '://duckduckgo.com/?' . http_build_query( $vars ) );
		exit;
	}
}

class duckduckgo_search_widget extends WP_Widget {
	private $counter = 1;
	
	function duckduckgo_search_widget() {
		parent::__construct( false, 'DuckDuckGo Search Widget', array('description' => 'DuckDuckGo Search Widget'));
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title'] );
					//apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		
		$id = $this->counter;
		$ssl = $instance['ssl'] ? 'ssl' : 'no-ssl';
		
		echo "<form target='_blank' role='search' method='get' class='duckduckgo-search_widget' id='duckduckgo-search-$id' action='" . esc_url( home_url( '/' ) ) . "' >
			<table>	
				<tr>";
					
					if($instance['logo'] == 'on'){
		echo		"<td style='background:url(".plugins_url('logo.png', __FILE__).");padding-right: 40px;background-size: contain;background-repeat: no-repeat;'></td>";
					}
		echo "			
					<td><input type='text' placeholder='Enter your search' name='s' id='duckduckgo-s-$id' /></td>
				</tr>
				</table>
				<input type='hidden' name='ddg-s' value='$ssl' />

			</form>";
		
		echo $after_widget;
		$this->counter += 1;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] 	= $new_instance['title'];
		$instance['ssl'] 	= isset( $new_instance['ssl'] );
		$instance['logo'] 	= isset( $new_instance['logo'] );
		
	return $instance;
	}

	function form( $instance ) {
													// Default values
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'ssl' => true, 'logo' => false ) );
		$title = array( 'title-value' => esc_attr( $instance['title'] ), 'id' => $this->get_field_id('title'), 'name' => $this->get_field_name('title') );
		$ssl   = array( 'ssl-cb-value' => checked( $instance['ssl'], true, false ), 'id' => $this->get_field_id('ssl'), 'name' => $this->get_field_name('ssl') );
		$logo  = array( 'logo-cb-value' => checked( $instance['logo'], true, false ), 'id' => $this->get_field_id('logo'), 'name' => $this->get_field_name('logo') );
		
		echo "<p><label for='{$title['id']}'>" . __('Title:') . "</label> <input class='widefat' id='{$title['id']}' name='{$title['name']}' type='text' value='{$title['title-value']}' /></p>";
		echo "<p><label for='{$logo['id']}'><input id='{$logo['id']}' name='{$logo['name']}' type='checkbox' {$logo['logo-cb-value']} /> " . __('Show DuckDuckGo Logo') . '</label></p>';
		echo "<p><label for='{$ssl['id']}'><input id='{$ssl['id']}' name='{$ssl['name']}' type='checkbox' {$ssl['ssl-cb-value']} /> " . __('Use SSL') . '</label></p>';
	}
}

new duckduck_search_widgetClass();