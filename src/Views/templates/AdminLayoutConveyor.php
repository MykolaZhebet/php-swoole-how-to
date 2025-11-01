<html>
<head>
    <title><?= $page ?? 'Admin page'?></title>
    <link rel="icon" href="data:," /> <!-- empty favicon -->
</head>
<body>
<h1>WebSocket Admin layout Conveyor: <?=$this->e($page)?></h1>
<div>
    <a href="/admin">Admin</a>
</div>
<?=$this->section('content')?>
<?php if (!is_null($ws_context)) {?>
    <?php $this->insert('partials/chatConveyor') ?>
    <script>
        window.WS_PORT = <?= $ws_context['port']?>;
        window.WS_TOKEN = '<?= $ws_context['token']?>';
    </script>
<?php } ?>
</body>
</html>