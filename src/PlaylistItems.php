<?php
namespace Exinfinite\YTC;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class PlaylistItems {
    /**
     * API Ref：https://developers.google.com/youtube/v3/docs/playlistItems/list
     *
     * @var string
     */
    private $api = "https://www.googleapis.com/youtube/v3/playlistItems";
    private $params = [];
    const _PARAM_PART = 'part';
    const _PARAM_PLAYLISTID = 'playlistId';
    const _PARAM_MAXRESULTS = 'maxResults';
    public function __construct(\Exinfinite\YTC\Agent $agent) {
        $this->agent = $agent;
        $this->cache = $agent->getCache();
        $this->cache->setExpire((new \DateTime(date('Y-m-d H:i:s')))->modify('+60 minute'));
    }
    /**
     * 資料類型
     *
     * @param Array $parts
     * @return this
     */
    public function setPart(Array $parts) {
        $this->params[self::_PARAM_PART] = implode(',', $parts);
        return $this;
    }
    /**
     * 播放清單ID
     *
     * @param String $list_id
     * @return this
     */
    public function setPlaylistId($list_id) {
        $this->params[self::_PARAM_PLAYLISTID] = $list_id;
        return $this;
    }
    /**
     * 限制取得資料量
     *
     * @param Integer $num
     * @return this
     */
    public function setMaxResults($num) {
        $this->params[self::_PARAM_MAXRESULTS] = $num;
        return $this;
    }
    /**
     * 請求資料
     *
     * @param boolean $enforce 是否強制更新快取
     * @return array
     */
    public function query($enforce = false) {
        $handler = function () {
            try {
                $response = $this->agent->sendRequest($this->api, $this->params);
                $contents = $response->getBody()->getContents();
                return json_decode($contents, true);
            } catch (\Exception $e) {
                return [];
            }
        };
        $key = $this->cache->mapKey($this->params, 'playlistitems_');
        if ($enforce !== true) {
            return $this->cache->hit($key, $handler);
        }
        $rst = call_user_func($handler);
        $this->cache->force($key, $rst);
        return $rst;
    }
    /**
     * 預設資料結構
     *
     * @param String $list_id
     * @param integer $max_rst
     * @param boolean $assoc true:array;false:json
     * @return mixed
     */
    public function itemsList($list_id, $max_rst = 30, $assoc = true) {
        $this->setPart(['snippet'])->setPlaylistId($list_id)->setMaxResults((int) $max_rst);
        $data = collect($this->query())->only(['nextPageToken', 'prevPageToken', 'items', 'pageInfo']);
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