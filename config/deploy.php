<?php

return [
    'ssh_host'     => env('DEPLOY_SSH_HOST'),
    'ssh_port'     => env('DEPLOY_SSH_PORT', 22),
    'ssh_user'     => env('DEPLOY_SSH_USER'),
    'ssh_password' => env('DEPLOY_SSH_PASSWORD'),
    'target_dir'   => env('DEPLOY_TARGET_DIR', '/var/www/audios_app'),
];

