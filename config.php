<?php

// "php_location": "/opt/lampp/bin/",
//"php_location": "/usr/bin/",

$config = '{
    "php_location": "/opt/lampp/bin/",
    "temp_dir":"/home/alpha/Downloads/xdb/",
    "extension_settings": [
        "xdebug.default_enable = On; включить xdebug по умолчанию",
        "xdebug.var_display_max_depth = 1000; глубина показа дампа массивов и объектов",
        "xdebug.remote_enable = On; включить удаленную отладку",
        "xdebug.remote_host = 127.0.0.1; хост для удаленной отладки",
        "xdebug.remote_port = 9000; порт для удаленной отладки",
        "xdebug.remote_handler = dbgp; протокол для отладки",
        "xdebug.idekey = XDEBUG; идентификатор, который будет отправлять IDE",
        "xdebug.remote_autostart = 1; автоматический запуск отладки",
        ";xdebug.remote_log=/tmp/xdebug/xdebug.log; лог-файл для удаленной отладки",
        ";xdebug.profiler_enable_trigger = 1; запускать профилирование по триггеру",
        "xdebug.profiler_enable = 0; включить профилирование",
        ";xdebug.profiler_output_dir = /tmp/xdebug/profiler/; директория для хранения результатов профилирования",
        "xdebug.show_local_vars = 1; отобразить все локальные переменные в случае возникновения ошибки",
        "xdebug.overload_var_dump = 1; включает усовершенcтвованный var_dump"
    ]
}';

?>