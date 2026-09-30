<?php
 require 'includes/app.php';
$u=need(['admin','patrao']);$pdo=db();$E=$u['emp'];
$de=$_GET['de']??date('Y-m-d',strtotime('-30 days'));$ate=$_GET['ate']??date('Y-m-d');$p=[$E,"$de 00:00:00","$ate 23:59:59"];
$q=$pdo->prepare('SELECT u.nome,COUNT(*) n,AVG(t.lead_time) lt FROM tarefas t JOIN usuarios u ON u.id_usuario=t.id_usuario WHERE t.id_empresa=? AND t.data_conclusao BETWEEN ? AND ? GROUP BY u.id_usuario,u.nome ORDER BY n DESC');$q->execute($p);$por=$q->fetchAll();
$q=$pdo->prepare('SELECT t.titulo,t.data_conclusao,t.lead_time,u.nome FROM tarefas t JOIN usuarios u ON u.id_usuario=t.id_usuario WHERE t.id_empresa=? AND t.data_conclusao BETWEEN ? AND ? ORDER BY t.data_conclusao DESC');$q->execute($p);$lst=$q->fetchAll();
$q=$pdo->prepare("SELECT COUNT(*) FROM tarefas t JOIN status_tarefas s ON s.id_status=t.id_status WHERE t.id_empresa=? AND s.tipo='andamento'");$q->execute([$E]);$wip=$q->fetchColumn();
$tot=count($lst);$avg=$tot?array_sum(array_column($lst,'lead_time'))/$tot:null;$thr=$tot/max(1,(strtotime($ate)-strtotime($de))/86400+1);
head('Relatórios');?>

<main class="container py-4"><div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3"><h1 class="h3 mb-0">Relatório de desempenho</h1>

<form class="d-flex gap-2 align-items-end no-print"><input type="date" name="de" value="<?=e($de)?>" class="form-control"><input type="date" name="ate" value="<?=e($ate)?>" class="form-control">

<button class="btn btn-primary">Filtrar</button><button type="button" class="btn btn-outline-secondary" onclick="print()">Imprimir</button></form></div>

<div class="row g-3 mb-4">
    <?php 
     foreach([['Tarefas concluídas',$tot],['Lead Time médio',dur($avg)],['Throughput',number_format($thr,2,',','.').' tarefas/dia'],['WIP atual',$wip]] as [$k,$v]):
     ?>

    <div class="col-6 col-lg-3"><div class="card p-3 h-100"><small class="text-muted"><?=$k?></small><div class="fs-5 fw-bold"><?=e($v)?></div></div></div><?php 
    endforeach
    ?></div>

    <div class="row g-4"><div class="col-lg-5"><h2 class="h5">Por pessoa</h2><table class="table table-sm bg-white"><thead><tr><th>Nome</th><th>Concluídas</th><th>Lead Time médio</th></tr></thead><tbody>
<?php
 foreach($por as $r):?><tr><td><?=e($r['nome'])?></td><td><?=$r['n']?></td><td><?=dur($r['lt'])?></td></tr><?php endforeach; if(!$por):?><tr><td colspan="3" class="text-muted">Nenhuma tarefa concluída neste período.</td></tr><?php endif?></tbody></table></div>
<div class="col-lg-7"><h2 class="h5">Tarefas concluídas</h2><div class="table-responsive"><table class="table table-sm bg-white"><thead><tr><th>Tarefa</th><th>Responsável</th><th>Concluída em</th><th>Lead Time</th></tr></thead><tbody>
<?php
 foreach($lst as $r):?><tr><td><?=e($r['titulo'])?></td><td><?=e($r['nome'])?></td><td><?=date('d/m/Y H:i',strtotime($r['data_conclusao']))?></td><td><?=dur($r['lead_time'])?></td></tr><?php endforeach?></tbody></table></div></div></div></main>

<?php
 foot()
 ?>
