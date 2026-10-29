<?php
$modulos=require __DIR__.'/modulos.php';
[$titulo,$descripcion,$campos]=$modulos[$pagina];
$mostrarFormulario=$mostrarFormulario??false;
ob_start(); ?>
<p class="page-description"><?= htmlspecialchars($descripcion) ?></p>
<div class="notice">Interfaz en preparación. El guardado y las consultas se conectarán con los controladores.</div>
<?php if($mostrarFormulario || $pagina==='reportes'): ?>
<section class="panel form-panel"><div class="section-heading"><h2><?= $pagina==='reportes'?'Consultar reporte':'Nuevo registro' ?></h2><a href="index.php">← Volver</a></div>
<form class="preview-form"><div class="form-grid">
<?php foreach($campos as $campo): $select=in_array($campo,['Cliente','Vehículo','Mecánico','Especialidad','Orden','Estado','Método','Tipo de reporte']); $type=str_contains($campo,'Fecha')?'datetime-local':(in_array($campo,['Desde','Hasta'])?'date':(str_contains($campo,'Correo')?'email':((str_contains($campo,'Precio')||str_contains($campo,'Costo')||in_array($campo,['Monto','Kilometraje','Año de fabricación']))?'number':'text'))); ?>
<label><?= htmlspecialchars($campo) ?><?php if($select): ?><select required><option value="">Seleccionar</option><?php
$opciones=$campo==='Método'?['Efectivo','Tarjeta de crédito','Tarjeta de débito','Yape']:($campo==='Tipo de reporte'?['Servicios realizados','Vehículos atendidos','Reparaciones pendientes','Ingresos obtenidos']:($campo==='Estado'?($pagina==='citas'?['Programada','Confirmada','Atendida','Cancelada','No asistió']:['Recibida','En diagnóstico','En reparación','Terminada','Entregada','Cancelada']):[])); foreach($opciones as $opcion): ?><option><?= htmlspecialchars($opcion) ?></option><?php endforeach ?></select><?php elseif(in_array($campo,['Descripción','Observaciones','Motivo'])): ?><textarea rows="3" placeholder="Escribe aquí"></textarea><?php else: ?><input type="<?= $type ?>" <?= $type==='number'?'min="0" step="0.01"':'' ?> placeholder="<?= htmlspecialchars($campo) ?>" required><?php endif ?></label>
<?php endforeach ?></div><div class="form-actions"><a class="button secondary" href="index.php">Cancelar</a><button class="button" type="submit"><?= $pagina==='reportes'?'Consultar':'Guardar registro' ?></button></div></form></section>
<?php else: ?>
<section class="panel"><div class="toolbar"><div class="search-box"><span>⌕</span><input type="search" placeholder="Buscar en <?= htmlspecialchars(mb_strtolower($titulo)) ?>" aria-label="Buscar registros"></div><a class="button" href="form.php">+ Nuevo registro</a></div><?php require __DIR__.'/'.$pagina.'/lista.php'; ?></section>
<?php endif; $contenido=ob_get_clean(); require __DIR__.'/layout.php'; ?>