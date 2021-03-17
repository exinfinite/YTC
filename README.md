# Fetch youtube videos by api-key

## Install

```php
composer require exinfinite/ytc
```

## Usage

### 初始化

```php
$apikey = "api-key";
$agent = new Agent($apikey, __DIR__ . '/cache');
$fetcher = new PlaylistItems($agent);
```

### 取得清單中影片

> Reference：https://developers.google.com/youtube/v3/docs/playlistItems

```php
$playlistID = 'playlistID';
$data = $fetcher
        ->setPart(['snippet'])
        ->setPlaylistId($playlistID)
        ->setMaxResults(10)
        ->query(false);//true:ignore cache
```

## Roadmap

- [X] PlaylistItems
- [ ] Playlists
- [ ] Videos