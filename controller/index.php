<?php

$result = json_encode([
    "prova" => 123
], JSON_PRETTY_PRINT );

echo "<pre>$result</pre>";
// var_dump(parse_ini_file(__DIR__."/../env/dev.env"));
var_dump(getenv());

phpinfo();