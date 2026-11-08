<?php
declare(strict_types=1);
require_once __DIR__.'/../includes/mail_auth.php';
mail_local_only();
$uid=mail_require_login();
require_once __DIR__.'/../config/database.php';
$q=$pdo->prepare('SELECT l.user_Email,u.first_Name,u.last_Name FROM Mail_Account ma JOIN Login l ON l.user_ID=ma.user_ID JOIN User u ON u.user_ID=ma.user_ID WHERE ma.user_ID=? LIMIT 1');
$q->execute([$uid]); $user=$q->fetch(PDO::FETCH_ASSOC);
if (!$user) { header('Location: logout.php'); exit; }
$email=strtolower(trim((string)$user['user_Email'])); $name=trim($user['first_Name'].' '.$user['last_Name']);
$path=dirname(__DIR__).'/storage/dev_mail.json';
function mailbox_data(string $path, ?callable $change=null): array {
 if (!is_dir(dirname($path))) mkdir(dirname($path),0700,true);
 $fp=fopen($path,'c+'); if(!$fp) throw new RuntimeException('Cannot open mailbox storage');
 try { if(!flock($fp,$change?LOCK_EX:LOCK_SH)) throw new RuntimeException('Mailbox storage locked');
  rewind($fp); $all=json_decode(stream_get_contents($fp)?:'[]',true); if(!is_array($all)) $all=[];
  $all=array_values(array_filter($all,'is_array'));
  if($change){$change($all); rewind($fp);ftruncate($fp,0);fwrite($fp,json_encode($all,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));fflush($fp);}
  return $all;
 } finally {flock($fp,LOCK_UN);fclose($fp);}
}
function mailbox_folder(array $m,string $email): ?string {
 $to=strtolower(trim((string)($m['to']??'')));$from=strtolower(trim((string)($m['from']??'')));
 $owner=strtolower(trim((string)($m['owner']??'')));
 if(($m['draft']??false) && $owner===$email) return 'drafts';
 if($from===$email && $owner===$email) return 'sent';
 if($to===$email) return 'inbox';
 return null;
}
function mailbox_url(string $folder,string $id='',string $search=''): string {
 return 'mailbox.php?folder='.rawurlencode($folder)
  .($id!==''?'&id='.rawurlencode($id):'')
  .($search!==''?'&q='.rawurlencode($search):'');
}
function mailbox_date(mixed $v):string {$t=strtotime((string)$v);return $t?date('M j, g:i A',$t):'';}
function mailbox_body(string $body):string {
 $parts=preg_split('~(https?://(?:localhost|127\.0\.0\.1)(?::\d+)?/[^\s<>"\']+)~i',$body,-1,PREG_SPLIT_DELIM_CAPTURE);
 $out=''; foreach($parts as $part){$url=filter_var($part,FILTER_VALIDATE_URL);if($url && preg_match('~^https?://(?:localhost|127\.0\.0\.1)(?::\d+)?/~i',$part)) $out.='<a class="mail-link" href="'.mail_h($part).'" rel="noopener noreferrer">'.mail_h($part).'</a>';else $out.=mail_h($part);}return $out;
}
function mailbox_security_body(array $message): string {
 $template=(string)($message['template']??'');
 if(!in_array($template,['reset_code','login_code','password_changed'],true)){
  $oldBody=(string)($message['body']??'');
  $from=strtolower(trim((string)($message['from']??'')));
  if($from==='security@ashford.edu' && preg_match('/verification code is:\s*[0-9]{6}/i',$oldBody)){
   $subject=strtolower((string)($message['subject']??''));
   $template=(str_contains($subject,'sign-in') || str_contains($subject,'sign in') || stripos($oldBody,'sign-in verification code')!==false) ? 'login_code' : 'reset_code';
  }
  else return nl2br(mailbox_body($oldBody));
 }
 $raw=(string)($message['body']??'');
 $recipient='';
 if(preg_match('/^Hello (.*?),/m',$raw,$matches))$recipient=trim($matches[1]);
 $hello='Hello '.mail_h($recipient!==''?$recipient:'there').',';
 $header='<div style="background:#082c52;color:#fff;padding:22px 28px;font-size:22px;font-weight:700;letter-spacing:.3px">◆ Ashford University <span style="font-size:12px;font-weight:400;opacity:.85"> | Security</span></div>';
 $start='<div style="max-width:680px;margin:18px auto;background:#fff;border:1px solid #dce3ed;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px #122b4510;font-family:Arial,sans-serif;line-height:1.6">'.$header.'<div style="padding:28px 34px;color:#1c2b3b">';
 $end='<hr style="border:0;border-top:1px solid #e3e8f0;margin:26px 0 16px"><div style="font-size:13px;color:#66788b">Ashford University Security<br>Account Protection &amp; Support<br><span style="font-size:11px">Internal university demonstration mail</span></div></div></div>';
 if($template==='reset_code' || $template==='login_code'){
  if(!preg_match('/verification code is:\s*([0-9]{6})/i',$raw,$matches))return nl2br(mailbox_body($raw));
  $code=mail_h($matches[1]);
  $isLogin=$template==='login_code';
  $heading=$isLogin?'Sign-In Verification Code':'Password Reset Verification';
  $intro=$isLogin?'We received a request to sign in to your Ashford University account. Enter the code below to complete your sign-in.':'We received a request to reset your Ashford University account password. Enter the code below to continue.';
  $warning=$isLogin?'If you did not attempt to sign in, please contact university support.':'If you did not request a password reset, you can safely ignore this email.';
  return $start.'<h2 style="font-size:24px;color:#102e51;margin:0 0 20px">'.$heading.'</h2><p><strong>'.$hello.'</strong></p><p>'.$intro.'</p><div style="text-align:center;background:#eaf3ff;border-radius:10px;padding:22px;margin:24px 0"><div style="font-size:13px;font-weight:bold;color:#17529a">YOUR VERIFICATION CODE</div><div style="font-size:38px;letter-spacing:8px;font-weight:800;color:#0b2e55">'.$code.'</div></div><p style="font-size:14px;color:#526579">⏱ Expires in 10 minutes &nbsp; • &nbsp; One-time use only</p><p style="font-size:14px">'.$warning.'</p>'.$end;
 }
 return $start.'<h2 style="font-size:24px;color:#102e51;margin:0 0 20px">Your Password Was Changed</h2><p><strong>'.$hello.'</strong></p><div style="background:#eaf7ee;color:#14633a;padding:16px;border-radius:9px;font-weight:bold">✓ Password changed successfully</div><p>Your Ashford University account password was successfully updated.</p><p>If you made this change, no further action is required.</p><p><strong>Didn’t make this change?</strong> Contact university support immediately.</p>'.$end;
}
$folders=['inbox'=>'Inbox','sent'=>'Sent Items','drafts'=>'Drafts','deleted'=>'Deleted Items'];
$folder=(string)($_GET['folder']??'inbox');if(!isset($folders[$folder]))$folder='inbox';
$error='';$notice='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $csrf=(string)($_POST['csrf']??'');
 if(!hash_equals(mail_csrf(),$csrf)){http_response_code(403);exit('Invalid form token');}
 $action=(string)($_POST['action']??'');$id=(string)($_POST['id']??'');
 if(in_array($action,['send','draft'],true)){
  $to=strtolower(trim((string)($_POST['to']??'')));$subject=trim((string)($_POST['subject']??''));$body=trim((string)($_POST['body']??''));
  if(strlen($to)>255 || strlen($subject)>200 || strlen($body)>20000 || ($action==='send' && (!filter_var($to,FILTER_VALIDATE_EMAIL)||$body===''))){$error='Enter a valid recipient and message.';}
  else {
   if($action==='send'){$check=$pdo->prepare('SELECT 1 FROM Login WHERE LOWER(user_Email)=? LIMIT 1');$check->execute([$to]);if(!$check->fetchColumn())$error='Recipient not found in Ashford University.';}
   if(!$error){mailbox_data($path,static function(array &$all)use($email,$to,$subject,$body,$action):void{
    $base=['from'=>$email,'to'=>$to,'subject'=>$subject,'body'=>$body,'date'=>date(DATE_ATOM)];
    if($action==='draft'){$all[]=array_merge($base,['id'=>bin2hex(random_bytes(12)),'owner'=>$email,'draft'=>true]);}
    else {
     $all[]=array_merge($base,['id'=>bin2hex(random_bytes(12))]);
     $all[]=array_merge($base,['id'=>bin2hex(random_bytes(12)),'owner'=>$email,'sent_copy'=>true]);
    }
   });header('Location: '.mailbox_url($action==='draft'?'drafts':'sent'));exit;}
  }
 } elseif(in_array($action,['delete','restore','read','permanent'],true)){
  mailbox_data($path,static function(array &$all)use($action,$id,$email):void{
   foreach($all as $i=>&$m){if((string)($m['id']??'')!==$id)continue;
    $base=mailbox_folder($m,$email);if($base===null)break;
    $deleted=is_array($m['deleted_for']??null)?$m['deleted_for']:[];
    $isDeleted=in_array($email,$deleted,true);
    if($action==='delete' && !$isDeleted){$deleted[]=$email;$m['deleted_for']=$deleted;}
    if($action==='restore' && $isDeleted){$m['deleted_for']=array_values(array_diff($deleted,[$email]));}
    if($action==='read' && !$isDeleted){$read=is_array($m['read_by']??null)?$m['read_by']:[];$read[]=$email;$m['read_by']=array_unique($read);}
    if($action==='permanent' && $isDeleted){$hidden=is_array($m['hidden_for']??null)?$m['hidden_for']:[];$hidden[]=$email;$m['hidden_for']=array_unique($hidden);}
    break;
   }unset($m);
  });header('Location: '.mailbox_url($folder==='deleted'?'deleted':$folder));exit;
 }
}
$all=mailbox_data($path);$counts=array_fill_keys(array_keys($folders),0);$messages=[];
foreach($all as $m){$base=mailbox_folder($m,$email);if(!$base)continue;
 if(in_array($email,$m['hidden_for']??[],true))continue;
 $deleted=in_array($email,$m['deleted_for']??[],true);
 $f=$deleted?'deleted':$base;$counts[$f]++;
 if($f===$folder)$messages[]=$m;
}
$search=trim((string)($_GET['q']??''));if($search!=='')$messages=array_values(array_filter($messages,static fn($m)=>stripos(implode(' ',[(string)($m['from']??''),(string)($m['to']??''),(string)($m['subject']??''),(string)($m['body']??'')]),$search)!==false));
usort($messages,static fn($a,$b)=>strcmp((string)($b['date']??''),(string)($a['date']??'')));
$selected=(string)($_GET['id']??($messages[0]['id']??''));$active=null;
foreach($messages as $m)if((string)($m['id']??'')===$selected){$active=$m;break;}
if(!$active && count($messages)>0){$active=$messages[0];$selected=(string)($active['id']??'');}
if($active && $folder==='inbox' && !in_array($email,$active['read_by']??[],true)){
 mailbox_data($path,static function(array &$all)use($selected,$email):void{foreach($all as &$m){if(($m['id']??'')===$selected){$m['read_by']=array_values(array_unique(array_merge($m['read_by']??[],[$email])));break;}}});
}
$compose=isset($_GET['compose']);$reply=isset($_GET['reply'])&&$active;
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=mail_h($folders[$folder])?> | Ashford Mail</title><link rel="stylesheet" href="mail.css"><style>
.nav-item{display:flex;text-decoration:none}.topsearch{color:#222;background:#fff}.mail-button{background:#0f5b99;color:#fff;border:0;border-radius:6px;padding:10px 15px;cursor:pointer}.mail-button.secondary{background:#e6eef8;color:#17456a}.compose-form{display:grid;gap:12px;padding:22px}.compose-form input,.compose-form textarea{width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:12px;font:inherit}.compose-form textarea{min-height:180px}.actions{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}.actions form{display:inline}.nav .compose-link{display:block;margin:12px;background:#0f5b99;color:white;border-radius:7px;padding:12px;text-align:center}.message.unread .subject{font-weight:800}.alert{padding:12px;background:#fff1f1;color:#8a1111}
</style></head><body class="mail-body">
<header class="topbar"><a class="brand" href="mailbox.php"><span class="brandmark">A</span> Ashford Mail</a><form method="get" action="mailbox.php"><input type="hidden" name="folder" value="<?=mail_h($folder)?>"><input class="topsearch" name="q" value="<?=mail_h($search)?>" placeholder="Search mail" aria-label="Search mail"></form><div class="account"><div><div class="account-name"><?=mail_h($name)?></div><div class="account-email"><?=mail_h($email)?></div></div><div class="avatar"><?=mail_h(strtoupper(substr($name,0,1)))?></div><a class="signout" href="logout.php">Sign out</a></div></header>
<div class="workspace"><nav class="nav" aria-label="Mail folders"><a class="compose-link" href="<?=mail_h(mailbox_url($folder))?>&amp;compose=1">+ New Mail</a><div class="nav-title">Folders</div>
<?php foreach($folders as $key=>$label):?><a class="nav-item <?=$folder===$key?'active':''?>" href="<?=mail_h(mailbox_url($key))?>"><span><?=mail_h($label)?></span><span class="count"><?=$counts[$key]?></span></a><?php endforeach;?><div class="nav-sep"></div><p class="nav-note">Internal Ashford University demonstration mail. Messages are local only.</p></nav>
<section class="messages"><div class="messages-header"><h1><?=mail_h($folders[$folder])?></h1><span class="tag"><?=count($messages)?> messages</span></div><div class="tools"><a class="tool-link" href="<?=mail_h(mailbox_url($folder,$selected,$search))?>">↻ Refresh</a> <?php if($search!==''):?><a href="<?=mail_h(mailbox_url($folder))?>">Clear search</a><?php endif;?></div>
<?php if(!$messages):?><div class="empty"><strong>No messages</strong><p>There are no messages in this folder.</p></div><?php endif;?>
<?php foreach($messages as $m):?><a class="message <?=$selected===(string)($m['id']??'')?'selected':''?> <?=!in_array($email,$m['read_by']??[],true)?'unread':''?>" href="<?=mail_h(mailbox_url($folder,(string)($m['id']??''),$search))?>"><div class="message-head"><span class="sender"><?=mail_h($folder==='sent'||$folder==='drafts'?'To: '.($m['to']??''):$m['from']??'')?></span><span class="date"><?=mail_h(mailbox_date($m['date']??''))?></span></div><div class="subject"><?=mail_h($m['subject']??'(No subject)')?></div><div class="preview"><?=mail_h($m['body']??'')?></div></a><?php endforeach;?></section>
<article class="reader"><?php if($error!==''):?><div class="alert"><?=mail_h($error)?></div><?php endif;?>
<?php if($compose||$reply):?><h2><?=$reply?'Reply':'New Message'?></h2><form class="compose-form" method="post"><input type="hidden" name="csrf" value="<?=mail_h(mail_csrf())?>"><label>To<input type="email" name="to" value="<?=mail_h($reply?($active['from']??''):($_POST['to']??''))?>" required></label><label>Subject<input name="subject" maxlength="200" value="<?=mail_h($reply?'Re: '.($active['subject']??''):($_POST['subject']??''))?>"></label><label>Message<textarea name="body" maxlength="20000"><?=mail_h($_POST['body']??'')?></textarea></label><div class="actions"><button class="mail-button" name="action" value="send">Send</button><button class="mail-button secondary" name="action" value="draft" formnovalidate>Save draft</button><a href="<?=mail_h(mailbox_url($folder))?>">Cancel</a></div></form>
<?php elseif($active):?><div class="eyebrow">Message</div><h2><?=mail_h($active['subject']??'(No subject)')?></h2><div class="read-meta"><div class="avatar">A</div><div><div class="read-from"><?=mail_h($active['from']??'')?></div><div class="read-to">To: <?=mail_h($active['to']??'')?></div></div><div class="read-date"><?=mail_h(mailbox_date($active['date']??''))?></div></div><div class="actions">
<?php if($folder==='inbox'):?><a class="mail-button secondary" href="<?=mail_h(mailbox_url($folder,(string)$active['id']))?>&amp;reply=1">Reply</a><?php endif;?>
<form method="post"><input type="hidden" name="csrf" value="<?=mail_h(mail_csrf())?>"><input type="hidden" name="id" value="<?=mail_h($active['id'])?>"><button class="mail-button secondary" name="action" value="<?=$folder==='deleted'?'restore':'delete'?>"><?=$folder==='deleted'?'Restore':'Delete'?></button></form>
<?php if($folder==='deleted'):?><form method="post"><input type="hidden" name="csrf" value="<?=mail_h(mail_csrf())?>"><input type="hidden" name="id" value="<?=mail_h($active['id'])?>"><button class="mail-button secondary" name="action" value="permanent" onclick="return confirm('Permanently remove this message from your mailbox?')">Delete permanently</button></form><?php endif;?></div>
<div class="read-body"><?=mailbox_security_body($active)?></div>
<?php else:?><div class="empty"><strong>Select a message</strong><p>Choose an email to read it.</p></div><?php endif;?><p class="footnote">Ashford University Mail is a local demonstration system, not Microsoft Outlook or an internet email provider.</p></article></div></body></html>
