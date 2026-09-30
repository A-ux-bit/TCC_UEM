<?php 
require 'includes/app.php';
$erro=$ok=$devlink='';$aba=['cadastrar'=>'cadastrar','esqueci'=>'esqueci'][$_GET['aba']??'']??'entrar';
$token=$_GET['token']??$_POST['token']??'';if(isset($_GET['token']))$aba='redefinir';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $pdo=db();$email=trim($_POST['email']??'');$s=$_POST['senha']??'';
 $ac=$_POST['acao']??'';
 if($ac==='esqueci'){
  $aba='esqueci';$q=$pdo->prepare("SELECT id_usuario FROM usuarios WHERE email=? AND status='ativo'");$q->execute([$email]);$id=$q->fetchColumn();
  if($id){
   $tk=bin2hex(random_bytes(32));
   $pdo->prepare('INSERT INTO redefinicoes_senha(id_usuario,token_hash,expira_em) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 1 HOUR))')->execute([$id,hash('sha256',$tk)]);
   $link=(isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].strtok($_SERVER['REQUEST_URI'],'?').'?token='.$tk;
   @mail($email,'Redefinição de senha - NexaFlow',"Para criar uma nova senha, acesse (válido por 1 hora):\n$link","Content-Type: text/plain; charset=UTF-8");
   if(MODO_DEV)$devlink=$link;
  }
  $ok='Se o e-mail estiver cadastrado, enviamos um link para redefinir a senha. Ele vale por 1 hora.';
 }elseif($ac==='redefinir'){
  $aba='redefinir';$q=$pdo->prepare('SELECT id_usuario FROM redefinicoes_senha WHERE token_hash=? AND usado=0 AND expira_em>NOW()');$q->execute([hash('sha256',$token)]);$id=$q->fetchColumn();
  if(!$id)$erro='Link inválido ou expirado. Peça um novo em "Esqueceu a senha?".';
  elseif(strlen($s)<8||$s!==($_POST['senha2']??''))$erro='A senha precisa ter 8 ou mais caracteres e ser igual à confirmação.';
  else{
   $pdo->prepare('UPDATE usuarios SET senha_hash=? WHERE id_usuario=?')->execute([password_hash($s,PASSWORD_DEFAULT),$id]);
   $pdo->prepare('UPDATE redefinicoes_senha SET usado=1 WHERE id_usuario=?')->execute([$id]);
   $ok='Senha alterada. Entre com a nova senha.';$aba='entrar';
  }
 }elseif($ac==='entrar'){
  $q=$pdo->prepare("SELECT * FROM usuarios WHERE email=? AND status='ativo'");$q->execute([$email]);$r=$q->fetch();
  if($r&&password_verify($s,$r['senha_hash'])){
   session_regenerate_id(true);
   $_SESSION['u']=['id'=>(int)$r['id_usuario'],'nome'=>$r['nome'],'tipo'=>$r['tipo_usuario'],'emp'=>(int)$r['id_empresa']];
   $pdo->prepare('UPDATE usuarios SET ultimo_acesso=NOW() WHERE id_usuario=?')->execute([$r['id_usuario']]);
   header('Location: '.($r['tipo_usuario']==='admin'?'admin.php':'kanban.php'));exit;
  }
  $erro='E-mail ou senha incorretos.';
 }else{
  $aba='cadastrar';$nome=trim($_POST['nome']??'');$emp=trim($_POST['empresa']??'');$tp=$_POST['perfil']??'';
  if(!$nome||!$emp||!isset(PERFIL[$tp])||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($s)<8||$s!==($_POST['senha2']??''))
   $erro='Preencha todos os campos. A senha precisa ter 8 ou mais caracteres e ser igual à confirmação.';
  else{
   $q=$pdo->prepare('SELECT 1 FROM usuarios WHERE email=?');$q->execute([$email]);
   $c=$pdo->prepare('SELECT id_empresa FROM empresas WHERE nome_empresa=?');$c->execute([$emp]);$id=$c->fetchColumn();
   $adm=$id&&$pdo->query("SELECT 1 FROM usuarios WHERE tipo_usuario='admin' AND id_empresa=".(int)$id)->fetch();
   if($q->fetch())$erro='Este e-mail já está cadastrado.';
   elseif($adm&&$tp==='admin')$erro='Esta empresa já tem administrador. Peça a ele para criar seu acesso.';
   else{
    $pdo->beginTransaction();
    if(!$id){
     $pdo->prepare('INSERT INTO empresas(nome_empresa) VALUES(?)')->execute([$emp]);$id=$pdo->lastInsertId();
     foreach([['A Fazer',1,null,'inicio'],['Em Andamento',2,3,'andamento'],['Concluído',3,null,'fim']] as $k)
      $pdo->prepare('INSERT INTO status_tarefas(id_empresa,nome_status,ordem,limite_wip,tipo) VALUES(?,?,?,?,?)')->execute([$id,...$k]);
    }
    $pdo->prepare('INSERT INTO usuarios(id_empresa,nome,email,senha_hash,tipo_usuario) VALUES(?,?,?,?,?)')->execute([$id,$nome,$email,password_hash($s,PASSWORD_DEFAULT),$tp]);
    $pdo->commit();$ok='Conta criada. Entre para continuar.';$aba='entrar';
   }
  }
 }
}
head('Acesso');$on=fn($a)=>$aba===$a?'active':'';$sh=fn($a)=>$aba===$a?'show active':'';?>
<div class="row g-0" style="min-height:calc(100vh - 57px)">
<div class="col-lg-5 side d-none d-lg-flex flex-column justify-content-center p-5"><h2 class="fw-bold">Bem-vindo à NexaFlow</h2>
<p>Gestão visual de tarefas com Kanban, Lead Time e limite de WIP.</p><ul><li>Drag and drop entre colunas</li><li>Lead Time automático</li><li>Relatórios para a gestão</li></ul></div>
<div class="col-lg-7 d-flex align-items-center justify-content-center p-4"><div style="max-width:420px;width:100%">

