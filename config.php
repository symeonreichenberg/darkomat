<?php
declare(strict_types=1);

return [
    'app_name' => 'Dárkomat',
    'database_url' => getenv('DATABASE_URL') ?: '',
    'upload_max_bytes' => 5 * 1024 * 1024,
];
