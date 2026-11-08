<?php
declare(strict_types=1);
// Run from Terminal only: APP_ENV=local php dev/seed_mail.php
if (PHP_SAPI !== 'cli' || getenv('APP_ENV') !== 'local') { http_response_code(403); exit("Local CLI only.\n"); }
require_once __DIR__.'/../config/database.php';
$storage=dirname(__DIR__).'/storage/dev_mail.json';
if(!is_dir(dirname($storage)))mkdir(dirname($storage),0700,true);
$fp=fopen($storage,'c+');if(!$fp)throw new RuntimeException('Cannot open storage');
try {
 if(!flock($fp,LOCK_EX))throw new RuntimeException('Cannot lock storage');
 rewind($fp);$messages=json_decode(stream_get_contents($fp)?:'[]',true);if(!is_array($messages))$messages=[];
 $existing=[];foreach($messages as $m){if(is_array($m)&&isset($m['seed_key']))$existing[(string)$m['seed_key']]=true;}
 // Use real demo account addresses; never assume names or create fictional login users.
 $rows=$pdo->query('SELECT l.user_Email,u.first_Name,l.user_Type FROM Mail_Account ma JOIN Login l ON l.user_ID=ma.user_ID JOIN User u ON u.user_ID=ma.user_ID WHERE LOWER(l.user_Email) LIKE \'%@ashford.edu\'')->fetchAll(PDO::FETCH_ASSOC);
 $samples=[
  ['registrar@ashford.edu','Registration Reminder — Upcoming Semester',"Hello %s,\n\nPlease review your registration dates and any account holds before selecting courses.\n\nOffice of the Registrar",-5],
  ['advising@ashford.edu','Academic Advising Appointments',"Hello %s,\n\nAcademic advising appointments are available. Review your degree requirements and plan your next semester.\n\nAcademic Advising",-4],
  ['library@ashford.edu','Library Resources and Research Support',"Hello %s,\n\nThe university library offers research guides, electronic journals, and study resources.\n\nUniversity Library",-3],
  ['ithelpdesk@ashford.edu','Technology Services — Account Safety',"Hello %s,\n\nProtect your university account: do not share verification codes or passwords. Contact the IT Help Desk if you need assistance.\n\nInformation Technology Services",-2],
  ['studentaffairs@ashford.edu','Campus Announcements and Student Services',"Hello %s,\n\nExplore campus activities, student support services, and upcoming announcements.\n\nStudent Affairs",-1],
 ];
 $added=0;
 foreach($rows as $r){$email=strtolower(trim((string)$r['user_Email']));$name=trim((string)$r['first_Name']);
  foreach($samples as $i=>$s){$key='ashford-demo-v1:'.$email.':'.$i;if(isset($existing[$key]))continue;
   $messages[]=['id'=>bin2hex(random_bytes(12)),'from'=>$s[0],'to'=>$email,'subject'=>$s[1], 'body'=>sprintf($s[2],$name), 'date'=>date(DATE_ATOM,strtotime($s[3].' days')),'seed_key'=>$key];$added++;
  }
 }
 rewind($fp);ftruncate($fp,0);fwrite($fp,json_encode($messages,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));fflush($fp);
 echo "Added {$added} sample emails across ".count($rows)." existing mail accounts.\n";
} finally {flock($fp,LOCK_UN);fclose($fp);}
chmod($storage,0600);
