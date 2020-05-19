<?php

namespace Drupal\message_digest_admin\Controller;

class MessageDigestAdminController{

    public function testTab(){
        return [
            '#markup'=> 'Test Tab',
        ];
    }
    public function sendTab(){
        return [
            '#markup'=> 'Send Tab',
        ];
    }
    public function historyTab(){
        return [
            '#markup'=> 'History Tab',
        ];
    }
}