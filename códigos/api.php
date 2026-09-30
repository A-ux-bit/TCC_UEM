<?php 

require 'includes/app.php';

header('Content-Type: application/json; charset=utf-8');

$u=need(['empresario']);$pdo=db();$E=$u['emp'];

$in=json_decode(file_get_contents('php://input'),true)?:[];$a=$in['acao']??'';

function out($ok,$msg=''){echo json_encode(['ok'=>$ok,'msg'=>$msg]);exit;}

function hist($p,$t,$u,$o,$d){$p->prepare('INSERT INTO historico_movimentacao(id_tarefa,id_usuario,status_origem,status_destino) VALUES(?,?,?,?)')->execute([$t,$u,$o,$d]);}

$pri=in_array($in['prioridade']??'',['baixa','media','alta'])?$in['prioridade']:'media';

$tit=trim($in['titulo']??'');$desc=trim($in['descricao']??'');

try{switch($a){
 case 'criar':
  if(!$tit)out(false,'Informe o título da tarefa.');
  $s=$pdo->prepare('SELECT id_status FROM status_tarefas WHERE id_empresa=? ORDER BY ordem LIMIT 1');$s->execute([$E]);$st=$s->fetchColumn();
  $pdo->prepare('INSERT INTO tarefas(id_empresa,id_usuario,titulo,descricao,prioridade,id_status) VALUES(?,?,?,?,?,?)')->execute([$E,$u['id'],$tit,$desc,$pri,$st]);
  hist($pdo,$pdo->lastInsertId(),$u['id'],null,$st);out(true);
 case 'editar':
  if(!$tit)out(false,'Informe o título da tarefa.');
  $pdo->prepare('UPDATE tarefas SET titulo=?,descricao=?,prioridade=? WHERE id_tarefa=? AND id_usuario=? AND id_empresa=?')->execute([$tit,$desc,$pri,$in['id']??0,$u['id'],$E]);out(true);
 case 'excluir':
  $pdo->prepare('DELETE FROM tarefas WHERE id_tarefa=? AND id_usuario=? AND id_empresa=?')->execute([$in['id']??0,$u['id'],$E]);out(true);
 case 'wip':
  $v=trim((string)($in['limite']??''));$l=ctype_digit($v)&&(int)$v>0?(int)$v:null;
  $pdo->prepare('UPDATE status_tarefas SET limite_wip=? WHERE id_status=? AND id_empresa=?')->execute([$l,$in['id']??0,$E]);out(true);
 case 'mover':
  $q=$pdo->prepare('SELECT * FROM tarefas WHERE id_tarefa=? AND id_empresa=?');$q->execute([$in['id']??0,$E]);$t=$q->fetch();
  $q=$pdo->prepare('SELECT * FROM status_tarefas WHERE id_status=? AND id_empresa=?');$q->execute([$in['destino']??0,$E]);$d=$q->fetch();
  if(!$t||!$d)out(false,'Tarefa ou coluna inválida.');
  if($t['id_status']==$d['id_status'])out(true);
  if($d['limite_wip']!==null){
   $q=$pdo->prepare('SELECT COUNT(*) FROM tarefas WHERE id_status=?');$q->execute([$d['id_status']]);
   if($q->fetchColumn()>=$d['limite_wip'])out(false,"Limite de WIP atingido em «{$d['nome_status']}» ({$d['limite_wip']}). Conclua uma tarefa antes de puxar outra.");
  }
  $fim=(int)($d['tipo']==='fim');
  $pdo->prepare('UPDATE tarefas SET id_status=?,data_inicio=IF(?=1 AND data_inicio IS NULL,NOW(),data_inicio),data_conclusao=IF(?=1,NOW(),NULL),lead_time=IF(?=1,TIMESTAMPDIFF(SECOND,data_criacao,NOW()),NULL) WHERE id_tarefa=?')
      ->execute([$d['id_status'],(int)($d['tipo']==='andamento'),$fim,$fim,$t['id_tarefa']]);
  hist($pdo,$t['id_tarefa'],$u['id'],$t['id_status'],$d['id_status']);out(true);
 }out(false,'Ação inválida.');
}catch(PDOException $x){out(false,'Erro no banco de dados.');}
