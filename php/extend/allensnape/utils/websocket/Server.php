<?php
namespace allensnape\utils\websocket;

require("IServer.php");

class Server implements IServer{

	var $master;
	var $sockets = array();
	var $handshakedSockets = [];
	var $debug = true;
	
	// 数据库连接
	var $mysqli = null;
	// 初始化数据库连接
	function initDatabase(){
		$this->mysqli = new \mysqli('localhost', 'root', 'root', 'webbet') or die('Could not get connected: ' . mysqli_error());
	}
	// 关闭数据库连接
	function closeDatabase(){
		$this->mysqli->close();
	}
	// 执行SQL
	function executeSql($sql){
		$data = $this->mysqli->query($sql);
		if(mysqli_errno($this->mysqli)){
			$this->initDatabase();
		}
		return $data;
	}

    /**
     * 构造函数. 用于初始化数据和对端口的实时监听
     * @param string $address       绑定监听的地址
     * @param string $port          绑定监听的端口
     */
	function __construct($address, $port){
        // 创建主连接
		$this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)     or die("socket_create() failed");
		socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1)    or die("socket_option() failed");
		socket_bind($this->master, $address, $port)                      or die("socket_bind() failed");
		socket_listen($this->master, 20)                                 or die("socket_listen() failed");

		// 初始化数据库
		$this->initDatabase();
        
        // 输出监听信息
		$this->sockets[] = $this->master;
		$this->say("Server Started : ".date('Y-m-d H:i:s'));
		$this->say("Listening on   : ".$address." port ".$port);
		$this->say("Master socket  : ".$this->master."\n");
        
        // 循环监听端口
		while(true){
			$socketArr = $this->sockets;
			$write = NULL;
			$except = NULL;
			socket_select($socketArr, $write, $except, NULL);  //自动选择来消息的socket 如果是握手 自动选择主机
			foreach ($socketArr as $socket){
				if ($socket == $this->master){  //主机
					$client = socket_accept($this->master);
					if ($client < 0){
						$this->log("socket_accept() failed", $socket);
						continue;
					} else{
						$this->connect($client);
					}
				} else {
					$bytes = @socket_recv($socket,$buffer,2048,0);
					if ($bytes == 0){
						$this->disConnect($socket);
					}
					else{
						if (!in_array($socket, $this->handshakedSockets)){
                            $this->beforeHandshake($socket);
                            $this->afterHandshake($socket, $this->doHandShake($socket, $buffer));
						}
						else{
							$msg = $this->decode($buffer);
                            $this->log("> " . $msg, $socket);
                            try{
                                $this->receiveMessage($socket, $msg);
                            }catch(\Exception $e){
                                $this->log($e->getMessage(), $socket);
                            }
							//$this->sendAll($msg);
							//$this->send($socket, $msg);
						}
					}
				}
			}
		}
	}
    
    /**
     * 将消息发送给所有连接
     * @param string $msg   发送的内容
     * @return void
     */
	function sendAll($msg){
		foreach ($this->sockets as $socket)
			if($socket != $this->master)
				$this->send($socket, $msg);
    }
    
    /**
     * 给单个连接发送消息
     * @param string $client        接收消息的socket
     * @param string $msg           发送的内容
     * @return void
     */
	function send($client, $msg){
		$msg = $this->frame($client, $msg);
		socket_write($client, $msg, strlen($msg));
    }
    
    /**
     * 将新连接放入连接列表
     * @param string $socket        新的socket
     * @return void
     */
	function connect($socket){
		array_push($this->sockets, $socket);
		$this->say("CONNECTED!", $socket);
    }
    
    /**
     * 断开连接
     * @param string $socket        要断开的socket
     * @return void
     */
	function disConnect($socket){
        try{
            $this->beforeDisconnect($socket);
        }catch(\Exception $e){
            $this->log($e->getMessage(), $socket);
        }
		// 移除socket
		$index = array_search($socket, $this->sockets);
		socket_close($socket);
		$this->say("DISCONNECTED!", $socket);
		if ($index !== false){
			array_splice($this->sockets, $index, 1); 
		}

		// 移除握手记录
		$index = array_search($socket, $this->handshakedSockets);
		if ($index !== false){
			array_splice($this->handshakedSockets, $index, 1); 
		}
    }
    
    /**
     * 实行websocket握手操作
     * @param string $socket        实行握手操作的socket
     * @param string $buffer        连接包含的数据; 用于解析出需要加密的数据
     * @return boolean 是否握手成功
     */
	function doHandShake($socket, $buffer){
		$this->log("Requesting handshake...", $socket);
		list($resource, $host, $origin, $key) = $this->getHeaders($buffer);
		$this->log("Handshaking...", $socket);
		$upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
					"Upgrade: websocket\r\n" .
					"Connection: Upgrade\r\n" .
					"Sec-WebSocket-Accept: " . $this->calcKey($key) . "\r\n\r\n";  //必须以两个回车结尾
		array_push($this->handshakedSockets, $socket);
		$sent = socket_write($socket, $upgrade, strlen($upgrade));
		$this->log("Done handshaking...", $socket);
		return $sent;
	}

    /**
     * 解析数据中的http头部数据
     * @param string $req     接收到的数据; 被解析的数据  
     * @return [请求头标志, 请求的主机, 请求的源, 加密之前的websocket握手key]
     */
	function getHeaders($req){
		$r = $h = $o = $key = null;
		if (preg_match("/GET (.*) HTTP/"              ,$req,$match)) { $r = $match[1]; }
		if (preg_match("/Host: (.*)\r\n/"             ,$req,$match)) { $h = $match[1]; }
		if (preg_match("/Origin: (.*)\r\n/"           ,$req,$match)) { $o = $match[1]; }
		if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/",$req,$match)) { $key = $match[1]; }
		return array($r, $h, $o, $key);
	}

    /**
     * 计算websocket握手key
     * @param string $key           需要被加密的key
     * @return string 加密后的key
     */
	function calcKey($key){
		//基于websocket version 13
		$accept = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
		return $accept;
    }
    
    /**
     * 解析消息体
     * @param string $buffer        通过websocket接收到的数据
     * @return string 解析后的文本数据
     */
	function decode($buffer) {
		$len = $masks = $data = $decoded = null;
		$len = ord($buffer[1]) & 127;

		if ($len === 126) {
			$masks = substr($buffer, 4, 4);
			$data = substr($buffer, 8);
		} 
		else if ($len === 127) {
			$masks = substr($buffer, 10, 4);
			$data = substr($buffer, 14);
		} 
		else {
			$masks = substr($buffer, 2, 4);
			$data = substr($buffer, 6);
		}
		for ($index = 0; $index < strlen($data); $index++) {
			$decoded .= $data[$index] ^ $masks[$index % 4];
		}

		// 去除非utf-8字符
		/* if($decoded){
			//先把正常的utf8替换成英文逗号
			$result = preg_replace('%(
				[\x09\x0A\x0D\x20-\x7E]
				| [\xC2-\xDF][\x80-\xBF]
				| \xE0[\xA0-\xBF][\x80-\xBF]
				| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}
				| \xED[\x80-\x9F][\x80-\xBF]
				| \xF0[\x90-\xBF][\x80-\xBF]{2}
				| [\xF1-\xF3][\x80-\xBF]{3}
				| \xF4[\x80-\x8F][\x80-\xBF]{2}
			)%xs',',',$decoded);
			//转成字符数字
			$charArr = explode(',', $result);
			//过滤空值、重复值以及重新索引排序
			$findArr = array_values(array_flip(array_flip(array_filter($charArr))));
			return $findArr ? str_replace($findArr, "", $decoded) : $decoded;
		} */

		return $decoded;
	}
    
    /**
     * 整理至websocket标准的消息体
     * @param string $buffer            要整理的数据
     * @return string 整理之后的websocket消息体
     */
	function frame($client, $msg){
		/* $a = str_split($msg, 125);
		if (count($a) == 1){
			return "\x81" . chr(strlen($a[0])) . $a[0];
		}
		$ns = "";
		foreach ($a as $o){
			$ns .= "\x81" . chr(strlen($o)) . $o;
		}
		return $ns; */
		$length = strlen($msg);
		if($length < 126){
			socket_write($client, "\x81" . chr($length), strlen($msg));
		}
		else if($length > 125 && $length < 65536){
			socket_write($client, 
				"\x81".
				chr(126).
				chr((($length>>8)&0xFF)).
				chr($length&0xFF),
			strlen($msg));
		}
		else if($length > 65535){
			socket_write($client, 
				"\x81".
				chr(127).
				chr(($length>>56) & 0xFF).
				chr(($length>>48) & 0xFF).
				chr(($length>>40) & 0xFF).
				chr(($length>>32) & 0xFF).
				chr(($length>>24) & 0xFF).
				chr(($length>>16) & 0xFF).
				chr(($length>>8) & 0xFF).
				chr($length & 0xFF),
			strlen($msg));
		}
		return $msg;
	}
    
    /**
     * debug信息输出
     *
     * @param string $msg   输出的内容
     * @return void
     */
	function log($msg='', $socket=null){
		if ($this->debug) $this->say($msg, $socket);
    }
    
    /**
     * 标准控制台输出
     * @param string $msg   输出的内容
     * @return void
     */
	function say($msg='', $socket=null){
		echo ((is_null($socket) ? '' : '['.$socket.'] ').date("Y-n-d H:i:s")." ".$msg."\n");
    }

    // 事件

    public function beforeHandshake($socket=null){}
    
    public function afterHandshake($socket=null, $result=false){}

    public function receiveMessage($socket=null, $msg=''){}

    public function beforeDisconnect($socket=null){}
    
}

// 启动方式
// php Server.php
//$server = new Server('localhost', 8081);

// js访问方式
/*
	var ws = new WebSocket("ws://localhost:8081");
	ws.onopen = function(){
		console.log("握手成功");
	}
	ws.onmessage = function(e){
		console.log("message:" + e.data);
	}
	ws.onerror = function(){
		console.log("error");
	}
*/
//ws.send(JSON.stringify({'target':0, 'message':'welcome!', data:{'name':'小明'}}));
