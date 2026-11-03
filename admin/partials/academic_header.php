<?php

declare(strict_types=1);

// Academic page header.

?>

<div class="card">

	<div class="dashboard-section-heading">
		<div>
			<p class="dashboard-eyebrow">Catalog</p>
			<h2>Academic management</h2>
		</div>
		<span class="badge"><?= e($securityLevel) ?></span>
	</div>

	<?php if ($error !== ''): ?>
		<div class="alert error"><?= e($error) ?></div>
	<?php endif; ?>

</div>
