<?php require_once __DIR__.'/../lib/layout.php'; require_role('abogado'); shell_top('Abogado'); ?>
<h1>Panel del abogado</h1>
<p>Bienvenido, <?=h(current_user()['name'])?>.</p>
<p class="soon">Próximo (CRUD): asuntos asignados, seguimiento y documentos por caso.</p>
<?php shell_bottom();
