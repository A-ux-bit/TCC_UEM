<?php

require 'includes/app.php';

$u=need();$pdo=db();$E=$u['emp'];$edit=$u['tipo']==='empresario';

$q=$pdo->prepare('SELECT * FROM status_tarefas WHERE id_empresa=? ORDER BY ordem');$q->execute([$E]);$cols=$q->fetchAll();

$q=$pdo->prepare('SELECT t.*,u.nome FROM tarefas t JOIN usuarios u ON u.id_usuario=t.id_usuario WHERE t.id_empresa=? ORDER BY t.data_criacao');$q->execute([$E]);

$by=[];foreach($q as $t)$by[$t['id_status']][]=$t;

$q=$pdo->prepare("SELECT (SELECT COUNT(*) FROM tarefas t JOIN status_tarefas s ON s.id_status=t.id_status WHERE t.id_empresa=:e AND s.tipo='andamento') wip,
 (SELECT COUNT(*) FROM tarefas WHERE id_empresa=:e AND data_conclusao>=NOW()-INTERVAL 7 DAY) c7,(SELECT AVG(lead_time) FROM tarefas WHERE id_empresa=:e) lt");
$q->execute([':e'=>$E]);$m=$q->fetch();$thr=$m['c7']/7;$est=$thr>0?$m['wip']/$thr*86400:null;

$P=['baixa'=>'Baixa','media'=>'Média','alta'=>'Alta'];

head('Kanban');

?>

<main class="container py-4">

<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Quadro Kanban</h1>

<?php
 if($edit):
 ?>

<button class="btn btn-primary" onclick="abrir()">Nova tarefa</button><?php else:?><span class="text-muted small">Somente leitura</span><?php endif?></div>

<div class="row g-3 mb-4"><?php foreach([['WIP atual',$m['wip']],['Throughput (7 dias)',number_format($thr,2,',','.').' tarefas/dia'],['Lead Time médio',dur($m['lt'])],['Lead Time estimado (WIP ÷ Throughput)',dur($est)]] as [$k,$v]):?>

<div class="col-6 col-lg-3"><div class="card p-3 h-100"><small class="text-muted"><?=$k?></small><div class="fs-5 fw-bold"><?=e($v)?></div></div></div><?php endforeach?></div>

<div class="board"><?php foreach($cols as $c):$ts=$by[$c['id_status']]??[];$n=count($ts);$full=$c['limite_wip']!==null&&$n>=$c['limite_wip'];?>

<div class="col-k"><div class="d-flex justify-content-between align-items-center mb-2"><strong><?=e($c['nome_status'])?></strong>

<span><span class="badge 
<?=$full?'text-bg-danger':'text-bg-secondary'?>"><?=$n?><?=$c['limite_wip']!==null?'/'.$c['limite_wip']:''?></span>

<?php if($edit):
?>

<button class="btn btn-sm btn-link p-0" title="Definir limite de WIP" onclick="wip(<?=$c['id_status']?>,<?=(int)$c['limite_wip']?>)">⚙</button><?php endif?></span></div>

<div class="col-body" data-col="<?=$c['id_status']?>">
    <?php
     foreach($ts as $t):
     ?>

<div class="card task mb-2 p-2" <?=$edit?'draggable="true"':''?> data-id="<?=$t['id_tarefa']?>">

<div class="d-flex justify-content-between"><span class="badge prio-<?=$t['prioridade']?>"><?=$P[$t['prioridade']]?></span>

<?php

if($edit&&$t['id_usuario']==$u['id']):
?>

<span><button class="btn btn-sm p-0" data-id="<?=$t['id_tarefa']?>" data-t="<?=e($t['titulo'])?>" data-d="<?=e($t['descricao'])?>" data-p="<?=$t['prioridade']?>" onclick="abrir(this)" title="Editar">✎</button>

<button class="btn btn-sm p-0" onclick="excluir(<?=$t['id_tarefa']?>)" title="Excluir">✕</button></span><?php endif?></div>

<div class="fw-semibold mt-1"><?=e($t['titulo'])?></div><small class="text-muted"><?=e($t['descricao'])?></small>

<div class="d-flex justify-content-between small text-muted mt-1"><span><?=e($t['nome'])?></span><span><?=$t['lead_time']!==null?'⏱ '.dur($t['lead_time']):'criada em '.date('d/m',strtotime($t['data_criacao']))?></span></div></div>

<?php
 
endforeach
 
?></div></div><?php endforeach?></div></main>

<?php

if($edit):
?>

<div class="modal fade" id="mt" tabindex="-1"><div class="modal-dialog"><form id="ft" class="modal-content"><input type="hidden" name="tid">

<div class="modal-header"><h5 class="modal-title">Tarefa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

<div class="modal-body vstack gap-3"><input name="titulo" class="form-control" placeholder="Título" required maxlength="150"><textarea name="descricao" class="form-control" rows="3" placeholder="Descrição"></textarea>

<select name="prioridade" class="form-select"><option value="baixa">Baixa</option><option value="media" selected>Média</option><option value="alta">Alta</option></select></div>

<div class="modal-footer"><button class="btn btn-primary">Salvar tarefa</button></div></form></div></div><?php endif?>

<?php
 
 foot($edit?'kanban.js':'')
 
?>
