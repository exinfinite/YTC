<?php
namespace Exinfinite\YTC;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class PlaylistItems {
    private $api = "https://www.googleapis.com/youtube/v3/playlistItems";
    private $params = [];
    const _PARAM_PART = 'part';
    const _PARAM_PLAYLISTID = 'playlistId';
    const _PARAM_MAXRESULTS = 'maxResults';
    public function __construct(Agent $agent) {
        $this->agent = $agent;
        $this->cache = $agent->getCache();
        $this->cache->setExpire((new \DateTime(date('Y-m-d H:i:s')))->modify('+30 minute'));
    }
    public function setPart(Array $parts) {
        $this->params[self::_PARAM_PART] = implode(',', $parts);
        return $this;
    }
    public function setPlaylistId($list_id) {
        $this->params[self::_PARAM_PLAYLISTID] = $list_id;
        return $this;
    }
    public function setMaxResults($num) {
        $this->params[self::_PARAM_MAXRESULTS] = $num;
        return $this;
    }
    public function sendRequest() {
        return $this->cache->hit(
            $this->cache->mapKey($this->params, 'playlistitems_'),
            function () {
                $response = $this->agent->sendRequest($this->api, $this->params);
                $contents = $response->getBody()->getContents();
                try {
                    return json_decode($contents, true);
                } catch (\Exception $e) {
                    return [];
                }
            });
    }
    public function itemsList($list_id, $assoc = false) {
        $this->setPart(['snippet'])->setPlaylistId($list_id)->setMaxResults(50);
        $data = collect($this->sendRequest())->only(['nextPageToken', 'prevPageToken', 'items', 'pageInfo']);
        $items = $data
            ->only(['items'])
            ->flatten(1)
            ->map(function ($item) {
                return collect($item['snippet'])->only(['publishedAt', 'title', 'description', 'thumbnails', 'resourceId']);
            });
        $rst = [
            "nextPageToken" => $data->get('nextPageToken', null),
            "prevPageToken" => $data->get('prevPageToken', null),
            "totalResults" => $data->get('pageInfo', null),
            "items" => $items->toArray(),
        ];
        return $assoc === true ? $rst : json_encode($rst, JSON_UNESCAPED_UNICODE);
    }
}