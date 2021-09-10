<?php

$date_str = '2021-09-14T12:00:00-04:00';
$date = date_create_from_format('Y-m-d\TH:i:sT', $date_str);
// echo var_export($date, true);
date_format($date, 'M');
date_format($date, 'j');
date_format($date, 'g:iA');