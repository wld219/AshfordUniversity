<?php
declare(strict_types=1);
/** Restore unique messages from prior backups without overwriting current storage. */
$root=dirname(__DIR__);
$current=$root.'/storage/dev_mail.json';
$backupDirs=glob($root.'/backup-mail-*/storage/dev_mail.json')?:[];
$backupDirs=array_merge($backupDirs,glob($root.'/backup-mail-preservation-*/storage/dev_mail.json')?:[]);
$backupDirs=array_values(array_unique($backupDirs));
if (!is_file($current)) {fwrite(STDERR,"No current storage file found.\n");exit(1);}
$fp=fopen($current,'c+');if(!$fp||!flock($fp,LOCK_EX)){fwrite(STDERR,"Cannot lock current storage.\n");exit(1);}
try {
 rewind($fp);$raw=stream_get_contents($fp);
 $all=json_decode($raw?:'[]',true,512,JSON_THROW_ON_ERROR);
 if(!is_array($all))throw new RuntimeException('Current mail storage is not an array');
 $key=static function(array $m):string {
  if(isset($m['id']) && (string)$m['id']!=='')return 'id:'.(string)$m['id'];
  return 'hash:'.hash('sha256',json_encode([$m['from']??'',$m['to']??'',$m['subject']??'',$m['body']??'',$m['date']??'']));
 };
 $known=[];foreach($all as $m)if(is_array($m))$known[$key($m)]=true;
 $before=count($all);$added=0;
 foreach($backupDirs as $file){
  $items=json_decode(file_get_contents($file)?:'[]',true);
  if(!is_array($items)){echo "Skipped invalid backup: $file\n";continue;}
  foreach($items as $m){if(!is_array($m))continue;$id=$key($m);if(isset($known[$id]))continue;$known[$id]=true;$all[]=$m;$added++;}
 }
 if($added){
  $safe=$current.'.before-recovery-'.date('Ymd-His').'.bak';
  if(file_put_contents($safe,$raw)===false)throw new RuntimeException('Could not back up current messages');
  $encoded=json_encode($all,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
  rewind($fp);if(ftruncate($fp,0)===false||fwrite($fp,$encoded)===false)throw new RuntimeException('Write failed');fflush($fp);
  echo "Safety backup: $safe\n";
 }
 echo "Current before: $before; recovered: $added; total: ".count($all)."; backup files inspected: ".count($backupDirs)."\n";
} catch(Throwable $e){fwrite(STDERR,$e->getMessage()."\n");exit(1);} finally{flock($fp,LOCK_UN);fclose($fp);}
