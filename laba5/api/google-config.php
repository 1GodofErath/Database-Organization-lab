<?php
return [
    'client_id' => getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_CLIENT_ID.apps.googleusercontent.com',
    'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_CLIENT_SECRET',
    'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: 'http://localhost:10092/api/google-callback.php'
];
