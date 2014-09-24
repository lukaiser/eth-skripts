<?php

/* ------------------------------------------------------------------------ *
 * Google Webfonts
 * ------------------------------------------------------------------------ */

function fitzgerald_enqueue_styles() {
	wp_enqueue_style( 'fitzgerald-fonts', 'https://fonts.googleapis.com/css?family=Crimson+Text:400,400italic,700|Roboto+Condensed:400,300,300italic,400italic' );
}

add_action( 'wp_print_styles', 'fitzgerald_enqueue_styles' );

add_filter("pb_lists_add_numbers_to_list_elements", function(){return true;});

/**
 * Returns an html blog of meta elements 
 * 
 * @return string $html metadata
 */
function pbt_get_seo_meta_elements() {
	// map items that are already captured
	$meta_mapping = array(
	    'author' => 'pb_author',
	    'description' => 'pb_about_50',
	    'keywords' => 'pb_keywords_tags',
	    'publisher' => 'pb_publisher'
	);

	$html = "<meta name='application-name' content='PressBooks'>\n";
	$metadata = \PressBooks\Book::getBookInformation();

	// create meta elements
	foreach ( $meta_mapping as $name => $content ) {
		if ( array_key_exists( $content, $metadata ) ) {
			$html .= "<meta name='" . $name . "' content='" . $metadata[$content] . "'>\n";
		}
	}

	return $html;
}

function pbt_get_microdata_meta_elements() {
	// map items that are already captured
	$html = '';
	$micro_mapping = array(
	    'about' => 'pb_bisac_subject',
	    'alternativeHeadline' => 'pb_subtitle',
	    'author' => 'pb_author',
	    'copyrightHolder' => 'pb_copyright_holder',
	    'copyrightYear' => 'pb_copyright_year',
	    'datePublished' => 'pb_publication_date',
	    'description' => 'pb_about_50',
	    'editor' => 'pb_editor',
	    'image' => 'pb_cover_image',
	    'inLanguage' => 'pb_language',
	    'keywords' => 'pb_keywords_tags',
	    'publisher' => 'pb_publisher',
	);
	$metadata = \PressBooks\Book::getBookInformation();

	// create microdata elements
	foreach ( $micro_mapping as $itemprop => $content ) {
		if ( array_key_exists( $content, $metadata ) ) {
			if ( 'pb_publication_date' == $content ) {
				$content = date( 'Y-m-d', $metadata[$content] );
			} else {
				$content = $metadata[$content];
			}
			$html .= "<meta itemprop='" . $itemprop . "' content='" . $content . "' id='" . $itemprop . "'>\n";
		}
	}

	// add elements that aren't captured, and don't need user input
	$lrmi_meta = array(
	    'educationalAlignment' => $metadata['pb_bisac_subject'],
	    'educationalUse' => 'Open textbook study',
	    'audience' => 'student',
	    'interactivityType' => 'mixed',
	    'learningResourceType' => 'textbook',
	    'typicalAgeRange' => '17-',
	);

	foreach ( $lrmi_meta as $itemprop => $content ) {
		// @todo parse educationalAlignment items into alignmentOjects
		$html .= "<meta itemprop='" . $itemprop . "' content='" . $content . "' id='" . $itemprop . "'>\n";
	}
	return $html;
}


/**
 * Prevent access by unregistered user if the book in question is private.
 */
function pb_private() {
    $bloginfourl= get_bloginfo('url'); ?>
    <div <?php post_class(); ?>>

        <h2 class="entry-title denied-title"><?php _e('Access Denied', 'pressbooks'); ?></h2>
        <!-- Table of content loop goes here. -->
        <div class="entry_content denied-text"><?php _e('This book is private, and accessible only to registered users. If you have an account you can <a href="'. $bloginfourl .'/wp-login.php?redirect_to='.$bloginfourl.'" class="login">login here</a>  <p class="sign-up">You can also set up your own PressBooks book at: <a href="http://pressbooks.com">PressBooks.com</a>.', 'pressbooks'); ?></p></div>
    </div><!-- #post-## -->
<?php
}
