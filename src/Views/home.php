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
