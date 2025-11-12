<?php

namespace app\common\websocket;


class Parser
{
    /**
     * @notes 数组数据转json
     * @param string $event
     * @param $data
     * @return false|string
     * @author 段誉
     * @date 2021/12/29 18:27
     */
    public function encode(string $event, $data)
    {
        return json_encode(['event' => $event, 'data' => $data]);
    }

    /**
     * @notes json转数组数据
     * @param $data
     * @return array
     * @author 段誉
     * @date 2021/12/29 18:28
     */
    public function decode($data)
    {
        $result = json_decode($data, true);
        return [
            'event' => $result['event'] ?? null,
            'data' => $result['data'] ?? null,
        ];
    }

}
