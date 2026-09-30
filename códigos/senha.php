<?php
 require 'includes/app.php';
$u=need();$erro=$ok='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $pdo=db();$q=$pdo->prepare('SELECT senha_hash FROM usuarios WHERE id_usuario=?');$q->execute([$u['id']]);$h=$q->fetchColumn();$n=$_POST['nova']??'';
 if(!password_verify($_POST['atual']??'',$h))$erro='A senha atual está incorreta.';
 elseif(strlen($n)<8||$n!==($_POST['nova2']??''))$erro='A nova senha precisa ter 8 ou mais caracteres e ser igual à confirmação.';
 else{$pdo->prepare('UPDATE usuarios SET senha_hash=? WHERE id_usuario=?')->execute([password_hash($n,PASSWORD_DEFAULT),$u['id']]);$ok='Senha alterada com sucesso.';}
}
head('Alterar senha');?>

<main class="container py-4" style="max-width:440px"><h1 class="h3 mb-3">Alterar senha</h1>

<?php 
 if($erro):
 ?>
 
<div class="alert alert-danger">
    <?=e($erro)
    ?>
    </div>
    <?php 
     endif; if($ok):
     ?>
    <div class="alert alert-success">
    <?
    =e($ok)
    ?>
    </div>
    <?php 
    endif
    ?>

<form method="post" class="vstack gap-3"><input name="atual" type="password" class="form-control" placeholder="Senha atual" required>

<input name="nova" type="password" class="form-control" placeholder="Nova senha (mínimo 8 caracteres)" required>

<input name="nova2" type="password" class="form-control" placeholder="Confirmar nova senha" required><button class="btn btn-primary">Salvar nova senha</button></form></main>
<?php 
 foot()
?>
