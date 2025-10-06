<?php

return [
    'embed' => [
        /*
        |--------------------------------------------------------------------------
        | Allowed iframe parents for the public embed endpoint.
        |--------------------------------------------------------------------------
        |
        | Configure which origins can embed the public audios table. The value can
        | be provided via the EMBED_FRAME_ANCESTORS environment variable using
        | a comma or space separated list (e.g. "https://a.com,https://b.com").
        |
        */
        'frame_ancestors' => array_values(array_filter(array_map(
            'trim',
            preg_split(
                '/[\s,]+/',
                (string) env('EMBED_FRAME_ANCESTORS', 'https://iglesiapalma.org https://www.iglesiapalma.org')
            ) ?: []
        ))),
    ],
];

