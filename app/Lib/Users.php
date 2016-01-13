<?php
/**
 * Created by PhpStorm.
 * User: raghav
 * Date: 06/01/16
 * Time: 2:28 PM
 */

App::uses('HttpSocket', 'Network/Http');

class Users {

    private $apiUrl;
    private $http;

    public function approveUser($username, $approve = true){
        $output = $this->updateUser($username, array("update"=>array("approved"=>$approve)));
    }

    public function assignVM($username, $data){
        $output = $this->updateUser($username, array("update"=>array("vm_ip"=>$data['ip'],"vm_ip_id"=>$data['vm_ip_id'])));
    }

    public function approveUserAndAssignVM($username, $data, $approve = true){
        $output = $this->updateUser($username, array(
            "update"=>array(
                "vm_ip"=>$data['ip'],
                "vm_ip_id"=>$data['vm_ip_id'],
                "approved"=>$approve
                )
            )
        );
    }

    public function getUser($username = null){
        $output = null;
        $this->http = new HttpSocket(array(
            'ssl_verify_peer' => false,
            'ssl_verify_host' => false
        ));
        $request = $this->getHeaders();
        $this->apiUrl = Configure::read('Overseer.url');

        if($username != null) {
            $result = $this->http->get($this->apiUrl . "/services/user/" . $username, array(), $request);
        }else{
            $result = $this->http->get($this->apiUrl . "/services/users", array(), $request);
        }
        if($result->code == 200){
            $output = json_decode($result->body);
        }
        return $output;
    }
    public function updateUser($username, $update){
        $this->http = new HttpSocket(array(
            'ssl_verify_peer' => false,
            'ssl_verify_host' => false
        ));
        $request = $this->getHeaders();

        $result = $this->http->put($this->apiUrl."/services/user/".$username, json_encode($update), $request);
        if($result->code == 200){
            $output = true;
        }
        return $output;
    }

    public function getHeaders(){
        $request = array(
            'header' => array(
                'Content-Type' => 'application/json',
                'Accept-Type'=>'application/json',
                'svmp-authtoken'=>Configure::read('Svmp.authToken')
            )
        );

        return $request;
    }

}