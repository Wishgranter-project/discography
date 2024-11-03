## Overview
A library to retrieve information on the discography of artists.

## Where the information comes from ?
Currently the library supports Discogs and MusicBrainz.

## Instantiating
```php
use WishgranterProject\Discography\Discogs\ApiDiscogs;
use WishgranterProject\Discography\Discogs\Source\SourceDiscogs;
use WishgranterProject\Discography\MusicBrainz\ApiMusicBrainz;
use WishgranterProject\Discography\MusicBrainz\Source\SourceMusicBrainz;

$discogsApi     = new ApiDiscogs('your discogs api token goes here');
$discogs        = new SourceDiscogs($discogsApi);

$musicBrainzApi = new ApiMusicBrainz();
$musicBrainz    = new SourceMusicBrainz($musicBrainzApi);
```

## Searching for artists by name
```php
$artists = $source->searchForArtist('Metallica');
```

## Searching albuns by artist name
```php
$albums = $source->getArtistsAlbums('Metallica');
```

## Search album by artist name and title.
```php
$album = $source->getAlbum($artistName, $albumTitle);
```

## License
MIT
