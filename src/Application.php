<?php
namespace webs_chat;


class Application
{
    public $ip = '127.0.0.1';
    private $port = 9503;
    private $server;
    private $clients = [];
    
    private static $instance = null;
    
    private function __construct()
    {
    }
    
    public static function Instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Application();
        }
        return self::$instance;
    }
    
    /**
     * @param string $ip
     * @param int $port
     */
    public function setIpPort($ip, $port)
    {
        $this->ip = $ip;
        $this->port = (int)trim($port);
    }
    
    /**
     * @return array [0=>ip, 1=>port]
     */
    public function getIpPort()
    {
        return [$this->ip, $this->port];
    }
    
    
    public function start()
    {
        $this->server = new \swoole_websocket_server($this->ip, $this->port);
    
        $this->server->on('open', [Application::Instance(), 'onOpen']);
    
        $this->server->on('message', [Application::Instance(), 'onMessage']);
    
        $this->server->on('close', [Application::Instance(), 'onClose']);
    
        $this->server->start();
    }
    
    public function onMessage(\swoole_websocket_server $server, $frame)
    {
        $input = json_decode($frame->data, true);
        
        if (!is_null($input)/* AND validation be here*/) {
            $input['text'] = $this->cleanse($input['text']);
            $input['from'] = $this->cleanse($input['from']);
            $input['to'] = $this->cleanse($input['to']);
            
            if ($input['type'] === Message::$types[0]) {
                //registration
                if (is_string($input['text']) AND strlen($input['text']) > 0) {
                    $mesText = 'You are registered as ' . trim($input['text']);
                    $message = new Message(Message::$types[1], 0, $frame->fd, $mesText);
                    $this->clients[$frame->fd]['name'] = trim($input['text']);
                } else {
                    $message = new Message(Message::$types[1], 0, $frame->fd, 'Recieved invalid username.');
                }
                $server->push($frame->fd, json_encode($message));
                
            } elseif ($input['type'] === Message::$types[3]) {
                //user to user
                $messageText = $input['text'];
                $toUser = $input['to'];
                $fromUser = $input['from'];

                if (strlen($messageText) > 0 AND isset($this->clients[$toUser]) AND isset($this->clients[$fromUser])) {
                    $message = new Message(
                        Message::$types[3],
                        $this->clients[$fromUser]['name'],
                        $this->clients[$toUser]['name'],
                        $messageText
                    );
        
                    $this->clients[$toUser]['messages'][] = $message;
                    $this->clients[$fromUser]['messages'][] = $message;
                }
                
            } elseif ($input['type'] === Message::$types[4]) {
                //error
                $server->close($frame->fd);
                
            } elseif ($input['type'] === Message::$types[5]) {
                //client requests userlist
                $userlist = $this->buildUsersList();
                $message = new Message(Message::$types[5], 0, $frame->fd, json_encode($userlist));
                $server->push($frame->fd, json_encode($message));
                
            } elseif ($input['type'] === Message::$types[6]) {
                //send to user his messages
                $fromUser = $input['from'];
                
                if (isset($this->clients[$fromUser])) {
                    $messages = $this->clients[$fromUser]['messages'];
                    
                    $message = new Message(Message::$types[6], 0, $frame->fd, json_encode($messages));
                    $server->push($frame->fd, json_encode($message));
                }
            }
            
        } else {
            throw new \Exception("Got unparseable message from client fd={$frame->fd}");
        }
        
    }
    
    public function onOpen(\swoole_websocket_server $server, $request)
    {
        echo "server: handshake success with fd{$request->fd}\n";
        $newClient = $this->addClient($request->fd, "fd{$request->fd}");
        $messageText = "Welcome to swoole websocket server, {$newClient['name']}!";
        $message = new Message(Message::$types[1], 0, $request->fd, $messageText);
        $server->push($request->fd, json_encode($message));
    }
    
    public function onClose($ser, $fd)
    {
        echo "client {$fd} closed\n";
        $this->removeClient($fd);
    }
    
    private function addClient($fd, $name)
    {
        $this->clients[$fd] = [
            'name' => $name,
            'fd' => $fd,
            'messages' => []
        ];
        return $this->clients[$fd];
    }
    
    private function removeClient($fd)
    {
        unset($this->clients[$fd]);
    }
    
    private function buildUsersList()
    {
        $userlist = [];
        foreach ($this->clients as $id => $client) {
            $userlist[] = ['id' => $id, 'name' => $client['name']];
        }
        
        return $userlist;
    }
    
    /**
     * Make text safe for use in html.
     * @param string $textFromUser
     * @return string
     */
    private function cleanse($textFromUser)
    {
        return htmlspecialchars(trim($textFromUser), ENT_QUOTES|ENT_HTML5);
    }
}