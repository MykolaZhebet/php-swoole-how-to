<html>
<head>
    <title><?= $page ?? 'Admin page'?></title>
    <link rel="icon" href="data:," /> <!-- empty favicon -->
</head>
<body>
<h1>WebSocket Admin layout: <?=$this->e($page)?></h1>
<?=$this->section('content')?>
<?php if (!is_null($ws_context)) {?>
    <?php $this->insert('partials/chat') ?>
    <script>
        window.WS_PORT = <?= $ws_context['port']?>;
        window.WS_TOKEN = '<?= $ws_context['token']?>';
    </script>
    <script src="js/ws.js"></script>
<?php } ?>
</body>
</html>