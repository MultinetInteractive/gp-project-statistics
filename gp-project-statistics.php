<?php
	/*
         * Plugin Name: GlotPress - Project Statistics
         * Plugin URI:  https://github.com/MultiNetInteractive/gp-project-statistics
         * Description: Statistics for GlotPress
         * Tags:        glotpress, statistics, glotpress plugin
         * Version:     1.0.0
         * Requires at least: 3.0
         * Tested up to: 4.9
         * Author:      Chris Gårdenberg, MultiNet Interactive AB
         * Author URI:  http://www.multinet.se
         * License:     GPL3
         * Text Domain: glotpress-project-statistics
         * Domain Path: /languages
	*/

class GP_Project_Statistics {
	public function __construct() {
		add_shortcode( 'gp-project-statistics', array( $this, 'gp_project_statistics' ) );
	}

	public function gp_project_statistics( $atts ) {
		$projects = GP::$project->all();
		$return = '<table class="table">';
		foreach($projects as $project) {
			$return .= '<tr><th colspan="2">' .  $project->name . '</th></tr>';
			$sets = GP::$translation_set->by_project_id( $project->id );
			foreach($sets as $set) {
				$return .= '<tr><td>' . $set->name . '</td><td>' . $set->percent_translated() . ' %</td></tr>';
			}
			$return .= '<tr><td colspan="2">&nbsp;</td></tr>';
		}
		$return .= '</table>';

		return $return;
	}
}

add_action( 'gp_init', 'gp_project_statistics_init' );

function gp_project_statistics_init() {
	GLOBAL $gp_project_statistics;
	$gp_project_statistics = new GP_Project_Statistics;
}
