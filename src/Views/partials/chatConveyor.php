<style>
    #chat-container {
        position: fixed;
        right: 1px;
        bottom: 1px;
        border: 1px solid #ccc;
        padding: 10px;
    }
    #output-strong {
        margin-right: 10px
    }
</style>

<div id="chat-container">
    <div>
        <form id="message-form">
            <div>
                <input id="message-box" type="text" placeholder="Put the message here" />
            </div>
            <input type="submit" value="Send" />
        </form>
    </div>
</div>
<hr/>

<div style="">
    <div>Connection list:</div>
    <div id="connections-list"></div>

</div>
<div>
    <ul id="output">
<!--  Will be populated by JavaScript  -->
    </ul>
</div>

<script src="js/app.js"></script>
<script>
    (function() {
        window.app.init({
                port: 8004,
                channel: 'sample-channel',
            },
            document.getElementById('message-form'),
            document.getElementById('message-box'),
            document.getElementById('output'),
            document.getElementById('connections-list'),
        );
    })();
</script>