<?php 
   require 'includes/app.php';
   $u=need(['admin']);$pdo=db();$E=$u['emp'];$msg='';
   if($_SERVER['REQUEST_METHOD']==='POST'){
   $a=$_POST['acao']??'';$id=(int)($_POST['uid']??0);$n=trim($_POST['nome']??'');$em=trim($_POST['email']??'');$s=$_POST['senha']??'';$tp=$_POST['tipo']??'';$st=($_POST['status']??'')==='inativo'?'inativo':'ativo';
   try{
     if($a==='excluir'&&$id!==$u['id'])$pdo->prepare('DELETE FROM usuarios WHERE id_usuario=? AND id_empresa=?')->execute([$id,$E]);
     elseif($a==='salvar'&&$n&&isset(PERFIL[$tp])){
      if($id){
              if($id===$u['id']){$tp='admin';$st='ativo';}
              $pdo->prepare('UPDATE usuarios SET nome=?,tipo_usuario=?,status=? WHERE id_usuario=? AND id_empresa=?')->execute([$n,$tp,$st,$id,$E]);
              if(strlen($s)>=8)$pdo->prepare('UPDATE usuarios SET senha_hash=? WHERE id_usuario=? AND id_empresa=?')->execute([password_hash($s,PASSWORD_DEFAULT),$id,$E]);
            }elseif(filter_var($em,FILTER_VALIDATE_EMAIL)&&strlen($s)>=8)
            $pdo->prepare('INSERT INTO usuarios(id_empresa,nome,email,senha_hash,tipo_usuario,status) VALUES(?,?,?,?,?,?)')->execute([$E,$n,$em,password_hash($s,PASSWORD_DEFAULT),$tp,$st]);
             else $msg='Informe um e-mail válido e uma senha com 8 ou mais caracteres.';
    }
 }catch(PDOException $x){$msg='Não foi possível concluir: e-mail já usado ou usuário com tarefas (inative-o em vez de excluir).';}
}
$q=$pdo->prepare('SELECT * FROM usuarios WHERE id_empresa=? ORDER BY nome');$q->execute([$E]);$us=$q->fetchAll();
$q=$pdo->prepare('SELECT h.data_movimentacao dt,t.titulo,u.nome,so.nome_status o,sd.nome_status d FROM historico_movimentacao h JOIN tarefas t ON t.id_tarefa=h.id_tarefa LEFT JOIN usuarios u ON u.id_usuario=h.id_usuario LEFT JOIN status_tarefas so ON so.id_status=h.status_origem JOIN status_tarefas sd ON sd.id_status=h.status_destino WHERE t.id_empresa=? ORDER BY h.id_movimentacao DESC LIMIT 50');$q->execute([$E]);$mv=$q->fetchAll();
head('Usuários');?>
<main class="container py-4"><div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Administração</h1><button class="btn btn-primary" onclick="ed()">Novo usuário</button></div>

<?php 

if($msg):

?>

<div class="alert alert-danger"><?=e($msg)?></div><?php endif?>


<ul class="nav nav-tabs mb-3"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tu">Usuários</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tm">Movimentações</button></li></ul>

<div class="tab-content"><div class="tab-pane fade show active table-responsive" id="tu"><table class="table align-middle bg-white"><thead><tr><th>Usuário</th><th>Perfil</th><th>Status</th><th>Último acesso</th><th></th></tr></thead><tbody>

<?php
 foreach($us as $r):
 ?>
 
<tr><td><span class="avatar me-2"><?=e(mb_strtoupper(mb_substr($r['nome'],0,2)))?></span><?=e($r['nome'])?><br><small class="text-muted ms-5"><?=e($r['email'])?></small></td>

<td><?=PERFIL[$r['tipo_usuario']]?></td><td><span class="badge <?=$r['status']==='ativo'?'text-bg-success':'text-bg-secondary'?>"><?=$r['status']?></span></td>

<td><?=$r['ultimo_acesso']?date('d/m/Y H:i',strtotime($r['ultimo_acesso'])):'—'?></td>

<td class="text-nowrap"><button class="btn btn-sm btn-outline-primary" data-id="<?=$r['id_usuario']?>" data-n="<?=e($r['nome'])?>" data-t="<?=$r['tipo_usuario']?>" data-s="<?=$r['status']?>" onclick="ed(this)">Editar</button>



<?php 

if($r['id_usuario']!=$u['id']):
  
?>
  <form method="post" class="d-inline" onsubmit="return confirm('Excluir este usuário?')"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="uid" value="<?=$r['id_usuario']?>"><button class="btn btn-sm btn-outline-danger">Excluir</button></form><?php endif?></td></tr>

  <?php endforeach
  
  ?>
  </tbody></table></div>

<div class="tab-pane fade table-responsive" id="tm"><table class="table table-sm bg-white"><thead><tr><th>Data</th><th>Tarefa</th><th>Responsável</th><th>De</th><th>Para</th></tr></thead><tbody>

<?php 
     foreach($mv as $r):?><tr><td><?=date('d/m/Y H:i',strtotime($r['dt']))?></td><td><?=e($r['titulo'])?></td><td><?=e($r['nome'])?></td><td><?=e($r['o']??'—')?></td><td><?=e($r['d'])?></td></tr><?php endforeach?></tbody></table></div></div></main>

<div class="modal fade" id="mu" tabindex="-1"><div class="modal-dialog"><form id="fu" method="post" class="modal-content"><input type="hidden" name="acao" value="salvar"><input type="hidden" name="uid">

<div class="modal-header"><h5 class="modal-title">Usuário</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>

<div class="modal-body vstack gap-3"><input name="nome" class="form-control" placeholder="Nome" required><input name="email" type="email" class="form-control" placeholder="E-mail">

<input name="senha" type="password" class="form-control" placeholder="Senha (mínimo 8; em branco mantém a atual)">

<select name="tipo" class="form-select"><?php foreach(PERFIL as $k=>$v):?><option value="<?=$k?>"><?=$v?></option><?php endforeach?></select>

<select name="status" class="form-select"><option value="ativo">Ativo</option><option value="inativo">Inativo</option></select></div>

<div class="modal-footer"><button class="btn btn-primary">Salvar usuário</button></div></form></div></div>

<script>function ed(b){const f=document.getElementById('fu'),d=b?b.dataset:{};f.uid.value=d.id||'';f.nome.value=d.n||'';f.tipo.value=d.t||'empresario';f.status.value=d.s||'ativo';f.email.value='';f.email.disabled=!!b;f.senha.value='';bootstrap.Modal.getOrCreateInstance('#mu').show()}</script>

<?php foot()?>