<?php 
if($erro):
?>

<div class="alert alert-danger"><?
=e($erro)
?>

</div>

<?php
 endif; if($ok):
 ?>

 <div class="alert alert-success">

<?=
  e($ok)
  ?>
</div>

<?php

endif; if($devlink):

?>

<div class="alert alert-warning">Modo de teste (e-mail não enviado): <a href="<?=e($devlink)?>">abrir link de redefinição</a></div>

<?php

endif

?>

<?php 
if($aba==='esqueci'):
?>

<h2 class="h5">Esqueceu a senha?</h2><p class="text-secondary">Informe seu e-mail e enviaremos um link para criar uma nova senha.</p>

<form method="post" class="vstack gap-3"><input type="hidden" name="acao" value="esqueci"><input name="email" type="email" class="form-control" placeholder="E-mail" required><button class="btn btn-primary">Enviar link</button><a href="login.php" class="small">Voltar ao login</a></form>

<?php
 elseif($aba==='redefinir'):

?>

 
<h2 class="h5">Criar nova senha</h2>

<form method="post" class="vstack gap-3"><input type="hidden" name="acao" value="redefinir"><input type="hidden" name="token" value="<?=e($token)?>"><input name="senha" type="password" class="form-control" placeholder="Nova senha (mínimo 8 caracteres)" required>

<input name="senha2" type="password" class="form-control" placeholder="Confirmar nova senha" required><button class="btn btn-primary">Salvar nova senha</button><a href="login.php" class="small">Voltar ao login</a></form>

<?php 

 else:

?>
<ul class="nav nav-pills nav-fill mb-3"><li class="nav-item"><button class="nav-link <?=$on('entrar')?>" data-bs-toggle="pill" data-bs-target="#e">Entrar</button></li>

<li class="nav-item"><button class="nav-link <?=$on('cadastrar')?>" data-bs-toggle="pill" data-bs-target="#c">Cadastrar</button></li></ul>

<div class="tab-content">

<form id="e" method="post" class="tab-pane fade <?=$sh('entrar')?> vstack gap-3"><input type="hidden" name="acao" value="entrar">

<input name="email" type="email" class="form-control" placeholder="E-mail" required><input name="senha" type="password" class="form-control" placeholder="Senha" required>

<button class="btn btn-primary">Entrar na plataforma</button><a href="login.php?aba=esqueci" class="login-forgot d-block">Esqueceu a senha?</a></form>

<form id="c" method="post" class="tab-pane fade <?=$sh('cadastrar')?> vstack gap-3"><input type="hidden" name="acao" value="cadastrar">

<input name="nome" class="form-control" placeholder="Nome completo" required><input name="empresa" class="form-control" placeholder="Nome da empresa" required>

<select name="perfil" class="form-select">

<?php

foreach(PERFIL as $k=>$v):?><option value="<?=$k?>" <?=$k==='empresario'?'selected':''?>><?=$v?></option><?php 

  endforeach
?>
</select>
<input name="email" type="email" class="form-control" placeholder="E-mail" required><input name="senha" type="password" class="form-control" placeholder="Senha (mínimo 8 caracteres)" required>
<input name="senha2" type="password" class="form-control" placeholder="Confirmar senha" required><button class="btn btn-primary">Criar minha conta</button></form>
</div>
<?php 
endif
?>
</div></div></div>
<?php 
foot()
?>
