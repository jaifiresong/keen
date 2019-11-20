<?php
 
class SayBye {
    public function handle($request) {
        echo '<br />';
        echo 'bye bye', $request->visitor_addr;
        echo '<br />';
    }
}