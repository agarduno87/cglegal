<?php require_once __DIR__.'/../lib/layout.php'; require_role('cliente'); shell_top('Cliente'); ?>
<h1>Mi portal</h1>
<p>Bienvenido, <?=h(current_user()['name'])?>.</p>
<p class="soon">Próximo (CRUD): estado de tu asunto, mensajes y documentos compartidos.</p>
<?php shell_bottom();
