<?php

namespace app\common\websocket;


use app\common\enum\ChatMsgEnum;

class Response
{
    /**
     * @notes 结果数据
     * @param string $msg
     * @param array $data
     * @param int $code
     * @return array
     * @author 段誉
     * @date 2021/12/29 18:26
     */
    private function result(string $msg = 'OK', array $data = [], int $code = 10001)
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
        return $result;
    }


    /**
     * @notes 成功
     * @param string $msg
     * @param array $data
     * @param int $code
     * @return array
     * @author 段誉
     * @date 2021/12/29 18:26
     */
    public function success(string $msg = 'OK', array $data = [], int $code = 10001)
    {
        return $this->result($msg, $data, $code);
    }


    /**
     * @notes 错误
     * @param string $msg
     * @param array $data
     * @param int $code
     * @return array
     * @author 段誉
     * @date 2021/12/29 18:27
     */
    public function error(string $msg = 'Error', array $data = [], int $code = 20001)
    {
        return $this->result($msg, $data, $code);
    }


    /**
     * @notes 整理返送错误信息
     * @param string $msg
     * @param int $msg_type
     * @return array
     * @author 段誉
     * @date 2021/12/29 18:27
     */
    public function formatSendError(string $msg, int $msg_type = 0)
    {
        if (empty($msg_type)) {
            $msg_type = ChatMsgEnum::TYPE_TEXT;
        }

        return [
            'msg' => $msg,
            'msg_type' => $msg_type,
        ];
    }

}
