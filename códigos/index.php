<?php 

require 'includes/app.php';head('Início');

?>

<header class="hero py-5"><div class="container py-4"><div class="row align-items-center g-4">

<div class="col-lg-6"><h1 class="display-5 fw-bold">Veja o trabalho da sua startup fluir</h1>

<p class="lead text-secondary">Kanban simples, com Lead Time calculado sozinho e limite de WIP por coluna, para a equipe focar no que importa.</p>

<a href="login.php?aba=cadastrar" class="btn btn-primary btn-lg me-2">Criar conta</a><a href="login.php" class="btn btn-outline-primary btn-lg">Entrar</a></div>

<div class="col-lg-6"><div class="card p-3 shadow-sm"><div class="row g-2 text-center small">

<div class="col-4"><div class="fw-semibold mb-2">Características</div><div class="card p-2 mb-1">Kanban</div><div class="card p-2">Limite de WIP</div></div>

<div class="col-4"><div class="fw-semibold mb-2">Funções</div><div class="card p-2 mb-1">Tela de login</div><div class="card p-2">CRUD de tarefas</div></div>

<div class="col-4"><div class="fw-semibold mb-2">Relatórios</div><div class="card p-2 mb-1">Tarefas concluídas</div><div class="card p-2">Lead Time médio</div></div></div></div></div></div></div></header>

<section class="container py-5"><div class="row g-4">

<?php
foreach([['Quadro Kanban','Arraste tarefas entre as colunas e veja o andamento de todo o time.'],['Lead Time automático','O tempo entre a criação e a entrega de cada tarefa é registrado sem preencher nada.'],['Limite de WIP','Cada coluna aceita um máximo de tarefas. Ao atingir o limite, novas entradas são bloqueadas.'],['Relatórios','Tarefas concluídas, Lead Time médio e desempenho por pessoa, para a gestão.']] as [$t,$d]):?>

<div class="col-md-6 col-lg-3"><h2 class="h5"><?=$t?></h2><p class="text-secondary"><?=$d?></p></div><?php endforeach?></div>

<p class="mt-3 mb-0 p-3 bg-white rounded border">Lei de Little: <strong>Lead Time = WIP ÷ Throughput</strong>. Com menos tarefas em andamento, o tempo de entrega diminui.</p></section>

<section class="bg-white border-top py-5"><div class="container"><h2 class="h4 mb-4">Quem faz a NexaFlow</h2><div class="row g-4">

<?php
 foreach([['Alex Hideki Horie','Desenvolvimento'],['Gislaine Camila Lapasini Leal','Orientação'],['Carlos Danilo Luz','Coorientação']] as [$n,$c]):?>

<div class="col-md-4 d-flex gap-3 align-items-center"><span class="avatar"><?=e(mb_substr($n,0,2))?></span><div><strong><?=e($n)?></strong><br><small class="text-muted"><?=$c?></small></div></div><?php endforeach?></div></div></section>

<?php
 foot()
 ?>
