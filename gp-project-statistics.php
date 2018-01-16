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
		add_shortcode( 'gp-locale-statistics', array( $this, 'gp_locale_statistics' ) );
	}

	public function gp_locale_statistics( $atts ) {
		$return = '<style type="text/css">
	.gp-stats-table a:link { text-decoration: none !important; box-shadow: 0 0; }
	.gp-stats-table tbody td.percent100 {background: #46B450; color: white;}
	.gp-stats-table tbody td.percent90 {background: #6BC373;}
	.gp-stats-table tbody td.percent80 {background: #90D296;}
	.gp-stats-table tbody td.percent70 {background: #B5E1B9;}
	.gp-stats-table tbody td.percent60 {background: #C7E8CA;}
	.gp-stats-table tbody td.percent50 {background: #FFE399;}
	.gp-stats-table tbody td.percent40 {background: #FBC5A9;}
	.gp-stats-table tbody td.percent30 {background: #F1ADAD;}
	.gp-stats-table tbody td.percent20 {background: #EA8484;}
	.gp-stats-table tbody td.percent10 {background: #E35B5B;}
	.gp-stats-table tbody td.percent0 {background: #DC3232; color: white;}
</style>';

		$projects = GP::$project->all( "name" );
		if ( is_array( $atts ) ) {
			if( array_key_exists( 'name', $atts ) ) {
				$projects = GP::$project->find( array( 'name' => explode(',', $atts['name'] ) ), "name" );
			} else if ( array_key_exists( 'slug', $atts ) ) {
                                $projects = GP::$project->find( array( 'slug' => explode(',', $atts['slug'] ) ), "name" );
                        } else if( array_key_exists( 'id', $atts ) ) {
                                $projects = GP::$project->find( array( 'id' => explode(',', $atts['id'] ) ), "name" );
                        }
		}

		$unique_locales = array();
		$project_locales = array();

		$locales = GP::$translation_set->all();

		foreach($projects as $proj) {
			$project_locales[$proj->id] = array();
			foreach($locales as $loc) {
				if( ! in_array( $loc->locale, $unique_locales ) ) {
					$unique_locales[] = $loc->locale;
				}
				if( ! array_key_exists( $loc->locale, $project_locales[$proj->id] ) && $loc->project_id == $proj->id ) {
					$project_locales[$proj->id][$loc->locale] = $loc;
				}
			}
		}

		sort( $unique_locales );

		$return .= '<table class="gp-stats-table">';
		$return .= '<tr><th>Locales</th>';
		foreach( $projects as $proj ) {
			$return .= '<th align="center">' . $proj->name . '</th>';
		}

		$return .= '<th>Waiting</th></tr>';

		$percent_locales = array();

		foreach( $unique_locales as $loc ) {
			$percent_locales[$loc] = 0;
			foreach( $projects as $proj ) {
				if( $project_locales[$proj->id][$loc] != null ) {
					$percent_locales[$loc] += $project_locales[$proj->id][$loc]->percent_translated();
				}
			}
		}

		arsort( $percent_locales );

		foreach( $percent_locales as $loc => $value ) {
			$waiting = 0;
			$return .= '<tr><td align="center">' . gp_link_get( gp_url_join( gp_url('/languages'), $loc ), esc_html( $loc ) ) . '</td>';
			foreach( $projects as $proj ) {
				if( $project_locales[$proj->id][$loc] != null ) {
					$locale = $project_locales[$proj->id][$loc];
					$percent = $locale->percent_translated();
					$return .= '<td align="center" class="' . $this->get_percent_class($percent)  . '">';
					$return .= gp_link_get( gp_url_project_locale( $proj->path, $loc, $locale->slug ), $percent ." %" ) . '</td>';
					$waiting += $locale->waiting_count;
				} else {
					$return .= '<td align="center">—</td>';
				}
			}
			$return .= '<td align="center">' . $waiting . '</td>';
			$return .= '</tr>';
		}

		$return .= '</table>';
		return $return;
	}

	private function get_percent_class( $percent ) {
		if( $percent >= 100) {
			return 'percent100';
		} else if ( $percent >= 90 ) {
			return 'percent90';
		} else if ( $percent >= 80 ) {
			return 'percent80';
		} else if ( $percent >= 70 ) {
			return 'percent70';
		} else if ( $percent >= 60 ) {
                        return 'percent60';
                } else if ( $percent >= 50 ) {
                        return 'percent50';
                } else if ( $percent >= 40 ) {
                        return 'percent40';
                } else if ( $percent >= 30 ) {
                        return 'percent30';
                } else if ( $percent >= 20 ) {
                        return 'percent20';
                } else if ( $percent >= 10 ) {
                        return 'percent10';
                }
		return 'percent0';
	}

	public function gp_project_statistics( $atts ) {
		$projects = GP::$project->all( "name" );
		$return = '<table class="table">';
		foreach($projects as $project) {
			$return .= '<tr><th colspan="2">' .  $project->name . '</th></tr>';
			$sets = GP::$translation_set->by_project_id( $project->id );
			foreach($sets as $set) {
				$return .= '<tr><td>' . $set->name . '</td><td align="right">' . $set->percent_translated() . ' %</td></tr>';
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
