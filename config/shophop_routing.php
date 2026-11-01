<?php
return [
    // OFF by default. Only enable AFTER verifying real, connected hub transport legs
    // and completing manifest/scan tests on a disposable local database.
    'enable_verified_hub_routing' => (bool) env('SHOPHOP_ENABLE_VERIFIED_HUB_ROUTING', false),
];
