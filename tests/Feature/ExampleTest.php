<?php

test('unauthenticated users are redirected to login page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
