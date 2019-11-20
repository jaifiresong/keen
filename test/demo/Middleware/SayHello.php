<?php

class SayHello {
    public function handle($request) {
        echo '<br />';
        echo 'hello', $request->visitor_addr;
        echo '<br />';
    }
}