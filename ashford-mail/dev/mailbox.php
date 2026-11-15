<?php
declare(strict_types=1);
/** Ashford Mail: development-only inbox. Never expose publicly. */
session_start();
$host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$remote = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$allowedHost = preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $host) === 1;
$allowedRemote = in_array($remote, ['127.0.0.1', '::1'], true);
if (!$allowedHost || !$allowedRemote) {
    http_response_code(403);
    exit('Development mailbox is available on localhost only.');
}
$storage = dirname(__DIR__) . '/storage/dev_mail.json';
$messages = [];
if (is_file($storage)) {
    $data = json_decode((string)file_get_contents($storage), true);
    if (is_array($data)) $messages = array_values(array_filter($data, 'is_array'));
}
usort($messages, static fn($a, $b) => strcmp((string)($b['date'] ?? ''), (string)($a['date'] ?? '')));
$selected = (string)($_GET['id'] ?? ($messages[0]['id'] ?? ''));
$active = null;
foreach ($messages as $message) {
    if ((string)($message['id'] ?? '') === $selected) { $active = $message; break; }
}
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function date_label($v): string { $t = strtotime((string)$v); return $t ? date('M j, g:i A', $t) : ''; }
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ashford Mail — Inbox</title>
<style>
:root{font-family:Arial,Helvetica,sans-serif;color:#242424;background:#f3f5f9}*{box-sizing:border-box}body{margin:0}a{text-decoration:none;color:inherit}
.top{height:52px;background:#0f4c81;color:white;display:flex;align-items:center;gap:20px;padding:0 22px}.brand{font-weight:700;font-size:18px;white-space:nowrap}.search{background:#e6f0fa;border:0;border-radius:5px;padding:10px 14px;width:min(470px,45vw);color:#243746}.profile{margin-left:auto;font-size:13px}
.shell{height:calc(100vh - 52px);display:grid;grid-template-columns:210px 350px minmax(350px,1fr)}.folders{background:#f8f9fc;border-right:1px solid #ddd;padding:22px 12px}.folder{display:block;padding:12px 14px;border-radius:5px;color:#3b4250;font-size:14px}.folder.current{background:#dceaf7;color:#084d82;font-weight:700}.folders h3{font-size:12px;color:#6c7685;margin:24px 14px 8px;text-transform:uppercase;letter-spacing:.07em}
.list{background:white;border-right:1px solid #ddd;overflow-y:auto}.listhead{padding:22px 20px 12px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center}.listhead h1{font-size:22px;margin:0}.toolbar{padding:12px 20px;color:#075a99;font-size:13px;border-bottom:1px solid #eee}.item{display:block;padding:17px 18px;border-bottom:1px solid #eee}.item:hover{background:#f5f9fd}.item.active{background:#eaf3fb;border-left:3px solid #1265a2;padding-left:15px}.sender{font-size:14px;font-weight:700}.subject{font-size:14px;margin-top:8px}.preview{font-size:12px;color:#737a85;margin-top:7px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}.date{float:right;font-size:11px;color:#687381;font-weight:400}
.reader{background:#fff;overflow-y:auto;padding:28px 38px}.reader h2{font-size:24px;font-weight:600;margin:12px 0 26px}.meta{border-bottom:1px solid #e6e6e6;padding-bottom:22px;font-size:13px;line-height:1.8}.meta strong{font-size:15px}.body{white-space:pre-wrap;line-height:1.75;font-size:15px;padding:30px 0;max-width:780px}.empty{padding:35px 20px;color:#737a85;font-size:14px}.note{font-size:12px;color:#6a7280;margin-top:28px}.pill{display:inline-block;background:#e6f3ff;color:#135783;padding:5px 9px;border-radius:12px;font-size:11px}
@media(max-width:900px){.shell{grid-template-columns:150px 290px minmax(260px,1fr)}.reader{padding:22px}.folders{padding:12px 6px}}@media(max-width:680px){.shell{display:block;height:auto}.folders{display:flex;gap:6px;overflow-x:auto}.folders h3{display:none}.list{max-height:320px}.reader{min-height:300px}.search{display:none}}
</style></head><body>
<header class="top"><div class="brand">▦ &nbsp; Ashford Mail</div><input class="search" placeholder="Search mail" aria-label="Search mail" disabled><div class="profile">Development Inbox &nbsp; ◉</div></header>
<main class="shell"><nav class="folders"><h3>Folders</h3><span class="folder current">▣ &nbsp; Inbox <small>(<?= count($messages) ?>)</small></span><span class="folder">✉ &nbsp; Sent Items</span><span class="folder">▤ &nbsp; Drafts</span><span class="folder">▧ &nbsp; Deleted Items</span><h3>University</h3><span class="folder">Ashford University</span></nav>
<section class="list"><div class="listhead"><h1>Inbox</h1><span class="pill">Local demo</span></div><div class="toolbar">↻ &nbsp; <a href="mailbox.php">Refresh</a></div>
<?php if (!$messages): ?><div class="empty">Your inbox is empty.<p>Verification messages will appear here after the reset backend is connected.</p></div><?php endif; ?>
<?php foreach ($messages as $m): ?><a class="item <?= $selected === (string)($m['id'] ?? '') ? 'active' : '' ?>" href="?id=<?= rawurlencode((string)($m['id'] ?? '')) ?>"><div class="sender"><?= h($m['from'] ?? 'security@ashford.edu') ?><span class="date"><?= h(date_label($m['date'] ?? '')) ?></span></div><div class="subject"><?= h($m['subject'] ?? '(No subject)') ?></div><div class="preview"><?= h($m['body'] ?? '') ?></div></a><?php endforeach; ?>
</section><article class="reader">
<?php if ($active): ?><h2><?= h($active['subject'] ?? '(No subject)') ?></h2><div class="meta"><strong><?= h($active['from'] ?? 'security@ashford.edu') ?></strong><br>To: <?= h($active['to'] ?? '') ?><br><?= h(date_label($active['date'] ?? '')) ?></div><div class="body"><?= h($active['body'] ?? '') ?></div>
<?php else: ?><div class="empty">Select a message to read it.</div><?php endif; ?>
<p class="note">Ashford University Mail — internal development simulation. This is not Microsoft Outlook or an internet email service.</p></article></main></body></html>
