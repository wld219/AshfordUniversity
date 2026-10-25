<?php

declare(strict_types=1);

// CSV export and pagination.

// Stream a CSV file to the browser and stop.
function csv_export(
	array $headers,
	array $rows,
	string $filename,
	?array $columns = null
): void {
	if ($columns === null) {
		$columns = array_keys($rows[0] ?? []);
	}

	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
	header('Cache-Control: no-store, no-cache');

	$output = fopen('php://output', 'w');

	// UTF-8 BOM so Excel opens accented names correctly.
	fwrite($output, "\xEF\xBB\xBF");
	fputcsv($output, $headers);

	foreach ($rows as $row) {
		$line = [];

		foreach ($columns as $column) {
			$line[] = is_scalar($row[$column] ?? null)
				? (string) ($row[$column] ?? '')
				: json_encode($row[$column] ?? null);
		}

		fputcsv($output, $line);
	}

	fclose($output);
	exit;
}

// Compute pagination metadata.
function pagination_meta(int $total, int $perPage, int $page): array
{
	$pages  = max(1, (int) ceil($total / $perPage));
	$page   = max(1, min($page, $pages));
	$offset = ($page - 1) * $perPage;

	return [
		'page'    => $page,
		'perPage' => $perPage,
		'total'   => $total,
		'pages'   => $pages,
		'offset'  => $offset,
	];
}

// Render pagination links that preserve current query parameters.
function render_pagination(array $meta, string $baseUrl): void
{
	if ($meta['pages'] <= 1) {
		return;
	}

	$query = $_GET;
	unset($query['page']);

	$link = static function (int $page) use ($baseUrl, $query): string {
		$query['page'] = $page;

		return e($baseUrl . '?' . http_build_query($query));
	};

	echo '<div class="pagination">';

	if ($meta['page'] > 1) {
		echo '<a class="btn light small" href="' . $link($meta['page'] - 1) . '">&laquo; Prev</a>';
	}

	echo '<span class="muted">Page ' . $meta['page'] . ' of ' . $meta['pages']
		. ' (' . $meta['total'] . ' records)</span>';

	if ($meta['page'] < $meta['pages']) {
		echo '<a class="btn light small" href="' . $link($meta['page'] + 1) . '">Next &raquo;</a>';
	}

	echo '</div>';
}
