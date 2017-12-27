<?php

namespace allensnape\utils\websocket;

interface IServer {

    /**
     * websocket握手之前
     * @param Socket $socket    握手的socket
     * @return void
     */
    public function beforeHandshake($socket=null);

    /**
     * websocket握手之后
     *
     * @param Socket $socket    握手的socket
     * @param boolean $result   socket_write()返回的数据
     * @return void
     */
    public function afterHandshake($socket=null, $result=false);

    /**
     * 收到socket消息时
     *
     * @param Socket $socket        收到消息的socket
     * @param string $msg           消息内容
     * @return void
     */
    public function receiveMessage($socket=null, $msg='');

    /**
     * 断开链接之前
     * @param Socket $socket        进行断开连接的socket
     * @return void
     */
    public function beforeDisconnect($socket=null);

}