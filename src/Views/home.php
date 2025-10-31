<?php $this->layout('layout', ['page' => 'home'])?>
<div>
    <h2>Home page</h2>
    <form id="message-form">
        <div>
            <input id="message-box" type="text" placeholder="Put the message here" />
        </div>
        <input type="submit" value="Send" />
    </form>
</div>

<div>
    <ul id="output"></ul>
</div>

<script>
    (function() {
        let app = {
            ws: null,

            config: {
                // url: 'ws://127.0.0.1',
                url: 'ws://localhost',
                port: 8004
            },

            init: function() {
                app.connectToServer();
                app.listenEvents();
            },

            connectToServer: function() {
                let wsServer = app.config.url + ':' + app.config.port;
                try {
                    app.ws = new WebSocket(wsServer);

                    app.ws.onopen = function(evt) {
                        console.log('Connected to ' + wsServer);
                    }
                    app.ws.onclose = function(evt) {
                        console.log('Disconnected from ' + wsServer);

                    }
                    app.ws.onmessage = function(evt) {
                        console.log('Received message: ' + evt.data);
                        app.handleMessage(evt.data);
                    }
                    app.ws.onerror = function(e) {
                        console.log('Error: ', e);
                    }
                } catch(error) {
                    console.log('Failed to connect to WS: ', error);
                }
            },

            listenEvents: function() {
                document.getElementById('message-form').addEventListener('submit', app.handleSubmit, false)
            },

            handleSubmit: function(e) {
                e.preventDefault();
                app.ws.send(document.getElementById('message-box').value);
            },

            handleMessage: function(data) {
                let input = document.createElement('li');
                input.innerHTML = data;
                document.getElementById('output').appendChild(input);
                console.log('message handled');
            }
        }
        app.init();
    })();
</script>
