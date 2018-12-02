# websocket-chat
This is a websockets chat example on php and js. It's made for training purposes, it's not production-ready, nor will it ever be. And it's architecture leaves much to be desired. It shows how awesome thing is [websockets protocol](https://developer.mozilla.org/ru/docs/WebSockets)... even on php!

How to install:
1. Swoole installs as php extension, see the link: https://www.swoole.co.uk/docs/get-started/installation
2. Chat server launches as cli application: 'php websocket-server.php'. Chat.html, css and main.js must be placed somewhere in www/htdocs of apache/nginx/whatever webserver you use. 
3. Run websocket-server.php, use your browser to navigate to chat.html and have fun!
4. php file actually runs behind another ip address (127.0.0.1:9503 by default, but can be changed in Application.php), so you need to configure webserver (apache in my case) to proxy every ws request from this IP to IP of your webserver.
Part of my apache virtualhost for example:

```
  ProxyPreserveHost On
	RewriteEngine On
	RewriteCond %{HTTP:Upgrade} =websocket [NC]
	RewriteRule /(.*) ws://127.0.0.1:9503 [P,L]
```

Example of chat.html window:

![example of chat.html window](https://i.imgur.com/C0PnYsh.png)

Reference:

https://www.swoole.co.uk/docs/

https://github.com/swoole/swoole-docs/

https://learn.javascript.ru/websockets

https://developer.mozilla.org/ru/docs/WebSockets
