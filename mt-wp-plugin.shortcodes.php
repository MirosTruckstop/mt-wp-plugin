<?php
/*
 * 
 */
add_shortcode('mt_photo', 'mt_photo');
function mt_photo($atts) {
    $a = shortcode_atts( array(
        'id' => '',
		'width' => '200'
    ), $atts);	
	
	$photo = new MT_Photo($a['id']);
	$item = $photo->getOne(array('id', 'path'), 'ARRAY_A');
	return '<img width="'.$a['width'].'" src="bilder/'.$item['path'].'">';
}

add_shortcode('total_number_of_photos', 'mt_add_shortcode_numPhotos');
function mt_add_shortcode_numPhotos() {
	return (new MT_Photo)->getCount();
}

add_shortcode('latest_news_date', 'mt_add_shortcode_latest_news_date');
function mt_add_shortcode_latest_news_date($atts) {
    $a = shortcode_atts( array(
        'format' => '%e. %B %Y'
    ), $atts );
	
	return strftime($a['format'], (new MT_News())->getLatestNewsTimestamp());
}

add_shortcode('latest_photo_date', 'mt_add_shortcode_latest_photo_date');
function mt_add_shortcode_latest_photo_date($atts) {
    $a = shortcode_atts( array(
        'format' => '%e. %B %Y'
    ), $atts );
	
	return strftime($a['format'], (new MT_Photo())->getLatestPhotoDate());
}

add_shortcode('mt_guestbook', 'mt_add_shortcode_guestbook');
function mt_add_shortcode_guestbook() {
	return '<iframe onload="if ( typeof ResizeIframe != \'undefined\' ) res = ResizeIframe; else if ( typeof top.ResizeIframe != \'undefined\' ) res = top.ResizeIframe; res.resize(document, this.id);" src="http://www.rosensturm.de/tinc?key=WnXUPJB7&amp;start=-1&amp;reverse=1" class="gaestebuch" id="tincInclude2624">X</iframe>';
}

add_shortcode('mt_statistics', 'mt_add_shortcode_statistics');
function mt_add_shortcode_statistics() {
	$returnString = '
			<table class="horizontalLeft">
			 <tr>
			  <th>'.__('Galerie', MT_NAME).'</th>
			  <th>'.__('Anzahl der Bilder', MT_NAME).'</th>
			 </tr>	
	';
	$tempCategoryId = 0;
	$tempSubcategoryId = 0;

	$query = (new MT_QueryBuilder())
		->from('photo')
		->select('COUNT(wp_mt_photo.id) as numPhotos')
		->joinInner('gallery', TRUE, array('id AS galleryId', 'name as galleryName'))
		->joinInner('category', 'wp_mt_category.id = wp_mt_gallery.category', array('id AS categoryId', 'name AS categoryName'))
		->joinLeftOuter('subcategory', 'wp_mt_subcategory.id = wp_mt_gallery.subcategory', array('id AS subcategoryId', 'name subcategoryName'))
		->whereEqual('wp_mt_photo.show', 1)
		->groupBy(array('categoryName', 'subcategoryName', 'galleryName'))
		->orderBy(array('categoryName', 'subcategoryName', 'galleryName'));
	foreach ($query->getResult() as $row) {
		// Category
		if( $row->categoryId != $tempCategoryId ) {
			$tempCategoryId = $row->categoryId;
			$returnString .= '
			 <tr>
			  <td><u>'.$row->categoryName.'</u></td>
			  <td></td>
			 </tr>';
		}

		// Subcategory
		if( $row->subcategoryId != $tempSubcategoryId) {
			$tempSubcategoryId = $row->subcategoryId;
			$returnString .= '
			 <tr>
			  <td>&nbsp;&nbsp;»&nbsp;&nbsp;'.$row->subcategoryName.'</td>
			  <td></td>
			 </tr>';
		}

		// Gallery
		$returnString .= '
			 <tr>
			  <td>&nbsp;&nbsp;&nbsp;&nbsp;»&nbsp;&nbsp;<a href="'.MT_Photo::GALLERY_PATH_ABS.'/'.$row->galleryId.'">'.$row->galleryName.'</a></td>
			  <td>'.$row->numPhotos.'</td>
			 </tr>';
	}
	$returnString .= '</table>';
	return $returnString;
}

add_shortcode('mt_recent_post', 'mt_add_shortcode_recent_post');
function mt_add_shortcode_recent_post() {
	$recent_posts = wp_get_recent_posts();
	$returnString = '';
	foreach( $recent_posts as $recent ){
		$returnString .= '<h3>'.$recent["post_title"].'</h3>'.$recent["post_content"].'<div class="postDate">Verfasst am: '.$recent["post_modified"].'</div>';
	}	
	return $returnString;
}

add_shortcode('mt_news', 'mt_add_shortcode_news');
function mt_add_shortcode_news() {
	$returnString = '';

	$dateYear_old = '';
	$dateMonth_old = '';
	$dateDay_old = '';

	$newsItems = MT_News::getAll(array('title', 'text', 'gallery', 'date' ), 'date DESC');
	foreach ($newsItems as $item) {
		// News link
		if( empty( $item->gallery ) ) {
			$news_link = '../';
		} else {
			$news_link = MT_Photo::GALLERY_PATH_ABS.'/'.$item->gallery;
		}

		// Year
		$dateYear = strftime( '%Y', $item->date );
		if( $dateYear != $dateYear_old && $dateYear != date( 'Y', time() ) ) {
			$dateYear_old = $dateYear;
			$returnString .= '
			</table>
			<hr>
			<center><h1>' . $dateYear . '</h1></center>';
		}

		// Month
		$dateMonth = strftime( '%B', $item->date );
		if($dateMonth != $dateMonth_old) {
				
			// Beim ersten Monat <table> noch nicht beenden
			if( !empty( $dateMonth_old ) ) {
				$returnString .= '</table>';
			}
			$dateMonth_old = $dateMonth;

			$returnString .= '
			<h3>' . $dateMonth . '</h3>
			<table class="table_quer" cellSpacing="4" cellPadding="1">
				<colgroup>
    				<col width="95px">
    				<col width="*">
  				</colgroup>';
					}
					
		// Day
		$returnString .= '<tr>';
				
		$dateDay = strftime( '%a, %d.%m.', $item->date );
				
		if($dateDay != $dateDay_old) {
			$dateDay_old = $dateDay;
					
			$returnString .= '<th>'. $dateDay . ':</th>';
		}
		else {
			$returnString .= '<th></th>';
		}
		$returnString .= '<td><a href="' . $news_link . '">' . $item->title . '</a><br>' . $item->text . '</td>
				</tr>';
	}
	$returnString .= '</table>';		
	return $returnString;
}


add_shortcode('mt_photographers', 'mt_add_shortcode_photographers');
function mt_add_shortcode_photographers() {
	$returnString = '<ul>';
	
	$photo = new MT_Photo();
	
	$items = MT_Photographer::getAll(array('id', 'name'), 'name');
	foreach ($items as $item) {
		$returnString .= '<li><a href="'.MT_Photographer::$photographersPath.$item->id.'">'.$item->name.'</a>&nbsp;<span class="style_grew">('.$photo->getNumPhotos($item->id).')</span></li>';				
	}
	$returnString .= '</ul>';
	return $returnString;
}