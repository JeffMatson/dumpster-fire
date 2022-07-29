<?php
require 'vendor/autoload.php';

$session = new SpotifyWebAPI\Session(
    'THISISATOKENIDONTKNOWWHY',
    'THISISATOKENIDONTKNOWWHY',
    'http://spotify.dev'
);

$api = new SpotifyWebAPI\SpotifyWebAPI();

if (isset($_GET['code'])) {
    $session->requestAccessToken($_GET['code']);
	$api->setAccessToken($session->getAccessToken());
	// print_r($api->me());

	$tracks = $api->getMyTop( 'tracks', [
		'limit' => 100,
	]);

	$artist_ids = array();
	$genres = array();
	$total_items = 0;

	foreach ($tracks->items as $track) {
		// foreach( $track->artists as $artist ) {
			$artist_ids[] = $track->artists[0]->id;
		// }
	}

	foreach ( $artist_ids as $id ) {
		$artist = $api->getArtist( $id );
		// var_dump( $artist );

		foreach ( $artist->genres as $genre ) {
			$total_items++;
			if ( array_key_exists( $genre, $genres ) ) {
				$genres[ $genre ]++;
			} else {
				$genres[ $genre ] = 1;
			}
		}
	}

	arsort( $genres );

	// $chart = new \SamChristy\PieChart\PieChartGD(1200, 750);

	// $chart->setTitle('Music Genres');
	// Method chaining coming soon!

	echo 'Total Unique Genres: ' . count( $genres );
	echo '<br>';
	echo 'Total Unique Artists: ' . count( $artist_ids );
	echo '<br>';

	echo '<h1>Full Genre</h1>';

	foreach ( $genres as $genre => $count ) {
		$percent = ( $count / $total_items ) * 100;
		echo $genre . ': ' . $percent . '%';
		echo '<br>';
		// $chart->addSlice( $genre,   $count, '#4A7EBB');

	}

	$simplified_genres = array();
	$total_words = 0;
	foreach ( $genres as $genre => $count ) {
		if ( strpos( $genre, '-' ) ) {
			$word_delimited = explode( '-', $genre );
		} elseif ( strpos( $genre, ' ' ) ) {
			$word_delimited = explode( ' ', $genre );
		} else {
			$word_delimited = array( $genre );
		}

		foreach ( $word_delimited as $word ) {
			$total_words++;
			if ( array_key_exists( $word, $simplified_genres ) ) {
				$simplified_genres[ $word ] = $simplified_genres[ $word ] + $count;
			} else {
				$simplified_genres[ $word ] = $count;
			}
		}


		// $chart->addSlice( $genre,   $count, '#4A7EBB');

	}

	echo '<h1>Simplified into individual words</h1>';

	arsort( $simplified_genres );

	foreach ( $simplified_genres as $individual_word => $count ) {
		$percent = ( $count / $total_words ) * 100;
		echo $individual_word . ': ' . $percent . '%';
		echo '<br>';
	}


	// $chart->draw();
	// $chart->outputPNG();



	// var_dump( $genres );

	// $artists = $api->getArtists( $artist_ids );

	// var_dump( $artists );


} else {
    $options = [
        'scope' => [
			'user-read-email',
			'user-library-read',
			'user-top-read',
			'user-read-recently-played'
        ],
    ];

    header('Location: ' . $session->getAuthorizeUrl($options));
    die();
}
